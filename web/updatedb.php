<?php
  include "db.php";

  $message = $_POST["message"];
  if (isset($message)) {
    $message = substr($message, 1);
    $message = explode("-", $message);
    $operation = $message[0];
    $value = doubleval($message[1]);

    if (strtolower($operation) == "p") {
      if ($value == 0 || $value == 1) {
        SetSelectedPower($value);
        if ($operation == "P") {
          SetCurrentPower($value);
        }
        if ($value == 0) {
          SetTimeout(0);
        }
      }
    } else if (strtolower($operation) == "t") {
      if ($value >= 10 && $value <= 20) {
        SetSelectedTemperature($value);
        if ($operation == "T") {
          SetCurrentTemperature($value);
        }
      }
    } else if ($operation == "o") {
      if ($value >= 0) {
        SetTimeout($value);
      }
    }
  }
?>
