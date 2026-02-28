#include <WiFi.h>
#include <HTTPClient.h>

const char* WIFI_SSID = "TU_WIFI";
const char* WIFI_PASS = "TU_PASS";
const char* API_URL   = "http://192.168.1.100:3000/telemetry"; // IP de tu PC

HardwareSerial XBee(2);
const int XBEE_RX = 21; // ESP32 RX2 <- XBee TX
const int XBEE_TX = 22; // ESP32 TX2 -> XBee RX

String lineBuffer;

static bool parsePipe(const String& line,
                      String& nombre,
                      double& presion,
                      double& temp,
                      double& hum,
                      double& lat,
                      double& lon,
                      double& alt,
                      double& ax,
                      double& ay,
                      double& az) {
  // Espera 10 campos => 9 separadores '|'
  int cuts[9];
  int c = 0;
  for (int i = 0; i < (int)line.length() && c < 9; i++) {
    if (line[i] == '|') cuts[c++] = i;
  }
  if (c < 9) return false;

  String s0 = line.substring(0, cuts[0]);
  String s1 = line.substring(cuts[0] + 1, cuts[1]);
  String s2 = line.substring(cuts[1] + 1, cuts[2]);
  String s3 = line.substring(cuts[2] + 1, cuts[3]);
  String s4 = line.substring(cuts[3] + 1, cuts[4]);
  String s5 = line.substring(cuts[4] + 1, cuts[5]);
  String s6 = line.substring(cuts[5] + 1, cuts[6]);
  String s7 = line.substring(cuts[6] + 1, cuts[7]);
  String s8 = line.substring(cuts[7] + 1, cuts[8]);
  String s9 = line.substring(cuts[8] + 1);

  s0.trim(); s1.trim(); s2.trim(); s3.trim(); s4.trim();
  s5.trim(); s6.trim(); s7.trim(); s8.trim(); s9.trim();

  if (s0.length() == 0) return false;

  nombre  = s0;
  presion = s1.toDouble();
  temp    = s2.toDouble();
  hum     = s3.toDouble();
  lat     = s4.toDouble();
  lon     = s5.toDouble();
  alt     = s6.toDouble();
  ax      = s7.toDouble();
  ay      = s8.toDouble();
  az      = s9.toDouble();

  return true;
}

static bool postToAPI(const String& nombre,
                      double presion,
                      double temp,
                      double hum,
                      double lat,
                      double lon,
                      double alt,
                      double ax,
                      double ay,
                      double az) {
  if (WiFi.status() != WL_CONNECTED) return false;

  HTTPClient http;
  http.begin(API_URL);
  http.addHeader("Content-Type", "application/json");

  String body = "{";
  body += "\"nombre\":\"" + nombre + "\",";
  body += "\"presion_hpa\":" + String(presion, 2) + ",";
  body += "\"temp_c\":" + String(temp, 2) + ",";
  body += "\"humedad_pct\":" + String(hum, 2) + ",";
  body += "\"latitud\":" + String(lat, 6) + ",";
  body += "\"longitud\":" + String(lon, 6) + ",";
  body += "\"altitud_m\":" + String(alt, 2) + ",";
  body += "\"ax_g\":" + String(ax, 3) + ",";
  body += "\"ay_g\":" + String(ay, 3) + ",";
  body += "\"az_g\":" + String(az, 3);
  body += "}";

  int code = http.POST(body);
  String resp = http.getString();
  http.end();

  Serial.printf("POST %d | %s\n", code, resp.c_str());
  return (code >= 200 && code < 300);
}

void setup() {
  Serial.begin(115200);

  XBee.begin(9600, SERIAL_8N1, XBEE_RX, XBEE_TX);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Conectando WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi OK: " + WiFi.localIP().toString());
}

void loop() {
  while (XBee.available()) {
    char ch = (char)XBee.read();

    if (ch == '\n') {
      String line = lineBuffer;
      lineBuffer = "";
      line.trim();
      if (line.length() == 0) continue;

      // Si mandas encabezado, ignóralo:
      if (line.startsWith("NOMBRE|PRESION|")) {
        Serial.println("Header ignorado");
        continue;
      }

      String nombre;
      double p,t,h,la,lo,al,ax,ay,az;

      if (parsePipe(line, nombre, p, t, h, la, lo, al, ax, ay, az)) {
        Serial.println("RX: " + line);
        postToAPI(nombre, p, t, h, la, lo, al, ax, ay, az);
      } else {
        Serial.println("Trama inválida: " + line);
      }
    } else if (ch != '\r') {
      if (lineBuffer.length() < 260) lineBuffer += ch;
      else lineBuffer = ""; // evita overflow
    }
  }
}
