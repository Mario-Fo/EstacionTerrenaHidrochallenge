#include "config.h"
#include "radio_link.h"
#include "telemetry.h"
#include <Arduino.h>
#include <WiFi.h>
#include <esp_now.h>
#include <esp_wifi.h>
#include <ArduinoJson.h>
#include "camera.h"
#include "flight.h"

bool radioOK = false;

static uint8_t broadcastMac[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};

// Comando recibido por ESP-NOW (se procesa en radioPollCommand, fuera del callback).
static volatile bool commandReceived = false;
static volatile char commandChar = 0;

static void onDataRecv(const uint8_t *mac_addr, const uint8_t *data, int data_len) {
    (void)mac_addr;
    if (data_len == 1) {
        char c = (char)data[0];
        if (c == 'c' || c == 'e') {
            commandChar = c;
            commandReceived = true;
        }
    }
}

bool radioInit() {
    pinMode(LED_PIN, OUTPUT);
    digitalWrite(LED_PIN, LOW);

    WiFi.mode(WIFI_STA);
    WiFi.disconnect();
    esp_wifi_set_channel(ESPNOW_CHANNEL, WIFI_SECOND_CHAN_NONE);

    if (esp_now_init() != ESP_OK) {
        Serial.println("ESP-NOW init fallo");
        radioOK = false;
        return false;
    }
    esp_now_register_recv_cb(onDataRecv);

    esp_now_peer_info_t peer = {};
    memcpy(peer.peer_addr, broadcastMac, 6);
    peer.channel = ESPNOW_CHANNEL;
    peer.encrypt = false;
    if (esp_now_add_peer(&peer) != ESP_OK) {
        Serial.println("ESP-NOW add_peer fallo");
        radioOK = false;
        return false;
    }
    Serial.println("ESP-NOW inicializado.");
    radioOK = true;
    return true;
}

const char *stateName(FlightState s) {
    switch (s) {
        case EN_ESPERA:  return "ESPERA";
        case ASCENSO:    return "ASCENSO";
        case DESACOPLE:  return "DESACOPLE";
        case DESCENSO:   return "DESCENSO";
        case ATERRIZADO: return "ATERRIZADO";
    }
    return "?";
}

String telemetryJson() {
    JsonDocument doc;
    JsonArray lecturas = doc["lecturas"].to<JsonArray>();
    JsonObject item = lecturas.add<JsonObject>();
    item["id"] = "HYDRONAUTAS";

    xSemaphoreTake(dataMutex, portMAX_DELAY);
    item["pres"] = serialized(String(tData.pres, 2));
    item["temp"] = serialized(String(tData.temp, 2));
    item["hum"] = serialized(String(tData.hum, 2));
    item["lat"] = serialized(String(tData.lat, 5));
    item["long"] = serialized(String(tData.lng, 5));
    item["alt"] = serialized(String(tData.alt, 2));
    item["velZ"] = serialized(String(tData.velZ, 2));
    item["accX"] = serialized(String(tData.accX, 2));
    item["accY"] = serialized(String(tData.accY, 2));
    item["accZ"] = serialized(String(tData.accZ, 2));
    item["RPM"] = tData.rpm;
    item["deployed"] = tData.paracaidas_eyectado;
    item["state"] = stateName(flightState);
    xSemaphoreGive(dataMutex);

    String out;
    serializeJson(doc, out);
    return out;
}

void radioTransmit() {
    if (!radioOK) return;

    String jsonStr = telemetryJson();

    Serial.print("TX: ");
    Serial.println(jsonStr);
    esp_err_t r = esp_now_send(broadcastMac, (uint8_t*)jsonStr.c_str(), jsonStr.length());
    digitalWrite(LED_PIN, !digitalRead(LED_PIN));

    if (r != ESP_OK) {
        Serial.printf("TX fallo: %d\n", r);
    }
}

void radioPollCommand() {
    if (!radioOK || !commandReceived) return;
    commandReceived = false;
    char command = commandChar;
    if (command == 'c') {
        cameraRequest();
        Serial.println("Camara: pulso solicitado (1 segundo)");
        radioSendAck("ACK:c");
    } else if (command == 'e') {
        Serial.println(flightDeploy() ? "Servo: despliegue solicitado por ESP-NOW"
                                     : "Servo: ya desplegado, comando ignorado");
        radioSendAck("ACK:e");
    }
}

void radioSendAck(const char *msg) {
    if (!radioOK) return;
    esp_now_send(broadcastMac, (uint8_t*)msg, strlen(msg));
}
