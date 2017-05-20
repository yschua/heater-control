<?php
  include "db.php";

  $message = $_POST["message"];
  if (isset($message)) {
    $message = substr($message, 1);
    $message = explode("-", $message);
    $operation = $message[0];
    $value = doubleval($message[1]);

    if ($operation == "p") {
      if ($value == 0 || $value == 1) {
        SetSelectedPower($value);
      }
    } else if ($operation == "t") {
      if ($value >= 10 && $value <= 20) {
        SetSelectedTemperature($value);
      }
    }
  }
?>
