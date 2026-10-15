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

#define CAMERA_PIN 7
// Boton activo a GND: drenador abierto, LOW pulsa y HIGH libera.
#define CAMERA_ACTIVE_LEVEL LOW
#define CAMERA_PULSE_MS 1000
// El GPIO 7 ya no se utiliza como salida PWM del servo.
#define SERVO_CH  0
#define SERVO_PIN 4
#define SERVO_FREQ_HZ 50
#define SERVO_RESOLUTION_BITS 10
// Mantiene los pulsos usados originalmente: aprox. 1 ms y 2 ms.
// Ajustar al recorrido mecanico y a las especificaciones del servo.
#define SERVO_CLOSED_DUTY 51
#define SERVO_DEPLOYED_DUTY 102
#define RPM_PIN   8

#define LED_PIN 48

#define LORA_FREQ     915.0
#define LORA_BW       125.0
#define LORA_SF       7
#define LORA_CR       7
#define LORA_SYNCWORD 0x12
#define LORA_PWR      22
#define LORA_PREAMBLE 8
