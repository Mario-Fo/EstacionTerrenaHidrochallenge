#pragma once

extern bool radioOK;
bool radioInit();
void radioTransmit();
void radioPollCommand();
String telemetryJson();
