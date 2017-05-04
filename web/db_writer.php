<?php
  include "data.php";

    
  $MAX_TEMP = 20.0;
  $MIN_TEMP = 10.0;

  $action = $_POST['action'];
  if (isset($action)) {
    switch ($action) {
      case 'increase':
        changeTemperature(+0.5); 
        break;
      case 'decrease':
        changeTemperature(-0.5);
        break;
      case 'power':
        changePower();
        break;
      default:
    }
  }

  function changeTemperature($offset) {
    global $pdo;
    global $row;
    global $MAX_TEMP;
    global $MIN_TEMP;

    if (!$row->power) 
      return;

    $temperature = $row->temperature + $offset;
    if ($temperature > $MAX_TEMP || $temperature < $MIN_TEMP)
      return;

    $sql = "UPDATE heater SET temperature = ".$temperature." WHERE heater_id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
  }

  function changePower() {
    global $pdo;
    global $row;

    $power = 1 - $row->power;
    $sql = "UPDATE heater SET power = ".$power." WHERE heater_id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
  }

?>
