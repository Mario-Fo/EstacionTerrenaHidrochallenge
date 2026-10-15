#include "config.h"
#include "telemetry.h"
#include <Arduino.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <Adafruit_BNO055.h>
#include <TinyGPSPlus.h>

Adafruit_BME280 bme;
Adafruit_BNO055 *bno = nullptr;
uint8_t bnoAddr = 0x28;
TinyGPSPlus gps;
HardwareSerial gpsSerial(1);

bool bmeOK = false;
bool bnoOK = false;
float altitudReferencia = 0;
float lastAlt = 0;
uint32_t lastAltTime = 0;
float accOffX = 0, accOffY = 0, accOffZ = 0;

const uint32_t SENSOR_RETRY_MS = 2000;
uint32_t lastBmeRetry = 0;
uint32_t lastBnoRetry = 0;

volatile uint32_t rpm_pulses = 0;
uint32_t last_rpm_time = 0;
portMUX_TYPE rpmMux = portMUX_INITIALIZER_UNLOCKED;

void IRAM_ATTR rpm_isr() {
    portENTER_CRITICAL_ISR(&rpmMux);
    rpm_pulses++;
    portEXIT_CRITICAL_ISR(&rpmMux);
}

void i2cScan() {
    Serial.println("I2C scan:");
    uint8_t found = 0;
    for (uint8_t addr = 1; addr < 127; addr++) {
        Wire.beginTransmission(addr);
        if (Wire.endTransmission() == 0) {
            Serial.printf("  device at 0x%02X\n", addr);
            found++;
        }
    }
    Serial.printf("I2C devices: %u\n", found);
}

bool i2cPresent(uint8_t addr) {
    Wire.beginTransmission(addr);
    return Wire.endTransmission() == 0;
}

bool bnoInit() {
    delete bno;
    bnoAddr = 0x29;
    bno = new Adafruit_BNO055(55, bnoAddr);
    if (bno->begin()) return true;
    delete bno;
    bnoAddr = 0x28;
    bno = new Adafruit_BNO055(55, bnoAddr);
    return bno->begin();
}

void sensorsInit() {
    Wire.begin(I2C_SDA, I2C_SCL);
    vTaskDelay(pdMS_TO_TICKS(100));
    i2cScan();

    gpsSerial.begin(9600, SERIAL_8N1, GPS_RX_PIN, GPS_TX_PIN);

    pinMode(RPM_PIN, INPUT_PULLUP);
    attachInterrupt(digitalPinToInterrupt(RPM_PIN), rpm_isr, FALLING);

    bmeOK = bme.begin(0x76);
    if (!bmeOK) bmeOK = bme.begin(0x77);
    Serial.printf("BME280: %s\n", bmeOK ? "OK" : "NO");

    bnoOK = bnoInit();
    Serial.printf("BNO055 @0x%02X: %s\n", bnoAddr, bnoOK ? "OK" : "NO");

    if (bnoOK) {
        // Offsets de calibracion (22 bytes). Pega aqui los que imprima el monitor
        // tras calibrar (mueve el sensor en todas las orientaciones hasta 3/3/3/3).
        static const uint8_t BNO_OFFSETS[22] = {0};
        bool hasOffsets = false;
        for (int i = 0; i < 22; i++) if (BNO_OFFSETS[i] != 0) { hasOffsets = true; break; }
        if (hasOffsets) {
            bno->setSensorOffsets(BNO_OFFSETS);
            Serial.println("BNO offsets cargados.");
        }

        uint8_t sys, gyro, accel, mag;
        bno->getCalibration(&sys, &gyro, &accel, &mag);
        Serial.printf("BNO calib sys=%d gyro=%d accel=%d mag=%d (3=calibrado)\n", sys, gyro, accel, mag);
        // offset de aceleracion asumiendo reposo al arranque
        float sx = 0, sy = 0, sz = 0;
        for (int i = 0; i < 20; i++) {
            sensors_event_t ev;
            bno->getEvent(&ev, Adafruit_BNO055::VECTOR_LINEARACCEL);
            sx += ev.acceleration.x; sy += ev.acceleration.y; sz += -ev.acceleration.z;
            vTaskDelay(pdMS_TO_TICKS(50));
        }
        accOffX = sx / 20.0f; accOffY = sy / 20.0f; accOffZ = sz / 20.0f;
        Serial.printf("BNO offset accel: %.2f/%.2f/%.2f\n", accOffX, accOffY, accOffZ);
    }

    if (bmeOK) {
        float suma = 0;
        for (int i = 0; i < 10; i++) {
            suma += bme.readAltitude(1013.25);
            vTaskDelay(pdMS_TO_TICKS(50));
        }
        altitudReferencia = suma / 10.0;
    }
}

void sensorsUpdate() {
    while (gpsSerial.available() > 0) {
        gps.encode(gpsSerial.read());
    }

    uint32_t now = millis();

    xSemaphoreTake(dataMutex, portMAX_DELAY);

    if (gps.location.isValid()) {
        tData.lat = gps.location.lat();
        tData.lng = gps.location.lng();
    }

    if (bmeOK) {
        float p = bme.readPressure() / 100.0F;   // hPa
        float t = bme.readTemperature();
        float h = bme.readHumidity();

        // Validar: BME280 valido = 300..1100 hPa, -40..85 C, 0..100 %
        if (p >= 300.0f && p <= 1100.0f && t >= -40.0f && t <= 85.0f && h >= 0.0f && h <= 100.0f) {
            tData.pres = p;
            tData.temp = t;
            tData.hum = h;

            // altitud desde la presion ya leida (misma referencia 1013.25)
            float currentAlt = 44330.0f * (1.0f - powf(p / 1013.25f, 0.1903f)) - altitudReferencia;

            // limitar el salto por muestra (evita picos por glitch del sensor)
            float dAlt = currentAlt - tData.alt;
            const float MAX_STEP = 3.0f; // m por ciclo (10 Hz)
            if (dAlt >  MAX_STEP) currentAlt = tData.alt + MAX_STEP;
            if (dAlt < -MAX_STEP) currentAlt = tData.alt - MAX_STEP;

            tData.alt = (tData.alt * 0.7f) + (currentAlt * 0.3f);

            // velocidad vertical desde la altitud FILTRADA y el dt real, con pasa-bajos
            uint32_t dtms = now - lastAltTime;
            if (dtms < 1) dtms = 1;
            float vInst = (tData.alt - lastAlt) / (dtms / 1000.0f);
            lastAlt = tData.alt;
            lastAltTime = now;
            tData.velZ = (tData.velZ * 0.6f) + (vInst * 0.4f);
        }
        // lectura invalida: se conservan los ultimos valores validos
    } else if (now - lastBmeRetry >= SENSOR_RETRY_MS) {
        lastBmeRetry = now;
        bmeOK = bme.begin(0x76) || bme.begin(0x77);
    }

    if (bnoOK) {
        sensors_event_t event;
        bno->getEvent(&event, Adafruit_BNO055::VECTOR_LINEARACCEL);
        tData.accX = event.acceleration.x - accOffX;
        tData.accY = event.acceleration.y - accOffY;
        // Montaje invertido: corregir solo Z conservando el signo del movimiento.
        tData.accZ = -event.acceleration.z - accOffZ;
    } else if (now - lastBnoRetry >= SENSOR_RETRY_MS) {
        lastBnoRetry = now;
        bnoOK = bnoInit();
    }

    if (now - last_rpm_time >= 1000) {
        portENTER_CRITICAL(&rpmMux);
        uint32_t pulses = rpm_pulses;
        rpm_pulses = 0;
        portEXIT_CRITICAL(&rpmMux);
        tData.rpm = pulses * 60; // 1 iman por vuelta
        last_rpm_time = now;
    }

    xSemaphoreGive(dataMutex);

    static uint32_t lastDbg = 0;
    if (now - lastDbg >= 5000) {
        lastDbg = now;
        Serial.printf("GPS chars=%u fix=%u sats=%u locValid=%d\n",
                      gps.charsProcessed(), gps.sentencesWithFix(),
                      gps.satellites.value(), gps.location.isValid());
        Serial.printf("BNO ok=%d acc=%.2f/%.2f/%.2f\n",
                      bnoOK, tData.accX, tData.accY, tData.accZ);
        if (bnoOK) {
            uint8_t sys, gyro, accel, mag;
            bno->getCalibration(&sys, &gyro, &accel, &mag);
            Serial.printf("BNO calib sys=%d gyro=%d accel=%d mag=%d\n", sys, gyro, accel, mag);
            if (sys == 3 && gyro == 3 && accel == 3 && mag == 3) {
                uint8_t cal[22];
                if (bno->getSensorOffsets(cal)) {
                    Serial.print("BNO_OFFSETS = {");
                    for (int i = 0; i < 22; i++) Serial.printf("0x%02X%s", cal[i], (i < 21) ? "," : "");
                    Serial.println("};");
                }
            }
        }
    }
}
