#include <RFM69.h>
#include <RFM69_ATC.h>

// Device
//#define FREQUENCY     RF69_868MHZ
#define FREQUENCY     RF69_915MHZ
#define ENCRYPT_KEY    "0123456789abcdef" // 16 bytes
#define IS_RFM69HW    
#define ENABLE_ATC    // Auto Transmission Control

// Pins
#define LED           9

// Network
#define NODE_ID        1    // Unique
#define NETWORK_ID     100  // Same
#define HEATER_ID  2
#define SERIAL_BAUD   115200

// Global
#ifdef ENABLE_ATC
  RFM69_ATC radio;
#else
  RFM69 radio;
#endif
char buff[1];

void setup() 
{
  Serial.begin(SERIAL_BAUD);
  delay(10); // need?
  radio.initialize(FREQUENCY, NODE_ID, NETWORK_ID);
#ifdef IS_RFM69HW
  radio.setHighPower();
#endif
  radio.encrypt(ENCRYPT_KEY);

  blink(LED, 3);
}

void loop() 
{
  if (Serial.available() > 0) {
    buff[0] = Serial.read();
    radio.sendWithRetry(HEATER_ID, buff, 1);
  }

  if (radio.receiveDone()) {
    if (radio.ACKRequested())
      radio.sendACK();
    Serial.write(radio.DATA[0]);
  }
}

void blink(byte pin, int duration)
{
  pinMode(pin, OUTPUT);
  digitalWrite(pin, HIGH);
  delay(duration);
  digitalWrite(pin, LOW);
}
