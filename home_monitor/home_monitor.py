import serial
import time
import logging
import threading
import signal
import sys
from homedb import HomeDb
from message import Message

# TODO auto check OS?
OS = 'Windows'

if OS == 'Unix':
  PORT = '/dev/ttyUSB0'
  DB_PATH = '../db/home.db'
  LOG_PATH = 'monitor.log'
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
    format='[%(asctime)s] (%(thread)d) %(threadName)s: %(message)s')

  logging.info('started')

  exit_handler = ExitHandler()

  # initialise threads
  gateway_server = GatewayServer()
  gateway_server.start()

  while not exit_handler.exit():
    time.sleep(1)

  # stop threads
  logging.debug('stopping threads')
  gateway_server.stop()
  gateway_server.join()

  logging.info('ended')


class GatewayServer(threading.Thread):

  def __init__(self):
    threading.Thread.__init__(self)
    self.name = 'GatewayServer'
    self._stop_event = threading.Event()

  def stop(self):
    self._stop_event.set()

  def run(self):
    logging.debug('started')

    self._db = HomeDb(DB_PATH, logging)
    self._ser = serial.Serial(PORT, SERIAL_BAUD)

    while not self._stop_event.is_set():
      msg_recv = self._receive(1)

      if not msg_recv or msg_recv.get() != REQUEST:
        continue

      # timing is very critical between request receive and request reply
      # this means no expensive queries like sql updates until message is sent

      msg_send = self._get_message()

      # Issue #11: reply with null message or silence?
      if msg_send.get() == 0x0:
        continue

      self._send(msg_send)
      msg_recv = self._receive(1) # will have to play around with this timeout

      if msg_recv and msg_recv.get() == SUCCESS:
        self._update_current_control()
      else:
        logging.error('update failed')

    self._ser.close()

    logging.debug('ended')

  def _get_message(self):
    msg = Message()

    # turn off on timeout
    if self._db.check_timeout():
      msg.power_toggle()
      return msg

    selected_power = self._db.get_selected_power()
    current_power = self._db.get_current_power()
    selected_temp = self._db.get_selected_temp()
    current_temp = self._db.get_current_temp()

    if selected_power != current_power:
      msg.power_toggle()

      # ignore temperature change on power off
      if selected_power == 0:
        return msg

    if selected_temp != current_temp:
      delta = selected_temp - current_temp
      msg.temp_delta(delta * 2)

    return msg

  def _update_current_control(self):
    if self._db.check_timeout():
      self._db.clear_timeout()
      self._db.set_selected_power(0)

    selected_power = self._db.get_selected_power()
    current_power = self._db.get_current_power()

    # do not update temperature on power off
    if not (selected_power == 0 and current_power == 1):
      self._db.set_current_temp(self._db.get_selected_temp())
    self._db.set_current_power(selected_power)

  def _send(self, msg):
    logging.debug('tx: {}'.format(msg.get_str()))
    self._ser.write(msg.get_bytes())

  def _receive(self, timeout = None):
    self._ser.timeout = timeout
    bytes_data = self._ser.read()

    # return null on timeout
    if not bytes_data:
      return None

    msg = Message.create_from_bytes(bytes_data)
    logging.debug('rx: {}'.format(msg.get_str()))
    return msg


class ExitHandler:

  def __init__(self):
    self._exit = False
    signal.signal(signal.SIGINT, self._set_exit)
    signal.signal(signal.SIGTERM, self._set_exit)
    signal.signal(signal.SIGABRT, self._set_exit)

  def _set_exit(self, signum, frame):
    self._exit = True

  def exit(self):
    return self._exit


if __name__ == '__main__':
  main()
