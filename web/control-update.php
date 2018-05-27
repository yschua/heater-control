<?php
  include "db.php";

  const DEFAULT_TIMEOUT = 30;
  const MIN_TEMP = 10.0;
  const MAX_TEMP = 20.0;

  $message = $_POST["message"];

  if (!isset($message)) {
    return;
  }

  $message = substr($message, 1);
  $message = explode("-", $message);
  $operation = $message[0];
  $value = doubleval($message[1]);

  if (strtolower($operation) == "p") {
    $currSelectedPower = $db->GetSelectedPower();
    if ($value == 0 || $value == 1) {
      $db->SetSelectedPower($value);
      if ($operation == "P") { // calibrate message
        $db->SetCurrentPower($value);
      }
      if ($value != $currSelectedPower)
      {
        if ($value == 0) { // on power off
          $db->SetTimeout(0);
        } else { // on power on
          $db->SetTimeout(DEFAULT_TIMEOUT); // always default to a timeout value
        }
        $db->DeactivateSchedule();
      }
    }
  }

  if (strtolower($operation) == "t") {
    $value = min(MAX_TEMP, max(MIN_TEMP, $value));
    $db->SetSelectedTemperature($value);
    if ($operation == "T") { // calibrate message
      $db->SetCurrentTemperature($value);
    }
  }

  if ($operation == "o") {
    if ($value >= 0 && $db->GetSelectedPower() != 0) {
      $db->SetTimeout($value);
      $db->DeactivateSchedule();
    }
  }
?>