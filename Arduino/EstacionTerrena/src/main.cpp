#include <Arduino.h>
#include <WiFi.h>

#include "config.h"
#include "radio_link.h"
#include "http_telemetry.h"
#include "battery.h"

String pendingJson = "";
bool hasPendingData = false;
String pendingCmd = "";
bool hasPendingCmd = false;
portMUX_TYPE dataMux = portMUX_INITIALIZER_UNLOCKED;

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

        if (hasPendingCmd) {
            String cmd;
            portENTER_CRITICAL(&dataMux);
            cmd = pendingCmd;
            hasPendingCmd = false;
            portEXIT_CRITICAL(&dataMux);
            Serial.println("Enviando comando al cohete: " + cmd);
            for (int i = 0; i < 3; i++) {           // 3 intentos para pegarle a la ventana RX del cohete
                radioSendCommand(cmd.c_str());
                vTaskDelay(pdMS_TO_TICKS(150));
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
    uint32_t lastCmdPoll = 0;
    while (1) {
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

        // Consultar comandos pendientes al servidor
        if (millis() - lastCmdPoll >= 500) {
            lastCmdPoll = millis();
            if (WiFi.status() == WL_CONNECTED) {
                String c = httpGetCommand();
                if (c.length() > 0) {
                    String cmd = (c == "c") ? "CAM" : (c == "e") ? "EJECT" : "";
                    if (cmd.length() > 0) {
                        portENTER_CRITICAL(&dataMux);
                        pendingCmd = cmd;
                        hasPendingCmd = true;
                        portEXIT_CRITICAL(&dataMux);
                        Serial.println("Comando del servidor: '" + c + "' -> " + cmd);
                    }
                }
            }
        }

        vTaskDelay(pdMS_TO_TICKS(50));
    }
}

void setup() {
    Serial.begin(115200);
    WiFi.mode(WIFI_STA);
    batteryInit();

    xTaskCreatePinnedToCore(TaskRadio, "LoRaRx", 8192, NULL, 2, NULL, 1);
    xTaskCreatePinnedToCore(TaskHTTP, "HTTP", 8192, NULL, 1, NULL, 0);
}

void loop() {
    vTaskDelay(portMAX_DELAY);
}
