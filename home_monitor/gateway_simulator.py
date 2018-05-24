import serial
import time
import struct

SUCCESS = 0x0
FAILED = 0x1
REQUEST = 0x2

# set up virtual COM port pair with home_monitor.py using com0com
ser = serial.Serial('COM4', 115200, timeout=1)

while True:
  try:
    ser.write(bytes([REQUEST]))
    recv = ser.read()
    if recv:
      msg = struct.unpack('B', recv)[0]
      toggle = (msg & 0x1) == 0x1
      delta = (msg >> 1) & 0x3f
      if (msg & 0x80) != 0x0:
        delta *= -1
      print('toggle: {}, delta: {}'.format(toggle, delta))
      ser.write(bytes([SUCCESS]))
    time.sleep(5)
  except KeyboardInterrupt:
    break