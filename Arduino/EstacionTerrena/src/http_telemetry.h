#pragma once

#include <Arduino.h>

void connectWiFi();
bool httpPost(const String &payload);
<<<<<<< HEAD
String httpFetchCommand();
=======
String httpGetCommand();
>>>>>>> cb0335b8459810b43a28a7ece3191b64527a81b7
