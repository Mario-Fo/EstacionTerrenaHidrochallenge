#pragma once

#include <Arduino.h>

void radioInit();
bool radioReceive(String &out, uint32_t timeout);
void radioTransmitCommand(const String &cmd);
