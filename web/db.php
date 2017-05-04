<?php
  $dir = "sqlite:../db/home.db";
  $pdo = new PDO($dir);
  $stmt = $pdo->query("SELECT * FROM heater WHERE heater_id = 1");
  $row = $stmt->fetchObject();

  function GetCurrentPower() {
    global $row;
    return ($row->current_power == 1);
  }

  function SetCurrentPower($power) {
    global $pdo;
    $stmt = $pdo->prepare(GetUpdateSql("current_power", $power));
    $stmt->execute();
  }

  function GetUpdateSql($field, $val) {
    return sprintf("UPDATE heater set %s = %d WHERE heater_id = 1", $field, $val);
  }
?>