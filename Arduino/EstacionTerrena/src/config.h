#pragma once

// Modulo LoRa: LLCC68 (pines detectados con el escaner)
#define LORA_SCK  10
#define LORA_MISO 8
#define LORA_MOSI 9
#define LORA_CS   11
#define LORA_RST  1
#define LORA_DIO1 6
#define LORA_BUSY 7


#define BATT_PIN      2
#define BATT_DIVISOR  2.0F
#define BATT_MIN_V    3.3F
#define BATT_MAX_V    4.2F

#define LORA_FREQ     915.0
#define LORA_BW       125.0
#define LORA_SF       7
#define LORA_CR       7
#define LORA_SYNCWORD 0x12
#define LORA_PWR      22
#define LORA_PREAMBLE 8
#define LORA_TCXO     0.0

#define SERVER_URL "http://172.28.32.128:8000/api/lecturas-multi"
