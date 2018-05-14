<?php
  class Db
  {
    private $db;
    private $controls;

    function __construct($filename)
    {
      $this->db = new SQLite3($filename);
      $this->controls = $this->GetControls();
    }

    /* Control methods */

    private function GetControls()
    {
      $stmt = $this->db->prepare("SELECT * FROM heater WHERE heater_id = 1");
      $result = $stmt->execute();
      return $result->fetchArray(SQLITE3_ASSOC);
    }

    function GetCurrentPower() { return $this->controls["current_power"]; }
    function GetSelectedPower() { return $this->controls["selected_power"]; }

    function GetCurrentTemperature() { return $this->controls["current_temperature"]; }
    function GetSelectedTemperature() { return $this->controls["selected_temperature"]; }

    function GetTimeoutStr()
    {
      $timeout = $this->controls["timeout"];

      if (is_null($timeout)) {
        return "OFF";
      }

      $stmt = $this->db->prepare("SELECT strftime('%H:%M', ?)");
      $stmt->bindValue(1, $timeout, SQLITE3_TEXT);
      $result = $stmt->execute();
      return $result->fetchArray(SQLITE3_NUM)[0];
    }

    private function SetControl($field, $value)
    {
      $query = sprintf("UPDATE heater SET %s = %s WHERE heater_id = 1", $field, $value);
      $stmt = $this->db->prepare($query);
      $stmt->execute();
    }

    function SetCurrentPower($value)
    {
      if ($value != $this->GetCurrentPower()) {
        $this->SetControl("current_power", $value);
      }
    }

    function SetSelectedPower($value)
    {
      if ($value != $this->GetSelectedPower()) {
        $this->SetControl("selected_power", $value);
      }
    }

    function SetCurrentTemperature($value)
    {
      if ($value != $this->GetCurrentTemperature()) {
        $this->SetControl("current_temperature", $value);
      }
    }

    function SetSelectedTemperature($value)
    {
      if ($value != $this->GetSelectedTemperature()) {
        $this->SetControl("selected_temperature", $value);
      }
    }

    function SetTimeout($timeout)
    {
      $datetime = ($timeout > 0) 
        ? sprintf("datetime('now', 'localtime', '+%d minutes')", $timeout)
        : "NULL";
      $this->SetControl("timeout", $datetime);
    }

    /* Schedule methods */

    function GetScheduleArray()
    {
      $stmt = $this->db->prepare("SELECT * FROM schedule");
      $result = $stmt->execute();
      $ret = array();
      while ($schedule = $result->fetchArray(SQLITE3_ASSOC)) {
        array_push($ret, $schedule);
      }
      return $ret;
    }

    function AddSchedule($days, $start, $end)
    {
      if ($start >= $end) {
        return;
      }
      $stmt = $this->db->prepare("INSERT INTO schedule (days, start_time, end_time) VALUES (?, ?, ?)");
      $stmt->bindValue(1, $days, SQLITE3_TEXT);
      $stmt->bindValue(2, $start, SQLITE3_TEXT);
      $stmt->bindValue(3, $end, SQLITE3_TEXT);
      $stmt->execute();
    }

    function DeleteSchedule($key)
    {
      $stmt = $this->db->prepare("DELETE FROM schedule WHERE schedule_id = ?");
      $stmt->bindValue(1, $key, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  $db = new DB("../db/home.db");
?>