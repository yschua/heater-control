<?php
  include "db.php";

  if ($_POST["action"] == "enable") {
    $db->SetCycleActive($_POST["value"]);
  }

  if ($_POST["action"] == "changeOn") {
    $db->SetOnDuration($_POST["value"]);
  }

  if ($_POST["action"] == "changeOff") {
    $db->SetOffDuration($_POST["value"]);
  }
?>