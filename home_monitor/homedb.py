import sqlite3
import collections

class HomeDb:

  def __init__(self, path, logging):
    self._conn = sqlite3.connect(path)
    self._conn.row_factory = sqlite3.Row
    self._conn.set_trace_callback(logging.debug)
    self._cursor = self._conn.cursor()

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
      self._cursor.execute('UPDATE heater SET {} = ? WHERE heater_id = 1'.format(field), (value,))
      self._conn.commit()

  def _get_current_datetime(self):
    self._cursor.execute('SELECT datetime(\'now\', \'localtime\')')
    return self._cursor.fetchone()[0]

  # def check_timeout(self):
  #   timeout = self._get_control('timeout')
  #   if not timeout:
  #     return False
  #   return (self._get_current_datetime() > timeout)

  def clear_timeout(self):
    self._set_field('timeout', 'NULL');