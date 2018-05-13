<?php
  include "db.php";

  const DEFAULT_TIMEOUT = 30;
  const MIN_TEMP = 10;
  const MAX_TEMP = 20;

  $message = $_POST["message"];

  if (!isset($message)) {
    return;
  }

  $message = substr($message, 1);
  $message = explode("-", $message);
  $operation = $message[0];
  $value = doubleval($message[1]);

  if (strtolower($operation) == "p") {
    if ($value == 0 || $value == 1) {
      SetSelectedPower($value);
      if ($operation == "P") { // calibrate message
        SetCurrentPower($value);
      }
      if ($value == 0) { // on power off
        SetTimeout(0);
      } else if (GetSelectedPower() != 1) { // always default to a timeout value
        SetTimeout(DEFAULT_TIMEOUT);
      }
    }
  }

  if (strtolower($operation) == "t") {
    if ($value >= MIN_TEMP && $value <= MAX_TEMP) {
      SetSelectedTemperature($value);
      if ($operation == "T") { // calibrate message
        SetCurrentTemperature($value);
      }
    }
  }

  if ($operation == "o") {
    if ($value >= 0) {
      SetTimeout($value);
    }
  }

  if ($operation == "s") {
    SetScheduleEnable($value);
  }
?>
