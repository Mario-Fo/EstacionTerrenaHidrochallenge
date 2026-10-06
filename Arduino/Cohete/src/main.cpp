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
    // Init fuera del WDT: con el modulo ausente puede tardar varios segundos
    radioOK = radioInit();
    esp_task_wdt_add(NULL);

    uint32_t lastTx = 0;
    uint32_t lastRetry = 0;
    while (1) {
        esp_task_wdt_reset();

        if (!radioOK && millis() - lastRetry >= 10000) {
            lastRetry = millis();
            esp_task_wdt_delete(NULL);
            radioOK = radioInit();
            esp_task_wdt_add(NULL);
        }

        if (millis() - lastTx >= 1000) {
            lastTx = millis();
            if (radioOK) {
                radioTransmit();
            } else {
                Serial.print("TELEMETRIA (sin LoRa): ");
                Serial.println(telemetryJson());
            }
        }

        vTaskDelay(pdMS_TO_TICKS(10));
    }
}

void setup() {
    Serial.begin(115200);

    // Margen amplio para tolerar bloqueos puntuales de LoRa/I2C (timeout en segundos)
    esp_task_wdt_init(5, true);

    dataMutex = xSemaphoreCreateMutex();

    pinMode(SERVO_PWR_PIN, OUTPUT);
    digitalWrite(SERVO_PWR_PIN, LOW); // servo sin alimentar hasta el despliegue

    ledcSetup(SERVO_CH, 50, 10);
    ledcAttachPin(SERVO_PIN, SERVO_CH);
    ledcWrite(SERVO_CH, 51); // 0 grados

    xTaskCreatePinnedToCore(TaskSensors, "Sensors", 4096, NULL, 2, NULL, 0);
    xTaskCreatePinnedToCore(TaskLoRa, "LoRaTx", 8192, NULL, 1, NULL, 1);
}

void loop() {
    vTaskDelay(portMAX_DELAY);
}
