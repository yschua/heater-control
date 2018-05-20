import sqlite3
import collections
import datetime

class HomeDb:

  DATETIME_FMT = '%Y-%m-%d %H:%M:%S'

  def __init__(self, path, logging):
    self._conn = sqlite3.connect(path)
    self._conn.row_factory = sqlite3.Row
    self._cursor = self._conn.cursor()
    self._logging = logging

  def get_controls(self):
    self._cursor.execute('SELECT * FROM heater WHERE heater_id = 1')
    all_values = self._cursor.fetchone()
    readable_fields = [
      'selected_power',
      'current_power',
      'selected_temperature',
      'current_temperature']
    readable_values = [all_values[f] for f in readable_fields]
    return collections.namedtuple('Control', readable_fields)._make(readable_values)

  def set_control(self, field, value):
    self._cursor.execute('SELECT {} FROM heater WHERE heater_id = 1'.format(field))
    if self._cursor.fetchone()[0] != value:
      with self._log_query():
        self._cursor.execute('UPDATE heater SET {} = ? WHERE heater_id = 1'.format(field), (value,))
        self._conn.commit()

  def get_datetime_now(self):
    self._cursor.execute('SELECT strftime(?, ?, ?)', (self.DATETIME_FMT, 'now', 'localtime'))
    return self._convert_to_datetime(self._cursor.fetchone()[0])

  def get_datetime_timeout(self):
    self._cursor.execute('SELECT timeout FROM heater WHERE heater_id = 1')
    return self._convert_to_datetime(self._cursor.fetchone()[0])

  def _convert_to_datetime(self, datetime_str):
    if datetime_str is None:
      return None
    return datetime.datetime.strptime(datetime_str, self.DATETIME_FMT)

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
