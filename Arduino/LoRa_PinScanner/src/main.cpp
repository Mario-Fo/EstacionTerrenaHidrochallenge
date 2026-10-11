// Escaner de pines del modulo LoRa (SX1262 / LLCC68 / E22 / E220)
//  Fase 1: detecta SCK/MOSI/MISO/CS leyendo el registro de version (0x0320)
//          buscando "SX12" o "LLCC". No necesita RST ni BUSY.
//  Fase 2: detecta RST (el pin que al ponerlo en bajo hace fallar la lectura).
//  Fase 3: con RST hallado, resetea y ve que pin pulsa en alto (BUSY); el otro
//          conectado es DIO1.
// No usa RadioLib: sirve igual para SX1262 (TCXO) y LLCC68 (cristal).

#include <Arduino.h>
#include <SPI.h>
#include <RadioLib.h>

const int CAND[] = {1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13};
const int NC = sizeof(CAND) / sizeof(CAND[0]);

#define CMD_READ_REGISTER   0x1D
#define REG_VERSION_STRING  0x0320

int g_sck = -1, g_mosi = -1, g_miso = -1, g_cs = -1, g_rst = -1;

bool hasPat(const uint8_t *b, const char *pat, int plen) {
    for (int i = 0; i + plen <= 16; i++) {
        bool ok = true;
        for (int j = 0; j < plen; j++) if ((char)b[i + j] != pat[j]) { ok = false; break; }
        if (ok) return true;
    }
    return false;
}

bool isSPI(int p) { return p == g_sck || p == g_mosi || p == g_miso || p == g_cs; }

bool rawRead(int sck, int mosi, int miso, int cs, uint8_t *b) {
    SPI.end();
    SPI.begin(sck, miso, mosi, cs);
    pinMode(cs, OUTPUT);
    digitalWrite(cs, HIGH);
    delayMicroseconds(20);
    SPI.beginTransaction(SPISettings(500000, MSBFIRST, SPI_MODE0));
    digitalWrite(cs, LOW);
    delayMicroseconds(20);
    SPI.transfer(CMD_READ_REGISTER);
    SPI.transfer((REG_VERSION_STRING >> 8) & 0xFF);
    SPI.transfer(REG_VERSION_STRING & 0xFF);
    for (int i = 0; i < 16; i++) b[i] = SPI.transfer(0x00);
    digitalWrite(cs, HIGH);
    SPI.endTransaction();
    return hasPat(b, "SX12", 4) || hasPat(b, "LLCC", 4);
}

void setup() {
    Serial.begin(115200);
    delay(1500);
    Serial.println();
    Serial.println("=== LoRa pin scanner v4 ===");

    // ---- Fase 1: SPI ----
    Serial.println("-- Fase 1: SCK/MOSI/MISO/CS --");
    for (int a = 0; a < NC; a++)
    for (int b = 0; b < NC; b++) {
        if (b == a) continue;
        for (int c = 0; c < NC; c++) {
            if (c == a || c == b) continue;
            for (int d = 0; d < NC; d++) {
                if (d == a || d == b || d == c) continue;
                uint8_t buf[16];
                if (rawRead(CAND[a], CAND[b], CAND[c], CAND[d], buf)) {
                    g_sck = CAND[a]; g_mosi = CAND[b]; g_miso = CAND[c]; g_cs = CAND[d];
                    Serial.printf("MATCH CS=%d SCK=%d MOSI=%d MISO=%d :", g_cs, g_sck, g_mosi, g_miso);
                    for (int i = 0; i < 16; i++) Serial.printf(" %02X", buf[i]);
                    Serial.print("  \"");
                    for (int i = 0; i < 16; i++) { char ch = (char)buf[i]; Serial.print((ch >= 32 && ch < 127) ? ch : '.'); }
                    Serial.println("\"");
                } else {
                    bool all00 = true, allFF = true;
                    for (int i = 0; i < 16; i++) { if (buf[i] != 0x00) all00 = false; if (buf[i] != 0xFF) allFF = false; }
                    if (!all00 && !allFF) {
                        Serial.printf("RAW CS=%d SCK=%d MOSI=%d MISO=%d :", CAND[d], CAND[a], CAND[b], CAND[c]);
                        for (int i = 0; i < 16; i++) Serial.printf(" %02X", buf[i]);
                        Serial.println();
                    }
                }
            }
        }
    }
    if (g_cs < 0) { Serial.println("Sin match SPI."); return; }

    // ---- Fase 2: RST ----
    Serial.println("-- Fase 2: RST --");
    for (int i = 0; i < NC; i++) {
        int c = CAND[i];
        if (isSPI(c)) continue;
        pinMode(c, OUTPUT_OPEN_DRAIN);   // solo tira a bajo, no pelea con salidas
        digitalWrite(c, LOW);
        delay(5);
        uint8_t buf[16];
        bool ok = rawRead(g_sck, g_mosi, g_miso, g_cs, buf);
        digitalWrite(c, HIGH);
        pinMode(c, INPUT);
        delay(5);
        if (!ok) { g_rst = c; Serial.printf("RST = %d (lectura fallo con reset)\n", c); break; }
    }
    if (g_rst < 0) { Serial.println("No encontre RST."); return; }

    // ---- Fase 3: BUSY vs DIO1 ----
    Serial.println("-- Fase 3: BUSY vs DIO1 --");
    int rem[16], n = 0;
    for (int i = 0; i < NC; i++) {
        int p = CAND[i];
        if (p == g_sck || p == g_mosi || p == g_miso || p == g_cs || p == g_rst) continue;
        rem[n++] = p;
    }
    for (int i = 0; i < n; i++) pinMode(rem[i], INPUT_PULLDOWN);
    bool hi[16];
    for (int i = 0; i < n; i++) hi[i] = false;
    pinMode(g_rst, OUTPUT);
    digitalWrite(g_rst, LOW);
    uint32_t t0 = millis();
    while (millis() - t0 < 30) { for (int i = 0; i < n; i++) if (digitalRead(rem[i])) hi[i] = true; }
    digitalWrite(g_rst, HIGH);
    t0 = millis();
    while (millis() - t0 < 500) { for (int i = 0; i < n; i++) if (digitalRead(rem[i])) hi[i] = true; }
    Serial.print("Niveles altos (BUSY) en: ");
    int busy = -1;
    for (int i = 0; i < n; i++) if (hi[i]) { Serial.printf("%d ", rem[i]); if (busy < 0) busy = rem[i]; }
    Serial.println();
    Serial.print("Detalle: ");
    for (int i = 0; i < n; i++) Serial.printf("GPIO%d=%d ", rem[i], hi[i] ? 1 : 0);
    Serial.println();
    Serial.printf(">>> CS=%d SCK=%d MOSI=%d MISO=%d RST=%d BUSY=%d\n",
                  g_cs, g_sck, g_mosi, g_miso, g_rst, busy);
    Serial.println("=== Fin ===");

    // ---- Fase 4: distinguir BUSY/DIO1 (sin RadioLib) ----
    Serial.println("-- Fase 4: BUSY vs DIO1 --");
    // 4a) verificar que RST resetea
    pinMode(g_rst, OUTPUT);
    digitalWrite(g_rst, LOW);
    delay(5);
    uint8_t bb[16];
    bool inReset = rawRead(g_sck, g_mosi, g_miso, g_cs, bb);
    digitalWrite(g_rst, HIGH);
    delay(5);
    bool outReset = rawRead(g_sck, g_mosi, g_miso, g_cs, bb);
    Serial.printf("reset test: en_reset_ok=%d fuera_ok=%d\n", inReset, outReset);
    // 4b) comando Calibrate (0x89 0x7F) -> BUSY alto unos ms
    SPI.end();
    SPI.begin(g_sck, g_miso, g_mosi, g_cs);
    pinMode(g_cs, OUTPUT);
    digitalWrite(g_cs, HIGH);
    pinMode(6, INPUT);
    pinMode(7, INPUT);
    SPI.beginTransaction(SPISettings(500000, MSBFIRST, SPI_MODE0));
    digitalWrite(g_cs, LOW);
    SPI.transfer(0x89);
    SPI.transfer(0x7F);
    digitalWrite(g_cs, HIGH);
    SPI.endTransaction();
    bool b6 = false, b7 = false;
    uint32_t t1 = millis();
    while (millis() - t1 < 50) { if (digitalRead(6)) b6 = true; if (digitalRead(7)) b7 = true; }
    Serial.printf("calib: GPIO6=%d GPIO7=%d  (el que sube = BUSY)\n", b6, b7);
    Serial.println("=== Fin ===");
}

void loop() {}
