#include "config.h"
#include "telemetry.h"
#include <Arduino.h>
#include "flight.h"

// Despliegue con "sacudida" anti-atasco: al dispararse, el servo oscila 90°
// de un lado a otro durante SERVO_WIGGLE_MS y luego queda en posicion desplegada.
static bool servoDeploying = false;
static uint32_t servoDeployStart = 0;
static uint32_t servoLastToggle = 0;
static bool servoWiggleHigh = false;

void flightInit() {
    ledcSetup(SERVO_CH, SERVO_FREQ_HZ, SERVO_RESOLUTION_BITS);
    ledcWrite(SERVO_CH, SERVO_CLOSED_DUTY);
    ledcAttachPin(SERVO_PIN, SERVO_CH);
}

// No bloqueante: arranca la secuencia; flightServoUpdate() la mantiene.
static void servoStartDeploy() {
    servoDeploying = true;
    servoDeployStart = millis();
    servoLastToggle = servoDeployStart;
    servoWiggleHigh = false;
    ledcWrite(SERVO_CH, SERVO_CLOSED_DUTY);
}

void flightServoUpdate() {
    if (!servoDeploying) return;
    uint32_t now = millis();
    if (now - servoDeployStart < SERVO_WIGGLE_MS) {
        if (now - servoLastToggle >= (SERVO_WIGGLE_PERIOD_MS / 2)) {
            servoLastToggle = now;
            servoWiggleHigh = !servoWiggleHigh;
            ledcWrite(SERVO_CH, servoWiggleHigh ? SERVO_DEPLOYED_DUTY : SERVO_CLOSED_DUTY);
        }
    } else {
        servoDeploying = false;
        ledcWrite(SERVO_CH, SERVO_DEPLOYED_DUTY);   // queda desplegado
    }
}

// El llamador debe tener dataMutex: comparten esta accion ambas tareas.
static bool deployLocked() {
    if (tData.paracaidas_eyectado) return false;
    servoStartDeploy();
    tData.paracaidas_eyectado = true;
    flightState = DESACOPLE;
    return true;
}

bool flightDeploy() {
    xSemaphoreTake(dataMutex, portMAX_DELAY);
    const bool deployed = deployLocked();
    xSemaphoreGive(dataMutex);
    return deployed;
}

void flightUpdate() {
    xSemaphoreTake(dataMutex, portMAX_DELAY);

    static uint8_t cnt = 0;
    float v = tData.velZ;    // velocidad vertical barometrica (BME280): senal primaria
    float alt = tData.alt;   // altitud barometrica
    float az = tData.accZ;   // aceleracion vertical del BNO: confirmacion secundaria

    switch (flightState) {
        case EN_ESPERA:
            // Despegue: velocidad barometrica alta O aceleracion fuerte del BNO.
            cnt = (v > LAUNCH_VEL_MS || az > LAUNCH_ACC_MS2) ? cnt + 1 : 0;
            if (cnt >= 3) { flightState = ASCENSO; cnt = 0; }
            break;

        case ASCENSO:
            // Apogeo: la velocidad barometrica deja de ser positiva a altitud
            // suficiente, sostenido APOGEE_CONFIRM ciclos y sin aceleracion
            // ascendente fuerte (BNO). El barometro manda; el BNO solo confirma.
            cnt = (v <= 0.0f && alt > APOGEE_MIN_ALT_M && az < LAUNCH_ACC_MS2)
                      ? cnt + 1 : 0;
            if (cnt >= APOGEE_CONFIRM) {
                deployLocked();
                cnt = 0;
            }
            break;

        case DESACOPLE:
            // desacople/eyeccion -> descenso cuando empieza a caer
            if (v < DESCENT_VEL_MS) { flightState = DESCENSO; cnt = 0; }
            break;

        case DESCENSO:
            // aterrizaje: quieto y bajo (5 ciclos)
            cnt = (fabs(v) < LANDING_VEL_MS && alt < LANDING_ALT_M) ? cnt + 1 : 0;
            if (cnt >= 5) { flightState = ATERRIZADO; cnt = 0; }
            break;

        default:
            break;
    }

    xSemaphoreGive(dataMutex);
}
