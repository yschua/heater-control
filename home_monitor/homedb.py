import sqlite3
import datetime

class HomeDb:

  DATETIME_FMT = '%Y-%m-%d %H:%M:%S'
  TIME_FMT = '%H:%M'

  def __init__(self, path, logging):
    self._conn = sqlite3.connect(path)
    self._conn.row_factory = sqlite3.Row
    self._cur = self._conn.cursor()
    self._logging = logging
    self._modified = False

  def commit(self):
    if self._modified:
      with self._log_query():
        self._conn.commit()
      self._modified = False

  def get_control(self):
    self._cur.execute('''
      SELECT
        selected_power,
        current_power,
        selected_temperature,
        current_temperature,
        timeout,
        is_on
      FROM heater WHERE heater_id = 1''')
    return Control(*self._cur.fetchone())

  def update_control(self, ctl):
    if not ctl.modified:
      return

    self._set_control('is_on', ctl.is_on)
    self._set_control('selected_power', ctl.selected_power)
    self._set_control('current_power', ctl.current_power)
    self._set_control('selected_temperature', ctl.selected_temperature)
    self._set_control('current_temperature', ctl.current_temperature)
    self._set_control('timeout', self.convert_to_db_timestamp(ctl.timeout))

  def _set_control(self, field, value):
    self._cur.execute('SELECT {} FROM heater WHERE heater_id = 1'.format(field))
    if self._cur.fetchone()[0] != value:
      self._modified = True
      with self._log_query():
        self._cur.execute('UPDATE heater SET {} = ?'.format(field), (value,))

  def get_datetime_now(self):
    self._cur.execute(
      'SELECT strftime(?, ?, ?)',
      (self.DATETIME_FMT, 'now', 'localtime'))
    return self.convert_to_datetime(self._cur.fetchone()[0])

  def get_schedule_array(self):
    self._cur.execute('''
      SELECT
        schedule_id,
        name,
        dop,
        start_time,
        end_time,
        is_enable,
        is_active
      FROM schedule JOIN day ON schedule.day_id = day.day_id''')
    rows = self._cur.fetchall()
    return [Schedule(*row)  for row in rows]

  def update_schedule(self, schedule):
    # only update is_active for now
    self._modified = True
    with self._log_query():
      self._cur.execute(
        'UPDATE schedule SET is_active = ? WHERE schedule_id = ?',
        (schedule.active, schedule.key))

  def get_on_off_cycle(self):
    self._cur.execute('''
      SELECT
        is_active,
        on_duration,
        off_duration,
        last_cycle
      FROM on_off_cycle''')
    return OnOffCycle(*self._cur.fetchone())

  def update_on_off_cycle(self, data):
    if not data.modified:
      return

    self._modified = True
    with self._log_query():
      db_timestamp = self.convert_to_db_timestamp(data.last_cycle)
      self._cur.execute('UPDATE on_off_cycle SET last_cycle = ?', (db_timestamp,))

  @staticmethod
  def convert_to_datetime(db_timestamp):
    if db_timestamp is None:
      return None
    return datetime.datetime.strptime(db_timestamp, HomeDb.DATETIME_FMT)

  @staticmethod
  def convert_to_db_timestamp(dt):
    return dt.strftime(HomeDb.DATETIME_FMT) if dt else None

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

  def __init__(self, sel_power, curr_power, sel_temp, curr_temp, timeout, is_on):
    self.selected_power = sel_power
    self.current_power = curr_power
    self.selected_temperature = float(sel_temp)
    self.current_temperature = float(curr_temp)
    self.timeout = HomeDb.convert_to_datetime(timeout)
    self.is_on = is_on
    self.modified = False


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


class OnOffCycle:

  def __init__(self, is_active, on_duration, off_duration, last_cycle):
    self.is_active = is_active
    self.on_duration = on_duration
    self.off_duration = off_duration
    self.last_cycle = HomeDb.convert_to_datetime(last_cycle)
    self.modified = False

  def set_last_cycle(self, last_cycle):
    self.last_cycle = last_cycle
    self.modified = True

  def get_on_end_datetime(self):
    return self.last_cycle + datetime.timedelta(minutes=self.on_duration)

  def get_cycle_end(self, power):
    duration = self.on_duration if power else self.off_duration
    return self.last_cycle + datetime.timedelta(minutes=duration)
