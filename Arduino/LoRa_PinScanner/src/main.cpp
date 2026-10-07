// Escaner de pines del modulo LoRa (SX1262 / E22-900M22S / LLCC68)
//  Fase 1: detecta SCK/MOSI/MISO/CS leyendo el registro de version (0x0320)
//          buscando "SX12" o "LLCC". No necesita RST ni BUSY.
//  Fase 2: con los SPI hallados, prueba RST/BUSY/DIO1 entre los pines restantes
//          usando RadioLib begin().

#include <Arduino.h>
#include <SPI.h>
#include <RadioLib.h>

const int CAND[] = {1, 2, 3, 4, 5, 6, 7};
const int NC = sizeof(CAND) / sizeof(CAND[0]);

#define CMD_READ_REGISTER   0x1D
#define REG_VERSION_STRING  0x0320

int g_sck = -1, g_mosi = -1, g_miso = -1, g_cs = -1;
int g_rst = -1;

bool hasPat(const uint8_t *b, const char *pat, int plen) {
    for (int i = 0; i + plen <= 16; i++) {
        bool ok = true;
        for (int j = 0; j < plen; j++) if ((char)b[i + j] != pat[j]) { ok = false; break; }
        if (ok) return true;
    }
    return false;
}

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

void phase1() {
    Serial.println("--- Fase 1: SCK/MOSI/MISO/CS ---");
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
                    Serial.printf("MATCH SCK=%d MOSI=%d MISO=%d CS=%d :", g_sck, g_mosi, g_miso, g_cs);
                    for (int i = 0; i < 16; i++) Serial.printf(" %02X", buf[i]);
                    Serial.print("  \"");
                    for (int i = 0; i < 16; i++) { char ch = (char)buf[i]; Serial.print((ch >= 32 && ch < 127) ? ch : '.'); }
                    Serial.println("\"");
                }
            }
        }
    }
    if (g_cs < 0) Serial.println("Fase 1: sin match.");
}

void phase2() {
    if (g_cs < 0) { Serial.println("Fase 2: omitida."); return; }
    Serial.println("--- Fase 2: RST/BUSY/DIO1 ---");
    int rem[8], n = 0;
    for (int i = 0; i < NC; i++) {
        int p = CAND[i];
        if (p != g_sck && p != g_mosi && p != g_miso && p != g_cs) rem[n++] = p;
    }
    for (int ri = 0; ri < n; ri++)
    for (int bi = 0; bi < n; bi++) {
        if (bi == ri) continue;
        for (int di = 0; di < n; di++) {
            if (di == ri || di == bi) continue;
            int rst = rem[ri], busy = rem[bi], dio1 = rem[di];
            SPI.end();
            SPI.begin(g_sck, g_miso, g_mosi, g_cs);
            Module mod(g_cs, dio1, rst, busy);
            SX1262 r(&mod);
            int st = r.begin(915.0, 125.0, 9, 7, 0x12, 22, 8, 2.2);
            Serial.printf("  RST=%d BUSY=%d DIO1=%d -> %d\n", rst, busy, dio1, st);
            if (st == RADIOLIB_ERR_NONE) {
                g_rst = rst;
                Serial.printf(">>> OK: CS=%d SCK=%d MOSI=%d MISO=%d RST=%d BUSY=%d DIO1=%d\n",
                              g_cs, g_sck, g_mosi, g_miso, rst, busy, dio1);
                return;  // seguimos con la Fase 3
            }
        }
    }
}

void phase3() {
    if (g_rst < 0) { Serial.println("Fase 3: omitida."); return; }
    Serial.println("--- Fase 3: BUSY vs DIO1 ---");
    int rem[8], n = 0;
    for (int i = 0; i < NC; i++) {
        int p = CAND[i];
        if (p != g_sck && p != g_mosi && p != g_miso && p != g_cs && p != g_rst) rem[n++] = p;
    }
    if (n != 2) { Serial.printf("Fase 3: quedan %d pines (se esperaban 2).\n", n); return; }
    int p1 = rem[0], p2 = rem[1];
    pinMode(g_rst, OUTPUT);
    pinMode(p1, INPUT_PULLDOWN);
    pinMode(p2, INPUT_PULLDOWN);
    digitalWrite(g_rst, LOW);
    delay(10);
    digitalWrite(g_rst, HIGH);
    bool s1 = false, s2 = false;
    uint32_t t0 = millis();
    while (millis() - t0 < 200) {
        if (digitalRead(p1)) s1 = true;
        if (digitalRead(p2)) s2 = true;
    }
    Serial.printf("Tras reset (RST=%d): GPIO%d pulso=%d, GPIO%d pulso=%d\n", g_rst, p1, s1, p2, s2);
    Serial.printf(">>> BUSY = %d, DIO1 = %d (el que pulso alto es BUSY)\n",
                  s1 ? p1 : (s2 ? p2 : -1), s1 ? p2 : (s2 ? p1 : -1));
}

void setup() {
    Serial.begin(115200);
    delay(1500);
    Serial.println();
    Serial.println("=== LoRa pin scanner v3 ===");
    phase1();
    phase2();
    phase3();
    Serial.println("=== Fin ===");
}

void loop() {}
