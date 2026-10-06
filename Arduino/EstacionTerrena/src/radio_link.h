#pragma once

#include <Arduino.h>

void radioInit();
void radioStartListen();
bool radioCheckRx(String &out);
