<?php
  include "db.php";

  $action = $_POST["action"];
  if (isset($action)) {
    switch($action) {
      case "#power-on":
        SetCurrentPower(1);
        break;
      case "#power-off":
        SetCurrentPower(0);
        break;
      default:
    }
  }
?>
