#include <Arduino.h>
#include <WiFi.h>

#include "config.h"
#include "radio_link.h"
#include "http_telemetry.h"

String pendingJson = "";
bool hasPendingData = false;
portMUX_TYPE dataMux = portMUX_INITIALIZER_UNLOCKED;

void TaskRadio(void *pvParameters) {
    radioInit();

    while (1) {
        String incoming;
        if (radioReceive(incoming, 100)) {
            Serial.println("Paquete recibido: " + incoming);
            portENTER_CRITICAL(&dataMux);
            pendingJson = incoming;
            hasPendingData = true;
            portEXIT_CRITICAL(&dataMux);
        }

        if (Serial.available() > 0) {
            char c = Serial.read();
            if (c == 'E' || c == 'e') {
                Serial.println("Enviando comando EJECT...");
                radioTransmitCommand("CMD_EJECT");
            }
        }

        vTaskDelay(pdMS_TO_TICKS(10));
    }
}

void TaskHTTP(void *pvParameters) {
    connectWiFi();
    uint8_t retries = 0;
    while (1) {
        if (hasPendingData) {
            String toSend;
            portENTER_CRITICAL(&dataMux);
            toSend = pendingJson;
            hasPendingData = false;
            portEXIT_CRITICAL(&dataMux);

            bool sent = false;
            if (WiFi.status() == WL_CONNECTED) {
                sent = httpPost(toSend);
            } else {
                connectWiFi();
            }

            if (!sent && retries < 3) {
                portENTER_CRITICAL(&dataMux);
                pendingJson = toSend;
                hasPendingData = true;
                portEXIT_CRITICAL(&dataMux);
                retries++;
            } else {
                retries = 0;
                if (!sent) Serial.println("Dato descartado (maximo de reintentos)");
            }
        }
        vTaskDelay(pdMS_TO_TICKS(50));
    }
}

void setup() {
    Serial.begin(115200);
    WiFi.mode(WIFI_STA);

    xTaskCreatePinnedToCore(TaskRadio, "LoRaRx", 8192, NULL, 2, NULL, 1);
    xTaskCreatePinnedToCore(TaskHTTP, "HTTP", 8192, NULL, 1, NULL, 0);
}

void loop() {
    vTaskDelay(portMAX_DELAY);
}
