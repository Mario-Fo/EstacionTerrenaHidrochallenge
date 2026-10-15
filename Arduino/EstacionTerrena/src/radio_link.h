#pragma once

#include <Arduino.h>

void radioInit();
void radioStartListen();
bool radioCheckRx(String &out);
<<<<<<< HEAD
bool radioSendCommand(char command);
=======
void radioSendCommand(const char *cmd);
>>>>>>> cb0335b8459810b43a28a7ece3191b64527a81b7
