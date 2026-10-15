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
        String resp = http.getString();
        if (resp.length()) Serial.println("Resp: " + resp);
    } else {
        Serial.printf("Server Err: %s\n", http.errorToString(code).c_str());
    }
    http.end();
    return code > 0;
}

String httpFetchCommand() {
    String url = SERVER_URL;
    const int api = url.indexOf("/api/");
    if (api < 0) return "";
    url = url.substring(0, api) + "/api/comandos/pendiente";
    HTTPClient http;
    http.setTimeout(2000);
    http.begin(url);
    const int status = http.GET();
    String command = status == 200 ? http.getString() : "";
    Serial.printf("fetch cmd: status=%d cmd='%s'\n", status, command.c_str());
    http.end();
    command.trim();
    return command;
}
