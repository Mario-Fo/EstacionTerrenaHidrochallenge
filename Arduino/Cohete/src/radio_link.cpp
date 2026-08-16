#include "config.h"
#include "telemetry.h"
#include <Arduino.h>
#include <SPI.h>
#include <RadioLib.h>
#include <ArduinoJson.h>

LLCC68 radio = new Module(LORA_CS, LORA_DIO1, LORA_RST, LORA_BUSY);

void radioInit() {
    SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_CS);
    int state = radio.begin(LORA_FREQ, LORA_BW, LORA_SF, LORA_CR, LORA_SYNCWORD, LORA_PWR, LORA_PREAMBLE);
    if (state != RADIOLIB_ERR_NONE) {
        Serial.print("LoRa fallo, codigo: ");
        Serial.println(state);
    } else {
        Serial.println("LoRa inicializado.");
    }
}

void radioTransmit() {
    StaticJsonDocument<512> doc;
    doc["ok"] = true;
    JsonObject data = doc.createNestedObject("data");
    data["id"] = "HYDRONAUTAS";

    xSemaphoreTake(dataMutex, portMAX_DELAY);
    data["pres"] = tData.pres;
    data["temp"] = tData.temp;
    data["hum"] = tData.hum;
    data["lat"] = tData.lat;
    data["long"] = tData.lng;
    data["alt"] = tData.alt;
    data["velZ"] = tData.velZ;
    data["accX"] = tData.accX;
    data["accY"] = tData.accY;
    data["accZ"] = tData.accZ;
    data["RPM"] = tData.rpm;
    data["deployed"] = tData.paracaidas_eyectado;
    xSemaphoreGive(dataMutex);

    String jsonStr;
    serializeJson(doc, jsonStr);
    radio.transmit(jsonStr);
}

void radioProcessCommands() {
    String incoming;
    if (radio.receive(incoming, 100) != RADIOLIB_ERR_NONE) return;

    if (incoming == "CMD_EJECT") {
        xSemaphoreTake(dataMutex, portMAX_DELAY);
        tData.paracaidas_eyectado = true;
        ledcWrite(SERVO_PIN, 102);
        xSemaphoreGive(dataMutex);
    }
}
