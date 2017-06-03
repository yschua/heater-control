import sqlite3

class HomeDb:

  def __init__(self, path, logging):
    self._conn = sqlite3.connect(path)
    self._conn.row_factory = sqlite3.Row
    self._cursor = self._conn.cursor()
    self._logging = logging

  def _get_field(self, field):
    sql = 'SELECT {} FROM heater WHERE heater_id = 1'.format(field)
    self._cursor.execute(sql)
    return self._cursor.fetchone()[0]

  def get_selected_power(self):
    return self._get_field('selected_power')

  def get_current_power(self):
    return self._get_field('current_power')

  def get_selected_temp(self):
    return self._get_field('selected_temperature')

  def get_current_temp(self):
    return self._get_field('current_temperature')

  def _set_field(self, field, value):
    if self._get_field(field) == value:
      return

    sql = 'UPDATE heater SET {} = {} WHERE heater_id = 1'.format(field, value)
    self._logging.info(sql)
    self._cursor.execute(sql)
    self._conn.commit()

  def set_selected_power(self, value):
    self._set_field('selected_power', value)

  def set_current_power(self, value):
    self._set_field('current_power', value)

  def set_selected_temp(self, value):
    self._set_field('selected_temperature', value)

  def set_current_temp(self, value):
    self._set_field('current_temperature', value)