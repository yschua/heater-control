import serial
import time
import logging
import homedb
from message import Message

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

# receive message control values
SUCCESS = 0x0
FAILED = 0x1
REQUEST = 0x2

# parameters
SERIAL_BAUD = 115200

def main():
  logging.basicConfig(
    filename=LOG_PATH,
    level=logging.DEBUG, # TODO set this with command line argument
    format='[%(asctime)s] %(message)s')

  # sqlite3 database
  db = homedb.HomeDb(DB_PATH, logging)

  # serial communications with gateway
  ser = serial.Serial(PORT, SERIAL_BAUD)

  logging.info('home_monitor.py started')

  # TODO implement safe exit
  while True:
    msg_recv = receive(ser) # blocking until received

    if not msg_recv or msg_recv.get() != REQUEST:
      continue

    # timing is very critical between request receive and request reply
    # this means no expensive queries like sql updates until message is sent

    logging.info('receive update request')

    msg_send = get_message(db)

    # nothing to update
    if msg_send.get() == 0x0:
      continue

    send(ser, msg_send)
    msg_recv = receive(ser, 1) # will have to play around with this timeout

    if msg_recv and msg_recv.get() == SUCCESS:
      update_current(db)
    else:
      logging.error('update failed')

  ser.close()

def get_message(db):
  msg = Message()

  # turn off on timeout
  if db.check_timeout():
    msg.power_toggle()
    return msg

  selected_power = db.get_selected_power()
  current_power = db.get_current_power()
  selected_temp = db.get_selected_temp()
  current_temp = db.get_current_temp()

  if selected_power != current_power:
    msg.power_toggle()

    # ignore temperature change on power off
    if selected_power == 0:
      return msg

  if selected_temp != current_temp:
    delta = selected_temp - current_temp
    msg.temp_delta(delta * 2)

  return msg

def update_current(db):
  if db.check_timeout():
    db.clear_timeout()
    db.set_selected_power(0)

  selected_power = db.get_selected_power()
  current_power = db.get_current_power()

  # do not update temperature on power off
  if not (selected_power == 0 and current_power == 1):
    db.set_current_temp(db.get_selected_temp())
  db.set_current_power(selected_power)

def send(ser, msg):
  logging.info('tx: {}'.format(msg.get_str()))
  ser.write(msg.get_bytes())

def receive(ser, timeout = None):
  ser.timeout = timeout
  bytes_data = ser.read()

  # return null on timeout
  if not bytes_data:
    return None

  msg = Message.create_from_bytes(bytes_data)
  logging.info('rx: {}'.format(msg.get_str()))
  return msg

if __name__ == '__main__':
  main()
