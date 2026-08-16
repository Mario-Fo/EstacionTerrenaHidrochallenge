#include <Arduino.h>
#include <esp_task_wdt.h>

#include "config.h"
#include "telemetry.h"
#include "sensors.h"
#include "flight.h"
#include "radio_link.h"

SemaphoreHandle_t dataMutex;
TelemetryData tData;
FlightState flightState = EN_ESPERA;

void TaskSensors(void *pvParameters) {
    esp_task_wdt_add(NULL);
    sensorsInit();

    while (1) {
        esp_task_wdt_reset();
        sensorsUpdate();
        flightUpdate();
        vTaskDelay(pdMS_TO_TICKS(100)); // 10 Hz
    }
}

void TaskLoRa(void *pvParameters) {
    esp_task_wdt_add(NULL);
    radioInit();

    uint32_t lastTx = 0;
    while (1) {
        esp_task_wdt_reset();

        if (millis() - lastTx >= 1000) {
            lastTx = millis();
            radioTransmit();
            radioProcessCommands();
        }

        vTaskDelay(pdMS_TO_TICKS(10));
    }
}

void setup() {
    Serial.begin(115200);

    // Margen amplio para tolerar bloqueos puntuales de LoRa/I2C
    esp_task_wdt_config_t wdt_config = {
        .timeout_ms = 5000,
        .idle_core_mask = (1 << portNUM_PROCESSORS) - 1,
        .trigger_panic = true
    };
    esp_task_wdt_init(&wdt_config);

    dataMutex = xSemaphoreCreateMutex();

    ledcAttach(SERVO_PIN, 50, 10);
    ledcWrite(SERVO_PIN, 51); // 0 grados

    xTaskCreatePinnedToCore(TaskSensors, "Sensors", 4096, NULL, 2, NULL, 0);
    xTaskCreatePinnedToCore(TaskLoRa, "LoRaTx", 8192, NULL, 1, NULL, 1);
}

void loop() {
    vTaskDelay(portMAX_DELAY);
}
