#pragma once

#include <Arduino.h>

extern bool radioOK;
bool radioInit();
void radioTransmit();
void radioPollCommand();
String telemetryJson();
void radioSendAck(const char *msg);
