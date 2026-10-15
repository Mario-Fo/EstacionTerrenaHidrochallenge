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

<<<<<<< HEAD
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
    http.end();
    command.trim();
    return command;
=======
String httpGetCommand() {
    HTTPClient http;
    http.begin(COMMAND_URL);
    int code = http.GET();
    String cmd = "";
    if (code > 0) {
        cmd = http.getString();
        cmd.trim();
    }
    http.end();
    return cmd;
>>>>>>> cb0335b8459810b43a28a7ece3191b64527a81b7
}
