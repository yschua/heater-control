<?php
  include "db.php";

  // TODO add validity checks

  if ($_POST["action"] == "add") {
    $schedules = $db->GetScheduleArray();
    $days = $db->GetDayArray();
    $GetDop = function($key) use ($days) { return $days[$key]["dop"]; };

    $startTime = $_POST["start"];
    $endTime = $_POST["end"];
    $dayKey = $_POST["dayKey"];
    $dop = $GetDop($dayKey);

    foreach ($schedules as $schedule) {
      $scheduleDop = $GetDop($schedule["day_id"]);

      if (($dop & $scheduleDop) != 0) {
        $scheduleStartTime = $schedule["start_time"];
        $scheduleEndTime = $schedule["end_time"];

        if ($startTime <= $scheduleEndTime && $scheduleStartTime <= $endTime) {
          echo json_encode(false);
          return;
        }
      }
    }

    $db->AddSchedule($dayKey, $startTime, $endTime);
    echo json_encode(true);
  }

  if ($_POST["action"] == "delete") {
    $db->DeleteSchedule($_POST["scheduleKey"]);
  }

  if ($_POST["action"] == "enable") {
    $db->SetEnableSchedule($_POST["scheduleKey"], $_POST["value"]);
  }
?>