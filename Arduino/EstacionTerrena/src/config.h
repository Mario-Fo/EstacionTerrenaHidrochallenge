#pragma once

#define LORA_SCK  5
#define LORA_MISO 7
#define LORA_MOSI 6
#define LORA_CS   4
#define LORA_RST  3
#define LORA_DIO1 2
#define LORA_BUSY 1

#define LORA_FREQ     915.0
#define LORA_BW       125.0
#define LORA_SF       9
#define LORA_CR       7
#define LORA_SYNCWORD 0x12
#define LORA_PWR      22
#define LORA_PREAMBLE 8

// IP generica para que el companero la edite (Servidor de Base de Datos)
#define SERVER_URL "http://0.0.0.0:8000/telemetry"
