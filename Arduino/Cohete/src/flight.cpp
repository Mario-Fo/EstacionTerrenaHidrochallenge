#include "config.h"
#include "telemetry.h"
#include <Arduino.h>

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
                tData.paracaidas_eyectado = true;
                digitalWrite(SERVO_PWR_PIN, HIGH); // habilita alimentacion del servo (MOSFET)
                ledcWrite(SERVO_CH, 102);          // aprox 180 grados
                flightState = DESACOPLE;
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
