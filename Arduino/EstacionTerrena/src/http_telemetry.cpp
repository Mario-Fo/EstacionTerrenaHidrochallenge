#include "config.h"
#include "http_telemetry.h"
#include "secrets.h"
#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>

void connectWiFi() {
    if (WiFi.status() == WL_CONNECTED) return;
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.print("Conectando a WiFi...");
    unsigned long start = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - start < 5000) {
        delay(500);
        Serial.print(".");
    }
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nWiFi Conectado!");
    } else {
        Serial.println("\nWiFi Fallo.");
    }
}

bool httpPost(const String &payload) {
    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");

    int code = http.POST(payload);
    if (code > 0) {
        Serial.printf("Server OK: %d\n", code);
    } else {
        Serial.printf("Server Err: %s\n", http.errorToString(code).c_str());
    }
    http.end();
    return code > 0;
}
