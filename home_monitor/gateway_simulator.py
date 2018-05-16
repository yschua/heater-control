import serial
import time

# set up virtual COM port pair with home_monitor.py
ser = serial.Serial('COM4', 115200, timeout=1)

while True:
  ser.write(bytes([REQUEST]))
  print(ser.read())
  ser.write(bytes([SUCCESS]))
  time.sleep(5)