<?php
  $pdo = new PDO("sqlite:../db/home.db");
  $stmt = $pdo->query("SELECT * FROM heater WHERE heater_id = 1");
  $row = $stmt->fetchObject();

  function GetUpdateSql($field, $val) {
    $query = sprintf("UPDATE heater SET %s = %s WHERE heater_id = 1", $field, $val);
    error_log($query);
    return $query;
  }

  function GetCurrentPower() {
    global $row;
    return ($row->current_power == 1);
  }

  function SetCurrentPower($power) {
    if ($power != GetCurrentPower()) {
      global $pdo;
      $stmt = $pdo->prepare(GetUpdateSql("current_power", $power));
      $stmt->execute();
    }
  }

  function GetSelectedPower() {
    global $row;
    return ($row->selected_power == 1);
  }

  function SetSelectedPower($power) {
    if ($power != GetSelectedPower()) {
      global $pdo;
      $stmt = $pdo->prepare(GetUpdateSql("selected_power", $power));
      $stmt->execute();
    }
  }

  function BoundTemperature($temperature) {
    $max = 20.0;
    $min = 10.0;
    return min($max, max($min, $temperature));
  }

  function GetCurrentTemperature() {
    global $row;
    return $row->current_temperature;
  }

  function SetCurrentTemperature($temperature) {
    if ($temperature != GetCurrentTemperature()) {
      global $pdo;
      $stmt = $pdo->prepare(GetUpdateSql("current_temperature", BoundTemperature($temperature)));
      $stmt->execute();
    }
  }

  function GetSelectedTemperature() {
    global $row;
    return $row->selected_temperature;
  }

  function SetSelectedTemperature($temperature) {
    if ($temperature != GetSelectedTemperature()) {
      global $pdo;
      $stmt = $pdo->prepare(GetUpdateSql("selected_temperature", BoundTemperature($temperature)));
      $stmt->execute();
    }
  }

  function GetTimeoutStr() {
    global $row;
    global $pdo;

    if (is_null($row->timeout)) {
      return "OFF";
    }

    $query = sprintf("SELECT strftime('%%H:%%M', '%s')", $row->timeout);
    $stmt = $pdo->query($query);
    return $stmt->fetch()[0];
  }

  function SetTimeout($timeout) {
    global $pdo;
    $datetime = ($timeout > 0)
      ? sprintf("datetime('now', 'localtime', '+%d minutes')", $timeout)
      : "NULL";
    $stmt = $pdo->prepare(GetUpdateSql("timeout", $datetime));
    $stmt->execute();
  }
?>