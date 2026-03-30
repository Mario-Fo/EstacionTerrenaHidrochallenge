#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "IZZI-49DC";
const char* password = "F82DC03649DC";
const char* serverUrl = "http://192.168.0.29:8000/api/lecturas-multi";

unsigned long ultimoIntentoWiFi = 0;
unsigned long ultimoEnvio = 0;
const unsigned long intervaloEnvioMs = 300;

float randomFloat(float minValue, float maxValue) {
  long r = random(0, 1000000);
  return minValue + ((float)r / 1000000.0f) * (maxValue - minValue);
}

void asegurarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  if (millis() - ultimoIntentoWiFi < 5000) return;

  ultimoIntentoWiFi = millis();
  Serial.println("Intentando reconectar WiFi...");

  WiFi.disconnect(true);
  delay(200);
  WiFi.begin(ssid, password);
}

void enviarPOST(const String& jsonPayload) {
  // Verificar conexión WiFi
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("No hay WiFi. No se envia POST.");
    return;
  }

  HTTPClient http;

  // Iniciar conexión
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");

  Serial.println("\n===== JSON ENVIADO =====");
  Serial.println(jsonPayload);

  // Enviar POST
  int code = http.POST(jsonPayload);

  // Mostrar código HTTP
  Serial.print("Código HTTP: ");
  Serial.println(code);

  // Interpretar código HTTP
  if (code > 0) {

    if (code >= 200 && code < 300) {
      Serial.println("EXITO (2xx)");
    } 
    else if (code >= 300 && code < 400) {
      Serial.println("REDIRECCION (3xx)");
    } 
    else if (code >= 400 && code < 500) {
      Serial.println("ERROR DEL CLIENTE (4xx)");
    } 
    else if (code >= 500) {
      Serial.println("ERROR DEL SERVIDOR (5xx)");
    }

    // Mostrar respuesta del servidor
    String respuesta = http.getString();
    Serial.println("📥 Respuesta servidor:");
    Serial.println(respuesta);

  } else {
    Serial.println("Error en la solicitud HTTP");
  }

  // Cerrar conexión
  http.end();
}

void setup() {
  Serial.begin(115200);
  delay(500);

  randomSeed(micros());

  Serial.println("=== ESTACION TERRENA ESP32 ===");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  Serial.print("Conectando a WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(400);
    Serial.print(".");
  }

  Serial.println();
  Serial.print("WiFi conectado. IP ESP32: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  asegurarWiFi();

  if (WiFi.status() == WL_CONNECTED && millis() - ultimoEnvio >= intervaloEnvioMs) {
    ultimoEnvio = millis();

    // Rangos cercanos a: 25.8400365, -97.4544468
    float lat = randomFloat(25.8350f, 25.8450f);
    float lon = randomFloat(-97.4600f, -97.4480f);
    float alt = randomFloat(1000.0f, 1030.0f);

    String payload = "[{\"id\":\"PRUEBA\"," "\"pres\":"+String(randomFloat(1000.0f, 1050.0f), 2)+
    ",\"temp\":"+String(randomFloat(20.0f, 30.0f), 2)+",\"hum\":"+String(randomFloat(40.0f, 60.0f), 2)+","
    "\"lat\":"+String(lat, 6)+",\"long\":"+String(lon, 6)+",\"alt\":"+String(alt, 2)+"," "\"accX\":"+
    String(randomFloat(-1.0f, 1.0f), 2)+",\"accY\":"+String(randomFloat(-1.0f, 1.0f), 2)+",\"accZ\":"
    +String(randomFloat(-1.0f, 1.0f), 2)+"," "\"RPM\":"+String(random(0, 2))+"}]";

    enviarPOST(payload);
  }

  delay(20);
}



