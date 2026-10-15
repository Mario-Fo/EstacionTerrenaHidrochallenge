#pragma once

#define I2C_SDA 6
#define I2C_SCL 5

#define LORA_SCK  12
#define LORA_MISO 13
#define LORA_MOSI 11
#define LORA_CS   9
#define LORA_RST  1
#define LORA_DIO1 3
#define LORA_BUSY 2

#define GPS_TX_PIN 43
#define GPS_RX_PIN 44

#define SERVO_PIN 8
#define SERVO_CH  0
#define CAM_PIN   7
#define RPM_PIN   4

#define LED_PIN 48

#define LORA_FREQ     915.0
#define LORA_BW       125.0
#define LORA_SF       7
#define LORA_CR       7
#define LORA_SYNCWORD 0x12
#define LORA_PWR      14
#define LORA_PREAMBLE 8
