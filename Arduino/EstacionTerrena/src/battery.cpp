#include "config.h"
#include "battery.h"
#include <Arduino.h>

void batteryInit() {
    analogReadResolution(12);
    analogSetPinAttenuation(BATT_PIN, ADC_11db);
}

float batteryVoltage() {
    float vAdc = analogRead(BATT_PIN) * 3.3F / 4095.0F;
    return vAdc * BATT_DIVISOR;
}

float batteryPercent() {
    float pct = (batteryVoltage() - BATT_MIN_V) / (BATT_MAX_V - BATT_MIN_V) * 100.0F;
    if (pct < 0.0F) pct = 0.0F;
    if (pct > 100.0F) pct = 100.0F;
    return pct;
}