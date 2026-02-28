#include <WiFi.h>
#include <HTTPClient.h>

// ===================== CONFIG WIFI =====================
const char* ssid     = "WIFI";
const char* password = "CONTRASEÑA DEL WIFI";

const char* serverUrl = "http://IP DEL SERVER:3000/api/lecturas-multi";

// ===================== XBEE UART2 =====================
HardwareSerial xbee(2);   // UART2
const int XBEE_RX = 16;   // GPIO16
const int XBEE_TX = 17;   // GPIO17

// ===================== BUFFER =====================
String trama = "";
unsigned long ultimoIntentoWiFi = 0;

// ===================================================
void asegurarWiFi() {

  if (WiFi.status() == WL_CONNECTED) return;

  if (millis() - ultimoIntentoWiFi < 5000) return;

  ultimoIntentoWiFi = millis();

  Serial.println("Intentando reconectar WiFi...");
  WiFi.disconnect(true);
  WiFi.begin(ssid, password);
}

// ===================================================
void enviarPOST(const String& jsonPayload) {

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("No hay WiFi. No se envia POST.");
    return;
  }

  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");

  Serial.println("===== JSON ENVIADO =====");
  Serial.println(jsonPayload);

  int code = http.POST(jsonPayload);

  Serial.print("POST -> ");
  Serial.println(code);

  if (code > 0) {
    String respuesta = http.getString();
    Serial.println("Respuesta servidor:");
    Serial.println(respuesta);
  }

  http.end();
}

// ===================================================
void setup() {

  Serial.begin(115200);

  // Iniciar XBee UART2
  xbee.begin(9600, SERIAL_8N1, XBEE_RX, XBEE_TX);

  Serial.println("=== ESTACION TERRENA BRAVO-II ===");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  Serial.print("Conectando a WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(400);
    Serial.print(".");
  }

  Serial.println();
  Serial.print("WiFi conectado. IP: ");
  Serial.println(WiFi.localIP());
}

// ===================================================
void loop() {

  asegurarWiFi();

  while (xbee.available()) {

    char c = (char)xbee.read();

    if (c == '\n') {

      trama.trim();

      if (trama.length() > 10) {  // evita basura vacía

        Serial.println("Trama recibida:");
        Serial.println(trama);

        enviarPOST(trama);
      }

      trama = "";
    }
    else {
      trama += c;

      // Protección por desbordamiento
      if (trama.length() > 1500) {
        trama = "";
      }
    }
  }
}