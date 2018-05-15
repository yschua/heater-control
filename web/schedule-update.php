<?php
  include "db.php";

  // TODO add validity checks

  if ($_POST["action"] == "add") {
    $db->AddSchedule($_POST["dayKey"], $_POST["start"], $_POST["end"]);
  }

  if ($_POST["action"] == "delete") {
    $db->DeleteSchedule($_POST["scheduleKey"]);
  }

  if ($_POST["action"] == "enable") {
    $db->SetEnableSchedule($_POST["scheduleKey"], $_POST["value"]);
  }
?>