<?php
  include "db.php";

  if ($_POST["action"] == "add") {
    AddSchedule($_POST["days"], $_POST["start"], $_POST["end"]);
  }
?>