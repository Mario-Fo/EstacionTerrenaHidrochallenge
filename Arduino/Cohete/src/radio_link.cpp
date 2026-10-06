#include "config.h"
#include "telemetry.h"
#include <Arduino.h>
#include <SPI.h>
#include <RadioLib.h>
#include <ArduinoJson.h>

LLCC68 radio = new Module(LORA_CS, LORA_DIO1, LORA_RST, LORA_BUSY);
bool radioOK = false;

bool radioInit() {
    pinMode(LED_PIN, OUTPUT);
    digitalWrite(LED_PIN, LOW);
    SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_CS);
    int state = radio.begin(LORA_FREQ, LORA_BW, LORA_SF, LORA_CR, LORA_SYNCWORD, LORA_PWR, LORA_PREAMBLE);
    if (state != RADIOLIB_ERR_NONE) {
        Serial.print("LoRa fallo, codigo: ");
        Serial.println(state);
        radioOK = false;
        return false;
    }
    Serial.println("LoRa inicializado.");
    radioOK = true;
    return true;
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
    item["accX"] = serialized(String(tData.accX, 2));
    item["accY"] = serialized(String(tData.accY, 2));
    item["accZ"] = serialized(String(tData.accZ, 2));
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
    int state = radio.transmit(jsonStr);
    digitalWrite(LED_PIN, !digitalRead(LED_PIN));

    if (state != RADIOLIB_ERR_NONE) {
        Serial.print("TX fallo, codigo: ");
        Serial.println(state);
    }
}
