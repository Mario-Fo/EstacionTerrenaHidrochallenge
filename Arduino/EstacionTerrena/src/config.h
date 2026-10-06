#pragma once

// Modulo LoRa: Ebyte E22-900M22S 
#define LORA_SCK  6
#define LORA_MISO 8
#define LORA_MOSI 9
#define LORA_CS   10
#define LORA_RST  11
#define LORA_DIO1 12
#define LORA_BUSY 13


#define BATT_PIN      7
#define BATT_DIVISOR  2.0F
#define BATT_MIN_V    3.3F
#define BATT_MAX_V    4.2F

#define LORA_FREQ     915.0
#define LORA_BW       125.0
#define LORA_SF       9
#define LORA_CR       7
#define LORA_SYNCWORD 0x12
#define LORA_PWR      22
#define LORA_PREAMBLE 8
#define LORA_TCXO     2.2

#define SERVER_URL "http://10.126.196.128:8000/api/lecturas-multi"
