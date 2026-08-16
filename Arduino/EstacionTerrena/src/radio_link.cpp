#include "config.h"
#include "radio_link.h"
#include <SPI.h>
#include <RadioLib.h>

LLCC68 radio = new Module(LORA_CS, LORA_DIO1, LORA_RST, LORA_BUSY);

void radioInit() {
    SPI.begin(LORA_SCK, LORA_MISO, LORA_MOSI, LORA_CS);
    int state = radio.begin(LORA_FREQ, LORA_BW, LORA_SF, LORA_CR, LORA_SYNCWORD, LORA_PWR, LORA_PREAMBLE);
    if (state == RADIOLIB_ERR_NONE) {
        Serial.println("Receptor LoRa inicializado.");
    } else {
        Serial.println("Fallo inicializando LoRa.");
    }
}

bool radioReceive(String &out, uint32_t timeout) {
    return radio.receive(out, timeout) == RADIOLIB_ERR_NONE;
}

void radioTransmitCommand(const String &cmd) {
    radio.transmit(cmd.c_str());
}
