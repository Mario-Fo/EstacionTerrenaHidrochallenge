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
        tData.pres = bme.readPressure() / 100.0F;
        tData.temp = bme.readTemperature();
        tData.hum = bme.readHumidity();

        float currentAlt = bme.readAltitude(1013.25) - altitudReferencia;
        tData.alt = (tData.alt * 0.7) + (currentAlt * 0.3);

        // La tarea corre a 10 Hz: el delta de 100 ms se multiplica por 10
        tData.velZ = (currentAlt - lastAlt) * 10.0F;
        lastAlt = currentAlt;
    } else if (now - lastBmeRetry >= SENSOR_RETRY_MS) {
        lastBmeRetry = now;
        bmeOK = bme.begin(0x76) || bme.begin(0x77);
    }

    if (bnoOK) {
        sensors_event_t event;
        bno->getEvent(&event, Adafruit_BNO055::VECTOR_LINEARACCEL);
        tData.accX = event.acceleration.x;
        tData.accY = event.acceleration.y;
        tData.accZ = event.acceleration.z;
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
    if (now - lastDbg >= 2000) {
        lastDbg = now;
        Serial.printf("GPS chars=%u fix=%u sats=%u locValid=%d\n",
                      gps.charsProcessed(), gps.sentencesWithFix(),
                      gps.satellites.value(), gps.location.isValid());
        Serial.printf("BNO ok=%d acc=%.2f/%.2f/%.2f\n",
                      bnoOK, tData.accX, tData.accY, tData.accZ);
    }
}
