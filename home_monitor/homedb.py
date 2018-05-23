import sqlite3
import datetime

class HomeDb:

  DATETIME_FMT = '%Y-%m-%d %H:%M:%S'
  TIME_FMT = '%H:%M'

  def __init__(self, path, logging):
    self._conn = sqlite3.connect(path)
    self._conn.row_factory = sqlite3.Row
    self._cursor = self._conn.cursor()
    self._logging = logging

  def commit(self):
    with self._log_query():
      self._conn.commit()

  def get_controls(self):
    self._cursor.execute('''
      SELECT
        selected_power,
        current_power,
        selected_temperature,
        current_temperature,
        timeout
      FROM heater WHERE heater_id = 1''')
    return Control(*self._cursor.fetchone())

  def set_control(self, field, value): # TODO create a update_control() instead
    self._cursor.execute('SELECT {} FROM heater WHERE heater_id = 1'.format(field))
    if self._cursor.fetchone()[0] != value:
      with self._log_query():
        self._cursor.execute(
          'UPDATE heater SET {} = ? WHERE heater_id = 1'.format(field),
          (value,))

  def get_datetime_now(self):
    self._cursor.execute(
      'SELECT strftime(?, ?, ?)',
      (self.DATETIME_FMT, 'now', 'localtime'))
    return self.convert_to_datetime(self._cursor.fetchone()[0])

  def get_datetime_timeout(self): # TODO get rid of this
    return self.get_controls().timeout

  def get_schedule_array(self):
    self._cursor.execute('''
      SELECT
        schedule_id,
        name,
        dop,
        start_time,
        end_time,
        is_enable,
        is_active
      FROM schedule JOIN day ON schedule.day_id = day.day_id''')
    rows = self._cursor.fetchall()
    return [Schedule(*row)  for row in rows]

  def update_schedule(self, schedule):
    # only update is_active for now
    with self._log_query():
      self._cursor.execute(
        'UPDATE schedule SET is_active = ? WHERE schedule_id = ?',
        (schedule.active, schedule.key))

  @staticmethod
  def convert_to_datetime(datetime_str):
    if datetime_str is None:
      return None
    return datetime.datetime.strptime(datetime_str, HomeDb.DATETIME_FMT)

  def _log_query(self):
    return LogQuery(self._conn, self._logging)


class LogQuery:

  def __init__(self, conn, logging):
    self._conn = conn
    self._logging = logging

  def __enter__(self):
    self._conn.set_trace_callback(self._logging.info)

  def __exit__(self, type, value, traceback):
    self._conn.set_trace_callback(None)


class Control:

  def __init__(self, sel_power, curr_power, sel_temp, curr_temp, timeout):
    self.selected_power = sel_power # force bool?
    self.current_power = curr_power # force bool?
    self.selected_temperature = sel_temp
    self.current_temperature = curr_temp
    self.timeout = HomeDb.convert_to_datetime(timeout)


class Schedule:

  def __init__(self, key, name, dop, start_time, end_time, enable, active):
    self.key = key
    self.name = name
    self.dop = dop
    self.start_time = datetime.datetime.strptime(start_time, HomeDb.TIME_FMT).time()
    self.end_time = datetime.datetime.strptime(end_time, HomeDb.TIME_FMT).time()
    self.enable = enable == 1
    self.active = active == 1

  def __str__(self):
    return 'Schedule(id={}) {} {} to {}'.format(
      self.key,
      self.name,
      self.start_time.strftime(HomeDb.TIME_FMT),
      self.end_time.strftime(HomeDb.TIME_FMT))
