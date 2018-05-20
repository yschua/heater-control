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

    c = self._db.get_controls()

    if c.selected_power != c.current_power:
      msg.power_toggle()

      # ignore temperature change on power off
      if c.selected_power == 0:
        return msg

    if c.selected_temperature != c.current_temperature:
      delta = c.selected_temperature - c.current_temperature
      msg.temp_delta(delta * 2)

    return msg

  def _update_current_control(self):
    c = self._db.get_controls()

    # do not update temperature on power off
    if not (c.selected_power == 0 and c.current_power == 1):
      self._db.set_control('current_temperature', c.selected_temperature)

    self._db.set_control('current_power', c.selected_power)

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
