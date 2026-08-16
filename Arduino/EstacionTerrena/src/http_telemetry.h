#pragma once

#include <Arduino.h>

void connectWiFi();
bool httpPost(const String &payload);
