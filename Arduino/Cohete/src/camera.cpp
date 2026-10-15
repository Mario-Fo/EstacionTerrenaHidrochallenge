#include <Arduino.h>
#include <atomic>
#include "config.h"
#include "camera.h"

static std::atomic<bool> requested(false);
static bool active = false;
static uint32_t started = 0;

void cameraInit() {
    digitalWrite(CAMERA_PIN, !CAMERA_ACTIVE_LEVEL);
    // HIGH en drenador abierto libera el contacto; no aplica 3.3 V.
    pinMode(CAMERA_PIN, OUTPUT_OPEN_DRAIN);
}

void cameraRequest() {
    requested.store(true);
}

void cameraUpdate() {
    const uint32_t now = millis();
    // Una solicitud durante el pulso no lo prolonga ni lo repite.
    if (requested.exchange(false) && !active) {
        started = now;
        active = true;
        digitalWrite(CAMERA_PIN, CAMERA_ACTIVE_LEVEL);
    }
    if (active && uint32_t(now - started) >= CAMERA_PULSE_MS) {
        digitalWrite(CAMERA_PIN, !CAMERA_ACTIVE_LEVEL);
        active = false;
    }
}
