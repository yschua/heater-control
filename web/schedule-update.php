<?php
  include "db.php";

  if ($_POST["action"] == "add") {
    $db->AddSchedule($_POST["dayKey"], $_POST["start"], $_POST["end"]);
  }

  if ($_POST["action"] == "delete") {
    $db->DeleteSchedule($_POST["scheduleKey"]);
  }
?>