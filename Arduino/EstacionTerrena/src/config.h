#pragma once

// ESP-NOW (reemplaza LoRa) por broadcast. Ambas placas en el mismo canal
// que el WiFi 2.4 GHz de la red local (Pixel 6: su lado 2.4 GHz es canal 1).
#define ESPNOW_CHANNEL 1

#define BATT_PIN      8
#define BATT_DIVISOR  2.0F
#define BATT_MIN_V    3.3F
#define BATT_MAX_V    4.2F

// Servidor Laravel que corre en la laptop conectada a esta misma red
#define SERVER_URL "http://10.143.41.144:8000/api/lecturas-multi"
