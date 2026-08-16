#pragma once

#include <freertos/FreeRTOS.h>
#include <freertos/semphr.h>

enum FlightState { EN_ESPERA, ASCENSO, APOGEO, DESCENSO, ATERRIZADO };

struct TelemetryData {
    float pres = 0;
    float temp = 0;
    float hum = 0;
    float lat = 0;
    float lng = 0;
    float alt = 0;
    float accX = 0;
    float accY = 0;
    float accZ = 0;
    uint32_t rpm = 0;
    float velZ = 0;
    bool paracaidas_eyectado = false;
};

extern SemaphoreHandle_t dataMutex;
extern TelemetryData tData;
extern FlightState flightState;
