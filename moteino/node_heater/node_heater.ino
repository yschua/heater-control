#include <RFM69.h>
#include <RFM69_ATC.h>
#include "LowPower.h"

#define DEBUG
#include "DebugUtils.h"

// Device
//#define FREQUENCY   RF69_868MHZ
#define FREQUENCY     RF69_915MHZ
#define ENCRYPT_KEY    "0123456789abcdef" // 16 bytes
#define IS_RFM69HW
#define ENABLE_ATC    // Auto Transmission Control

// Pins
#define LED           9
#define BTN_UP        16
#define BTN_DOWN      14
#define BTN_POW       15
#define INT_RADIO     2

// Network
#define NODE_ID        2    // Unique
#define NETWORK_ID     100  // Same
#define GATEWAY_ID     1
#define SERIAL_BAUD   115200

// Communications Protocol
// Header (1,2 LSB)
#define REQ           0x1
#define DONE           0x2
#define MSG           0x3
// Payload (3,4 LSB)
#define TEMP_UP       0x1
#define TEMP_DOWN     0x2
#define POWER         0x3

// Global
#ifdef ENABLE_ATC
  RFM69_ATC radio;
#else
  RFM69 radio;
#endif
char buff[1];

void setup()
{
  initPins();

  Serial.begin(SERIAL_BAUD);
  radio.initialize(FREQUENCY, NODE_ID, NETWORK_ID);
#ifdef IS_RFM69HW
  radio.setHighPower();
#endif
  radio.encrypt(ENCRYPT_KEY);
#ifdef ENABLE_ATC
  radio.enableAutoPower(-70); // fiddle with this
#endif

  blink(LED, 3);

  delay(1000);
}

void loop()
{
  buff[0] = REQ;
  radio.sendWithRetry(GATEWAY_ID, buff, 1);
  radio.receiveDone();

  DEBUG_PRINT("send REQ\n")

  delay(40); // round trip delay

  while (radio.receiveDone()) {
    if (radio.ACKRequested())
      radio.sendACK();
    radio.sleep();

    DEBUG_PRINT("receive: %x\n", radio.DATA[0]);

    char recv = radio.DATA[0];
    char action = recv & 0x3;
    char count = recv >> 2;

    switch (action) {
      case TEMP_UP:
        delay(1000);
        volumeUp(count);
        break;
      case TEMP_DOWN:
        delay(1000);
        volumeDown(count);
        break;
      case POWER:
        power();
        break;
      default:
        break;
    }

    DEBUG_PRINT("send DONE\n")
    buff[0] = DONE;
    radio.sendWithRetry(GATEWAY_ID, buff, 1);

    radio.receiveDone();
    delay(100);

    DEBUG_PRINT("listening\n")
  }

  DEBUG("no response, going back to sleep\n");

  radio.sleep();
  delay(40);
  for (int i = 0; i < 4; i++)
    LowPower.powerDown(SLEEP_8S, ADC_OFF, BOD_OFF);

  // Sleep 32 sec
  // radio.sleep();
  // for (int i = 0; i < 4; i++)
  //   LowPower.powerDown(SLEEP_8S, ADC_OFF, BOD_OFF);

  // if (radio.receiveDone()) {
  //   blink(LED, 3);

  //   if (radio.ACKRequested())
  //     radio.sendACK();

    // char recv = radio.DATA[0];
    // char action = recv & 0x3;
    // char count = recv >> 2;

    // switch (action) {
    //   case TEMP_UP:
    //     volumeUp(count);
    //     break;
    //   case TEMP_DOWN:
    //     volumeDown(count);
    //     break;
    //   case POWER:
    //     power();
    //     break;
    //   default:
    //     break;
    // }

  //   buff[0] = DONE;
  //   radio.sendWithRetry(GATEWAY_ID, buff, 1);
  //   blink(LED, 3);
  // }
  // Serial.flush();
  // //radio.sleep();
  // LowPower.powerDown(SLEEP_FOREVER, ADC_OFF, BOD_OFF);
}

void initPins()
{
  pinMode(BTN_UP, OUTPUT);
  digitalWrite(BTN_UP, LOW);
  pinMode(BTN_DOWN, OUTPUT);
  digitalWrite(BTN_DOWN, LOW);
  pinMode(BTN_POW, OUTPUT);
  digitalWrite(BTN_POW, LOW);
}

void blink(byte pin, int duration)
{
  pinMode(pin, OUTPUT);
  digitalWrite(pin, HIGH);
  delay(duration);
  digitalWrite(pin, LOW);
}

void press(byte button, int duration)
{
  pinMode(button, OUTPUT);
  pinMode(LED, OUTPUT);
  digitalWrite(button, HIGH);
  digitalWrite(LED, HIGH);
  delay(duration);
  digitalWrite(button, LOW);
  digitalWrite(LED, LOW);
  delay(200);
}

void power()
{
  press(BTN_POW, 1500);
}

void volume(byte button, int count)
{
  press(button, 400);

  for (int i = 0; i < count; i++)
    press(button, 400);
}

void volumeUp(int count)
{
  volume(BTN_UP, count);
}

void volumeDown(int count)
{
  volume(BTN_DOWN, count);
}
