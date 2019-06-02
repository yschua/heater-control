import serial
import time
import logging
import threading
import signal
import sys
import datetime
from homedb import HomeDb
from message import Message

# TODO auto check OS?
OS = 'Windows'

if OS == 'Unix':
  PORT = '/dev/ttyUSB0'
  DB_PATH = '/var/git/heater-control/db/home.db'
  LOG_PATH = '/var/git/heater-control/home_monitor/monitor.log'
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
    level=logging.INFO, # TODO set this with command line argument
    format='[%(asctime)s] (%(thread)d) %(threadName)s: %(message)s')

  logging.info('started')

  exit_handler = ExitHandler()

  # initialise threads
  threads = [GatewayServer(), Scheduler()]
  for thread in threads:
    thread.start()

  while not exit_handler.exit():
    time.sleep(1)

  # stop threads
  logging.debug('stopping threads')
  for thread in threads:
    thread.stop()
    thread.join()

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

      if msg_send.get() == 0x0:
        # Issue #11: reply with null message or silence?
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
    ctl = self._db.get_control()

    if ctl.selected_power != ctl.current_power:
      msg.power_toggle()

    if ctl.selected_power and ctl.selected_temperature != ctl.current_temperature:
      delta = ctl.selected_temperature - ctl.current_temperature
      msg.temp_delta(delta * 2)

    return msg

  def _update_current_control(self):
    # current controls should only be updated here
    ctl = self._db.get_control()

    if ctl.selected_power:
      ctl.current_temperature = ctl.selected_temperature
    ctl.current_power = ctl.selected_power
    ctl.modified = True

    self._db.update_control(ctl)
    self._db.commit()

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


class Scheduler(threading.Thread):
  # Only one schedule is allowed to be active at any time, this means overlapping
  # schedules are ignored. Don't create overlapping schedules!
  # On any change of control values by a user, except the temperature, any active
  # schedule will be deactivated

  def __init__(self):
    threading.Thread.__init__(self)
    self.name = 'Scheduler'
    self._stop_event = threading.Event()

  def stop(self):
    self._stop_event.set()

  def run(self):
    logging.debug('started')

    self._db = HomeDb(DB_PATH, logging)

    while not self._stop_event.is_set():
      self._now = self._db.get_datetime_now()
      ctl = self._db.get_control()
      schedules = self._db.get_schedule_array()
      running_schedule = self._get_running_schedule(schedules)

      ctl, running_schedule = self._stop_rogue_schedule(ctl, running_schedule)
      ctl, running_schedule = self._stop_on_timeout(ctl, running_schedule)
      ctl = self._start_new_schedule(ctl, running_schedule, schedules)
      ctl = self._handle_selected_power(ctl, running_schedule)

      # these functions only commit if there are changes
      self._db.update_control(ctl)
      self._db.commit()

      time.sleep(1)

    logging.debug('ended')

  def _stop_rogue_schedule(self, ctl, running_schedule):
    if running_schedule is None:
      return ctl, None

    start = self._get_datetime_today(running_schedule.start_time)
    end = self._get_datetime_today(running_schedule.end_time)
    endElapsed = self._now - end

    if (self._now < start or
        endElapsed.total_seconds() > 5 or
        ctl.is_on == False or
        ctl.timeout != end):
      logging.warning('Unexpected state, stopping {}'.format(running_schedule))
      running_schedule.active = False
      self._db.update_schedule(running_schedule)

    return ctl, running_schedule

  def _stop_on_timeout(self, ctl, running_schedule):
    if ctl.timeout and ctl.timeout < self._now:
        if running_schedule and running_schedule.active:
          logging.info('Stopping {}'.format(running_schedule))
          running_schedule.active = False
          self._db.update_schedule(running_schedule)
        else:
          logging.info('Timeout reached, turning off heater')
        ctl.is_on = False
        ctl.timeout = None
        ctl.modified = True

    return ctl, running_schedule

  def _start_new_schedule(self, ctl, running_schedule, schedules):
    if ((running_schedule is None or not running_schedule.active) and
        ctl.is_on == False):
      new_schedule = self._get_new_schedule(schedules)
      if new_schedule:
        logging.info('Starting {}'.format(new_schedule))
        ctl.is_on = ctl.selected_power = True
        ctl.timeout = self._get_datetime_today(new_schedule.end_time)
        ctl.modified = True
        new_schedule.active = True
        self._db.update_schedule(new_schedule)

    return ctl

  @staticmethod
  def _set_selected_power_from_is_on(ctl):
    if ctl.is_on != ctl.selected_power:
      ctl.selected_power = ctl.is_on
      ctl.modified = True
    return ctl

  def _handle_selected_power(self, ctl, running_schedule):
      on_off_cycle = self._db.get_on_off_cycle()

      # if not dealing with on-off cycle
      if not ctl.is_on or not on_off_cycle.is_active:
        if on_off_cycle.last_cycle:
          on_off_cycle.set_last_cycle(None)
          self._db.update_on_off_cycle(on_off_cycle)
        ctl = self._set_selected_power_from_is_on(ctl)
        return ctl

      datetime_now = datetime.datetime.now()

      if on_off_cycle.last_cycle is None:
        on_off_cycle.set_last_cycle(datetime_now)

      if datetime_now > on_off_cycle.get_cycle_end(ctl.selected_power):
        logging.info('{} cycle end. Turning {}.'.format(
          "On" if ctl.selected_power else "Off",
          "off" if ctl.selected_power else "on"))
        ctl.selected_power = not ctl.selected_power
        ctl.modified = True
        on_off_cycle.set_last_cycle(datetime_now)

      self._db.update_on_off_cycle(on_off_cycle)
      return ctl

  def _get_running_schedule(self, schedules):
    for schedule in schedules:
      if schedule.active:
        return schedule
    return None

  def _get_new_schedule(self, schedules):
    for schedule in schedules:
      if schedule.enable and not schedule.active:
        dop = 1 << self._now.date().weekday()
        if (dop & schedule.dop) != 0:
          start = self._get_datetime_today(schedule.start_time)
          delta_seconds = (self._now - start).total_seconds()
          if 0 <= delta_seconds < 5:
            return schedule
    return None

  def _get_datetime_today(self, time):
    return datetime.datetime.combine(self._now.date(), time)


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
