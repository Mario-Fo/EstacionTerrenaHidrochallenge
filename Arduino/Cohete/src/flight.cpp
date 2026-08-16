#include "config.h"
#include "telemetry.h"
#include <Arduino.h>

void flightUpdate() {
    xSemaphoreTake(dataMutex, portMAX_DELAY);

    switch (flightState) {
        case EN_ESPERA:
            if (tData.velZ > 5.0) flightState = ASCENSO;
            break;

        case ASCENSO:
            if (tData.velZ <= 0.0 && tData.alt > 10.0) {
                flightState = APOGEO;
                tData.paracaidas_eyectado = true;
                ledcWrite(SERVO_PIN, 102); // aprox 180 grados
                flightState = DESCENSO;
            }
            break;

        case DESCENSO:
            if (fabs(tData.velZ) < 0.5 && tData.alt < 5.0) {
                flightState = ATERRIZADO;
            }
            break;

        default:
            break;
    }

    xSemaphoreGive(dataMutex);
}
