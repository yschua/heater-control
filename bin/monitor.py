import serial
import time
import sqlite3
import logging
import struct

OS = 'Windows'

if OS == 'Unix':
  PORT = '/dev/ttyUSB0'
  DB_PATH = '/var/db/home.db'
  LOG_PATH = '/var/scripts/monitor.log'
elif OS == 'Windows':
  PORT = 'COM3'
  DB_PATH = '../db/home.db'
  LOG_PATH = 'monitor.log'

# Communications
REQ = 0x1
DONE = 0x2
MSG = 0x3
TEMP_UP = 0x1
TEMP_DOWN = 0x2
POWER = 0x3

READ_TIMEOUT = 15 # depends on time from MIN_TEMP to MAX_TEMP and vice-versa
POLL_PERIOD = 6 # depends on time it takes for temperature control to "settle"
SERIAL_BAUD = 115200

def init():
  logging.basicConfig(
    filename=LOG_PATH,
    level=logging.DEBUG,
    format='[%(asctime)s] %(message)s')

  # sqlite
  global conn, c
  conn = sqlite3.connect(DB_PATH)
  conn.row_factory = sqlite3.Row
  c = conn.cursor()

  # serial comm with moteino
  global ser
  ser = serial.Serial(PORT, SERIAL_BAUD, timeout=READ_TIMEOUT)

  logging.info('monitor.py started')

def main():
  init()

  # implement safe exit
  while True:
    print('listening')
    recv = to_uchar(ser.read())
    if (recv == REQ):
      print('receive REQ')
      c.execute('SELECT * FROM heater WHERE heater_id = 1')
      row = c.fetchone()

      if row['selected_power'] == 1 and row['current_power'] == 0: # switched on
        update_power(row)
        update_temperature(row)
      else:
        update_temperature(row)
        update_power(row)
    else:
      print('nothing received')

  ser.close()

def update_power(row):
  selected_power, current_power = row['selected_power'], row['current_power']
  if (selected_power != current_power):
    send_success = send(POWER)

    if send_success:
      c.execute('UPDATE heater SET current_power = ?', (selected_power, ))
    else:
      c.execute('UPDATE heater SET selected_power = ?', (current_power, ))
    conn.commit()

    logging.info(
      'set current_power {} -> {}: {}'.
      format(current_power, selected_power, 'success' if send_success else 'failed'))
    if not send_success:
      logging.info('set selected_power back to {}'.format(current_power))

def update_temperature(row):
  selected_temperature, current_temperature = row['selected_temperature'], row['current_temperature']
  if selected_temperature != current_temperature:
    delta = selected_temperature - current_temperature
    message = TEMP_UP if delta > 0 else TEMP_DOWN
    count = abs(int(delta * 2))
    message = message | count << 2

    send_success = send(message)

    if send_success:
      c.execute('UPDATE heater SET current_temperature = ?', (selected_temperature, ))
    else:
      c.execute('UPDATE heater SET selected_temperature = ?', (current_temperature, ))
    conn.commit()

    logging.info(
      'set current_temperature {} -> {}: {}'.
      format(current_temperature, selected_temperature, 'success' if send_success else 'failed'))
    if not send_success:
      logging.info('set selected_temperature back to {}'.format(current_temperature))

def send(message):
  if not isinstance(message, int):
    raise ValueError()
  print('send MSG: {:02x}'.format(message))
  ser.write(bytes([message]))
  recv = to_uchar(ser.read())
  if recv == DONE:
    print('receive DONE')
  else:
    print('no reply, rollback')
  return recv == DONE
  # return to_uchar(ser.read()) == DONE # True if ack received

def to_uchar(byte_data):
  if not byte_data or not isinstance(byte_data, bytes):
    return 0
  return struct.unpack('B', byte_data)[0]

def dict_from_row(row):
  return dict(zip(row.keys(), row))

if __name__ == '__main__':
  main()
