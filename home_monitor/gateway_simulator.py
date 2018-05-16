import serial
import time

SUCCESS = 0x0
FAILED = 0x1
REQUEST = 0x2

# set up virtual COM port pair with home_monitor.py
ser = serial.Serial('COM4', 115200, timeout=1)

while True:
  ser.write(bytes([REQUEST]))
  recv = ser.read()
  if recv:
    print(recv)
    ser.write(bytes([SUCCESS]))
  time.sleep(5)