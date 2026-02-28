#include <Wire.h>
#include <SPI.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <Servo.h>
#include <TinyGPS++.h>
#include <SoftwareSerial.h>
#include <math.h>

// ===================== BME280 (SPI HARDWARE) =====================
// Constructor SPI: Adafruit_BME280(csPin)
#define BME_CS 10
Adafruit_BME280 bme(BME_CS);

// ===================== GPS =====================
TinyGPSPlus gps;
SoftwareSerial gpsSerial(4, 3); // RX, TX (Arduino RX<-GPS TX)

// ===================== XBEE =====================
// XBee está en RX/TX del Arduino => usamos Serial

// ===================== SERVO =====================
Servo servoParacaidas;
const int SERVO_PIN = 9;

bool paracaidasAbierto = false;
bool vueloIniciado = false;

int contadorInicio = 0;
int contadorCaida  = 0;

float temp = 0, hum = 0, pres = 0;
double latitude = 0, longitude = 0, altitude = 0;

// --- UMBRALES (AJUSTABLES) ---
const float UMBRAL_INICIO = 1.10;   // g
const int   LECTURAS_INICIO = 2;

const float UMBRAL_CAIDA = 0.70;    // g
const int   LECTURAS_CAIDA = 3;

// ===================== MPU6050 (I2C) =====================
const int MPU6050_ADDR = 0x68;
const int PWR_MGMT_1   = 0x6B;
const int ACCEL_XOUT_H = 0x3B;

int16_t leerDato(int direccion, int registro);

// ====== TELEMETRÍA PRO (buffers) ======
char payload[320];

char pres_s[16], temp_s[16], hum_s[16];
char lat_s[20],  lon_s[20],  alt_s[16];
char ax_s[16],   ay_s[16],   az_s[16];

void setup() {
  // Serial = XBee (D0/D1)
  Serial.begin(9600);

  // I2C SOLO para MPU6050
  Wire.begin(); // A4/A5

  // GPS
  gpsSerial.begin(9600);

  // Servo
  servoParacaidas.attach(SERVO_PIN);
  servoParacaidas.write(0);

  // Iniciar MPU6050
  Wire.beginTransmission(MPU6050_ADDR);
  Wire.write(PWR_MGMT_1);
  Wire.write(0);
  Wire.endTransmission(true);

  // Iniciar BME280 por SPI hardware (pines 10-13 del UNO)
  // (CS en D10; SCK D13; MOSI D11; MISO D12)
  if (!bme.begin()) {
    Serial.println("ERROR: No se detecto BME280 por SPI. Revisa CSB/SCL/SDA/SDO y VCC.");
  }
}

void loop() {
  // --- BME280 (SPI) ---
  temp = bme.readTemperature();      // °C
  hum  = bme.readHumidity();         // %
  pres = bme.readPressure() / 100.0; // hPa

  // --- GPS ---
  while (gpsSerial.available() > 0) {
    gps.encode(gpsSerial.read());
  }

  if (gps.location.isUpdated()) {
    latitude  = gps.location.lat();
    longitude = gps.location.lng();
  }
  if (gps.altitude.isUpdated()) {
    altitude  = gps.altitude.meters();
  }

  // --- MPU6050 (I2C) ---
  int16_t ax_raw = leerDato(MPU6050_ADDR, ACCEL_XOUT_H);
  int16_t ay_raw = leerDato(MPU6050_ADDR, ACCEL_XOUT_H + 2);
  int16_t az_raw = leerDato(MPU6050_ADDR, ACCEL_XOUT_H + 4);

  float acelX = ax_raw / 16384.0;
  float acelY = ay_raw / 16384.0;
  float acelZ = az_raw / 16384.0;

  float aMag = sqrt(acelX * acelX + acelY * acelY + acelZ * acelZ);

  // --- DETECCIÓN DE LANZAMIENTO ---
  if (!vueloIniciado) {
    if (aMag > UMBRAL_INICIO) contadorInicio++;
    else contadorInicio = 0;

    if (contadorInicio >= LECTURAS_INICIO) {
      vueloIniciado = true;
    }
  }

  if (vueloIniciado && !paracaidasAbierto) {
    if (aMag < UMBRAL_CAIDA) contadorCaida++;
    else contadorCaida = 0;

    if (contadorCaida >= LECTURAS_CAIDA) {
      paracaidasAbierto = true;
      servoParacaidas.write(180);
    }
  }

  // ====== TELEMETRÍA (JSON PRO sin String) ======
  // Convertir números a texto (UNO friendly)
  dtostrf(pres,      0, 2, pres_s);
  dtostrf(temp,      0, 2, temp_s);
  dtostrf(hum,       0, 2, hum_s);

  dtostrf(latitude,  0, 6, lat_s);
  dtostrf(longitude, 0, 6, lon_s);
  dtostrf(altitude,  0, 2, alt_s);

  dtostrf(acelX,     0, 2, ax_s);
  dtostrf(acelY,     0, 2, ay_s);
  dtostrf(acelZ,     0, 2, az_s);

  // Construir JSON en buffer fijo (sin fragmentación)
  snprintf(payload, sizeof(payload),
    "{\"code\":\"BRAVO-II\","
    "\"pres\":%s,\"temp\":%s,\"hum\":%s,"
    "\"lat\":%s,\"long\":%s,\"alt\":%s,"
    "\"accX\":%s,\"accY\":%s,\"accZ\":%s,"
    "\"vuelo\":%s,\"paracaidas\":%s}",
    pres_s, temp_s, hum_s,
    lat_s, lon_s, alt_s,
    ax_s, ay_s, az_s,
    vueloIniciado ? "true" : "false",
    paracaidasAbierto ? "true" : "false"
  );

  // Enviar por XBee (Serial D0/D1)
  Serial.println(payload);

  delay(500);
}

int16_t leerDato(int direccion, int registro) {
  Wire.beginTransmission(direccion);
  Wire.write(registro);
  Wire.endTransmission(false);
  Wire.requestFrom(direccion, 2, true);
  int16_t dato = (Wire.read() << 8) | Wire.read();
  return dato;
}