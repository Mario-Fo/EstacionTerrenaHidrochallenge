#include "config.h"
#include "telemetry.h"
#include <Arduino.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <Adafruit_BNO055.h>
#include <TinyGPSPlus.h>

Adafruit_BME280 bme;
Adafruit_BNO055 bno = Adafruit_BNO055(55, 0x28);
TinyGPSPlus gps;
HardwareSerial gpsSerial(1);

bool bmeOK = false;
bool bnoOK = false;
float altitudReferencia = 0;
float lastAlt = 0;

volatile uint32_t rpm_pulses = 0;
uint32_t last_rpm_time = 0;
portMUX_TYPE rpmMux = portMUX_INITIALIZER_UNLOCKED;

void IRAM_ATTR rpm_isr() {
    portENTER_CRITICAL_ISR(&rpmMux);
    rpm_pulses++;
    portEXIT_CRITICAL_ISR(&rpmMux);
}

void sensorsInit() {
    Wire.begin(I2C_SDA, I2C_SCL);
    gpsSerial.begin(9600, SERIAL_8N1, GPS_RX_PIN, GPS_TX_PIN);

    pinMode(RPM_PIN, INPUT_PULLUP);
    attachInterrupt(digitalPinToInterrupt(RPM_PIN), rpm_isr, FALLING);

    bmeOK = bme.begin(0x76);
    if (!bmeOK) bmeOK = bme.begin(0x77);

    bnoOK = bno.begin();

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
    } else {
        bmeOK = bme.begin(0x76) || bme.begin(0x77);
    }

    if (bnoOK) {
        sensors_event_t event;
        bno.getEvent(&event, Adafruit_BNO055::VECTOR_LINEARACCEL);
        tData.accX = event.acceleration.x;
        tData.accY = event.acceleration.y;
        tData.accZ = event.acceleration.z;
    } else {
        bnoOK = bno.begin();
    }

    uint32_t now = millis();
    if (now - last_rpm_time >= 1000) {
        portENTER_CRITICAL(&rpmMux);
        uint32_t pulses = rpm_pulses;
        rpm_pulses = 0;
        portEXIT_CRITICAL(&rpmMux);
        tData.rpm = pulses * 60; // 1 iman por vuelta
        last_rpm_time = now;
    }

    xSemaphoreGive(dataMutex);
}
