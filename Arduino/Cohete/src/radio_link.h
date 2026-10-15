#pragma once

extern bool radioOK;
bool radioInit();
void radioTransmit();
String telemetryJson();
bool radioCheckCommand(String &cmd, uint32_t timeout);
void radioSendAck(const char *msg);
