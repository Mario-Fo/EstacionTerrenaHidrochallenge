#include "config.h"
#include "radio_link.h"
#include <SPI.h>
#include <RadioLib.h>

SX1262 radio = new Module(LORA_CS, LORA_DIO1, LORA_RST, LORA_BUSY);

void radioInit() {
    SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_CS);
    int state = radio.begin(LORA_FREQ, LORA_BW, LORA_SF, LORA_CR, LORA_SYNCWORD, LORA_PWR, LORA_PREAMBLE, LORA_TCXO);
    if (state == RADIOLIB_ERR_NONE) {
        Serial.println("Receptor LoRa inicializado.");
    } else {
        Serial.print("Fallo inicializando LoRa, codigo: ");
        Serial.println(state);
    }
}

volatile bool rxFlag = false;
void IRAM_ATTR onRx() { rxFlag = true; }

void radioStartListen() {
    radio.setPacketReceivedAction(onRx);
    radio.startReceive();
}

bool radioCheckRx(String &out) {
    if (!rxFlag) return false;
    rxFlag = false;
    uint8_t buf[256];
    size_t len = radio.getPacketLength();
    if (len > 255) len = 255;
    int16_t st = radio.readData(buf, len);
    float rssi = radio.getRSSI();
    float snr = radio.getSNR();
    radio.startReceive();
    if (st == RADIOLIB_ERR_NONE) {
        buf[len] = 0;
        out = String((char*)buf);
        Serial.printf("RSSI=%.0f SNR=%.1f\n", rssi, snr);
        return true;
    }
    Serial.printf("readData err=%d RSSI=%.0f SNR=%.1f\n", st, rssi, snr);
    return false;
}
