#include <Arduino.h>
#include <WiFi.h>
#include <freertos/queue.h>

#include "config.h"
#include "radio_link.h"
#include "http_telemetry.h"
#include "battery.h"

String pendingJson = "";
bool hasPendingData = false;
portMUX_TYPE dataMux = portMUX_INITIALIZER_UNLOCKED;
QueueHandle_t commandQueue;

void TaskRadio(void *pvParameters) {
    radioInit();
    radioStartListen();
    uint32_t lastBatt = 0;

    while (1) {
        String incoming;
        if (radioCheckRx(incoming)) {
            if (incoming.startsWith("ACK")) {
                Serial.println("ACK del cohete: " + incoming);
            } else {
                Serial.println("Paquete recibido: " + incoming);
                portENTER_CRITICAL(&dataMux);
                pendingJson = incoming;
                hasPendingData = true;
                portEXIT_CRITICAL(&dataMux);
            }
        }

        // El cohete alterna TX (~0.4 s) y escucha (~0.6 s) cada 1 s.
        // Se envia 3 veces con 200 ms de separacion para pegarle a la ventana de escucha.
        char command;
        if (xQueueReceive(commandQueue, &command, 0) == pdTRUE) {
            for (int i = 0; i < 3; i++) {
                vTaskDelay(pdMS_TO_TICKS(200));
                radioSendCommand(command);
            }
        }

        if (millis() - lastBatt >= 5000) {
            lastBatt = millis();
            Serial.printf("Bateria: %.2fV (%.0f%%)\n", batteryVoltage(), batteryPercent());
        }

        vTaskDelay(pdMS_TO_TICKS(10));
    }
}

void TaskHTTP(void *pvParameters) {
    connectWiFi();
    uint8_t retries = 0;
    uint32_t lastCommandPoll = 0;
    while (1) {
        if (millis() - lastCommandPoll >= 1000 &&
            WiFi.status() == WL_CONNECTED && uxQueueSpacesAvailable(commandQueue) > 0) {
            lastCommandPoll = millis();
            String command = httpFetchCommand();
            if (command == "c" || command == "e") {
                char value = command[0];
                xQueueSend(commandQueue, &value, 0);
                Serial.println("Comando del servidor: " + command);
            } else if (command.length()) {
                Serial.println("Comando no soportado por este firmware: " + command);
            }
        }
        if (hasPendingData) {
            String toSend;
            portENTER_CRITICAL(&dataMux);
            toSend = pendingJson;
            hasPendingData = false;
            portEXIT_CRITICAL(&dataMux);

            bool sent = false;
            if (WiFi.status() == WL_CONNECTED) {
                Serial.println("Enviando por WiFi: " + toSend);
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
    batteryInit();
    commandQueue = xQueueCreate(1, sizeof(char));
    if (!commandQueue) {
        Serial.println("No se pudo crear la cola de comandos");
        return;
    }

    xTaskCreatePinnedToCore(TaskRadio, "LoRaRx", 8192, NULL, 2, NULL, 1);
    xTaskCreatePinnedToCore(TaskHTTP, "HTTP", 8192, NULL, 1, NULL, 0);
}

void loop() {
    vTaskDelay(portMAX_DELAY);
}
