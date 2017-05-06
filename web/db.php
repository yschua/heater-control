<?php
  $pdo = new PDO("sqlite:../db/home.db");
  $stmt = $pdo->query("SELECT * FROM heater WHERE heater_id = 1");
  $row = $stmt->fetchObject();

  function GetUpdateSql($field, $val) {
    return sprintf("UPDATE heater SET %s = %d WHERE heater_id = 1", $field, $val);
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
?>