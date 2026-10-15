#include "config.h"
#include "radio_link.h"
#include <Arduino.h>
#include <WiFi.h>
#include <esp_now.h>
#include <esp_wifi.h>

static uint8_t broadcastMac[6] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};

static volatile bool packetReady = false;
static portMUX_TYPE rxBufMux = portMUX_INITIALIZER_UNLOCKED;
static uint8_t rxBuf[250];
static volatile size_t rxLen = 0;

// Paquetes de mas de 1 byte = telemetria JSON; "ACK:..." = confirmacion del cohete.
static void onDataRecv(const uint8_t *mac_addr, const uint8_t *data, int data_len) {
    (void)mac_addr;
    if (data_len <= 0 || (size_t)data_len > sizeof(rxBuf)) return;
    portENTER_CRITICAL_ISR(&rxBufMux);
    memcpy(rxBuf, data, data_len);
    rxLen = (size_t)data_len;
    packetReady = true;
    portEXIT_CRITICAL_ISR(&rxBufMux);
}

void radioInit() {
    if (esp_now_init() != ESP_OK) {
        Serial.println("ESP-NOW init fallo");
        return;
    }
    esp_now_register_recv_cb(onDataRecv);

    esp_now_peer_info_t peer = {};
    memcpy(peer.peer_addr, broadcastMac, 6);
    peer.channel = ESPNOW_CHANNEL;
    peer.encrypt = false;
    if (esp_now_add_peer(&peer) != ESP_OK) {
        Serial.println("ESP-NOW: no se pudo agregar peer broadcast");
        return;
    }
    Serial.println("ESP-NOW inicializado.");
}

void radioStartListen() {
    // ESP-NOW entrega por callback; no hay que re-armer nada.
}

bool radioCheckRx(String &out) {
    if (!packetReady) return false;
    portENTER_CRITICAL(&rxBufMux);
    size_t len = rxLen;
    uint8_t buf[251];
    memcpy(buf, rxBuf, len);
    packetReady = false;
    portEXIT_CRITICAL(&rxBufMux);
    buf[len] = 0;
    out = String((char*)buf);
    return true;
}

bool radioSendCommand(char command) {
    const esp_err_t st = esp_now_send(broadcastMac, (uint8_t*)&command, 1);
    Serial.printf("Comando ESP-NOW %c: %d\n", command, st);
    return st == ESP_OK;
}
