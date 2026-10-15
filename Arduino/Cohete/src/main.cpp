#include <Arduino.h>
#include <esp_task_wdt.h>
#include <WiFi.h>

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

void handleCommand(const String &cmd) {
    if (cmd == "CAM") {
        Serial.println("CMD: camara ON (boton 1s)");
        digitalWrite(CAM_PIN, HIGH);
        vTaskDelay(pdMS_TO_TICKS(1000));
        digitalWrite(CAM_PIN, LOW);
        radioSendAck("ACK:CAM");
    } else if (cmd == "EJECT") {
        Serial.println("CMD: eyeccion paracaidas");
        xSemaphoreTake(dataMutex, portMAX_DELAY);
        tData.paracaidas_eyectado = true;
        xSemaphoreGive(dataMutex);
        ledcWrite(SERVO_CH, 102);
        radioSendAck("ACK:EJECT");
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

        if (radioOK) {
            if (millis() - lastTx >= 1000) {
                lastTx = millis();
                radioTransmit();
            }
            // ventana de escucha de comandos (la estacion responde con CAM/EJECT)
            String cmd;
            if (radioCheckCommand(cmd, 300)) {
                handleCommand(cmd);
            }
        } else if (millis() - lastTx >= 1000) {
            lastTx = millis();
            Serial.print("TELEMETRIA (sin LoRa): ");
            Serial.println(telemetryJson());
        }

        vTaskDelay(pdMS_TO_TICKS(10));
    }
}

void setup() {
    setCpuFrequencyMhz(80);   // baja el CPU a 80 MHz (ahorro de bateria)
    WiFi.mode(WIFI_OFF);      // apaga WiFi
    btStop();                 // apaga Bluetooth/BLE

    Serial.begin(115200);

    // Margen amplio para tolerar bloqueos puntuales de LoRa/I2C (timeout en segundos)
    esp_task_wdt_init(5, true);

    dataMutex = xSemaphoreCreateMutex();

    pinMode(CAM_PIN, OUTPUT);
    digitalWrite(CAM_PIN, LOW); // boton de camara sin pulsar

    ledcSetup(SERVO_CH, 50, 10);
    ledcAttachPin(SERVO_PIN, SERVO_CH);
    ledcWrite(SERVO_CH, 51); // 0 grados

    xTaskCreatePinnedToCore(TaskSensors, "Sensors", 4096, NULL, 2, NULL, 0);
    xTaskCreatePinnedToCore(TaskLoRa, "LoRaTx", 8192, NULL, 1, NULL, 1);
}

void loop() {
    vTaskDelay(portMAX_DELAY);
}
