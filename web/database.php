<?php
  $dir = "sqlite:../db/home.db";
  $pdo = new PDO($dir);
  $sql = "SELECT * FROM heater WHERE heater_id = 1";
  $stmt = $pdo->query($sql);
  $row = $stmt->fetchObject();

  function setHidden() {
    global $row;
    if (!$row->power)
      print "style=\"visibility: hidden;\"";
  }

  function disableButton() {
    global $row;
    if (!$row->power)
      print "disabled";
  }

  function showPower() {
    global $row;
    if ($row->power)
      print "ON";
    else
      print "OFF";
  }
?>