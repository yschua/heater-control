<?php
  include "db.php";

  $message = $_POST["message"];
  if (isset($message)) {
    $message = substr($message, 1);
    $message = explode("-", $message);
    $operation = $message[0];
    $value = intval($message[1]);

    if ($operation == "p") {
      if ($value == 0 || $value == 1) {
        SetCurrentPower($value);
      }
    }
  }
?>
