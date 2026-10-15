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
#define SERVO_CH  0
#define SERVO_PIN 8
#define SERVO_FREQ_HZ 50
#define SERVO_RESOLUTION_BITS 10
// 10 bits @ 50 Hz: 51 ~= 1.0 ms (0°), 77 ~= 1.5 ms (90°), 102 ~= 2.0 ms (180°).
// Al desplegar, el servo oscila 90° de un lado a otro (anti-atasco) durante 5 s.
#define SERVO_CLOSED_DUTY 51      // reposo
#define SERVO_DEPLOYED_DUTY 77    // desplegado (90° respecto del reposo)
#define SERVO_WIGGLE_MS 5000      // duracion de la sacudida
#define SERVO_WIGGLE_PERIOD_MS 800 // ciclo completo sube+baja
#define RPM_PIN   4

#define LED_PIN 48

// ESP-NOW (reemplaza LoRa): telemetria por broadcast JSON + comandos 'c'/'e' de 1 byte.
#define ESPNOW_CHANNEL 1

#define LORA_FREQ     915.0
#define LORA_BW       125.0
#define LORA_SF       7
#define LORA_CR       7
#define LORA_SYNCWORD 0x12
#define LORA_PWR      14
#define LORA_PREAMBLE 8

// Deteccion de vuelo (ajustar en banco con datos reales)
#define LAUNCH_VEL_MS     5.0f    // velocidad vertical (baro) para declarar despegue
#define LAUNCH_ACC_MS2    15.0f   // aceleracion vertical (BNO) alternativa para despegue
#define APOGEE_MIN_ALT_M  30.0f   // altitud minima para declarar apogee
#define APOGEE_CONFIRM    4       // ciclos consecutivos confirmando apogee
#define DESCENT_VEL_MS    -1.0f   // velocidad para pasar a DESCENSO
#define LANDING_VEL_MS    0.5f    // velocidad "quieto" para aterrizaje
#define LANDING_ALT_M     5.0f    // altitud "bajo" para aterrizaje
