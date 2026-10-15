#include "config.h"
#include "telemetry.h"
#include <Arduino.h>
#include "flight.h"

void flightInit() {
    ledcSetup(SERVO_CH, SERVO_FREQ_HZ, SERVO_RESOLUTION_BITS);
    ledcWrite(SERVO_CH, SERVO_CLOSED_DUTY);
    ledcAttachPin(SERVO_PIN, SERVO_CH);
}

// El llamador debe tener dataMutex: comparten esta accion ambas tareas.
static bool deployLocked() {
    if (tData.paracaidas_eyectado) return false;
    ledcWrite(SERVO_CH, SERVO_DEPLOYED_DUTY);
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
    float v = tData.velZ;
    float alt = tData.alt;

    switch (flightState) {
        case EN_ESPERA:
            // despegue: velocidad vertical sostenida (>5 m/s, 3 ciclos)
            cnt = (v > 5.0f) ? cnt + 1 : 0;
            if (cnt >= 3) { flightState = ASCENSO; cnt = 0; }
            break;

        case ASCENSO:
            // apogeo: la velocidad cruza a <= 0 con altitud suficiente (3 ciclos)
            cnt = (v <= 0.0f && alt > 10.0f) ? cnt + 1 : 0;
            if (cnt >= 3) {
<<<<<<< HEAD
                deployLocked();
=======
                tData.paracaidas_eyectado = true;
                ledcWrite(SERVO_CH, 102);          // aprox 180 grados
                flightState = DESACOPLE;
>>>>>>> cb0335b8459810b43a28a7ece3191b64527a81b7
                cnt = 0;
            }
            break;

        case DESACOPLE:
            // desacople/eyeccion -> descenso cuando empieza a caer
            if (v < -1.0f) { flightState = DESCENSO; cnt = 0; }
            break;

        case DESCENSO:
            // aterrizaje: quieto y bajo (5 ciclos)
            cnt = (fabs(v) < 0.5f && alt < 5.0f) ? cnt + 1 : 0;
            if (cnt >= 5) { flightState = ATERRIZADO; cnt = 0; }
            break;

        default:
            break;
    }

    xSemaphoreGive(dataMutex);
}
