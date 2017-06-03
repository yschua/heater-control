import serial
import time
import logging
import struct
import homedb

# TODO auto check OS?
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
    level=logging.DEBUG, # TODO set this with command line argument
    format='[%(asctime)s] %(message)s')

  # database
  # TODO add logging
  global db
  db = homedb.HomeDb(DB_PATH, logging)

  # serial comm with moteino
  global ser
  ser = serial.Serial(PORT, SERIAL_BAUD, timeout=READ_TIMEOUT)

  logging.info('monitor.py started')

def main():
  init()

  # TODO implement safe exit
  while True:
    logging.debug('listening')
    recv = to_uchar(ser.read())
    if (recv == REQ):
      logging.debug('receive REQ')

      if db.get_selected_power() == 1 and db.get_current_power() == 0: # switched on
        update_power()
        update_temperature()
      else:
        update_temperature()
        update_power()
    else:
      logging.debug('nothing received')

  ser.close()

def update_power():
  selected_power, current_power = db.get_selected_power(), db.get_current_power()
  if (selected_power != current_power):
    send_success = send(POWER)

    if send_success:
      db.set_current_power(selected_power)
    else:
      db.set_selected_power(current_power)

    logging.info(
      'set current_power {} -> {}: {}'.
      format(current_power, selected_power, 'success' if send_success else 'failed'))
    if not send_success:
      logging.info('set selected_power back to {}'.format(current_power))

def update_temperature():
  selected_temp, current_temp = db.get_selected_temp(), db.get_current_temp()
  if selected_temp != current_temp:
    delta = selected_temp - current_temp
    message = TEMP_UP if delta > 0 else TEMP_DOWN
    count = abs(int(delta * 2))
    message = message | count << 2

    send_success = send(message)

    if send_success:
      db.set_current_temp(selected_temp)
    else:
      db.set_selected_temp(current_temp)

    logging.info(
      'set current_temperature {} -> {}: {}'.
      format(current_temp, selected_temp, 'success' if send_success else 'failed'))
    if not send_success:
      logging.info('set selected_temp back to {}'.format(current_temp))

def send(message):
  if not isinstance(message, int):
    raise ValueError()
  logging.debug('send MSG: {:02x}'.format(message))
  ser.write(bytes([message]))
  recv = to_uchar(ser.read())
  if recv == DONE:
    logging.debug('receive DONE')
  else:
    logging.debug('no reply, rollback')
  return recv == DONE
  # return to_uchar(ser.read()) == DONE # True if ack received

def to_uchar(byte_data):
  if not byte_data or not isinstance(byte_data, bytes):
    return 0
  return struct.unpack('B', byte_data)[0]

if __name__ == '__main__':
  main()
