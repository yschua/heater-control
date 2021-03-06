<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  <div class="active-schedule text-success">
      <?php
        $activeSchedule = $db->GetActiveSchedule();
          if (!empty($activeSchedule)) {
          $days = $db->GetDayArray();
          printf(
            "Scheduled <span class=\"glyphicon glyphicon-fire\"></span><br>%s %s-%s",
            $days[$activeSchedule["day_id"]]["name"],
            $activeSchedule["start_time"],
            $activeSchedule["end_time"]
          );
        }
      ?>
  </div>

  <!-- Power control -->
  <div class="control-item">
    <div class="control-label">Power</div>
    <div class="btn-group control-input">
      <a href="#p-1" class="btn btn-lg btn-input col-xs-6 btn-<?php echo $db->GetIsOn() ? "primary active" : "default";?>">On</a>
      <a href="#p-0" class="btn btn-lg btn-input col-xs-6 btn-<?php echo !$db->GetIsOn() ? "danger active" : "default";?>">Off</a>
    </div>
  </div>

  <!-- Temperature control -->
  <div class="control-item">
    <div class="control-label">Temperature</div>
    <div class="control-input dropdown">
      <button class="btn btn-lg btn-block btn-default dropdown-toggle" type="button" data-toggle="dropdown">
        <?php printf("%.1f", $db->GetSelectedTemperature());?>&deg;C <span class="caret" />
      </button>
      <ul class="dropdown-menu">
        <?php
          for ($temp = 15.0; $temp <= 20.0; $temp += 0.5) {
            printf(
              "<li><a href=\"#t-%.1f\" class=\"btn-input%s\">%.1f</a></li>\n",
              $temp,
              ($db->GetSelectedTemperature() == $temp) ? " btn-default" : "",
              $temp
            );
          }
        ?>
      </ul>
    </div>
  </div>

  <!-- Timeout control -->
  <div class="control-item">
    <div class="control-label">Timeout</div>
    <div class="control-input dropdown">
      <button class="btn btn-lg btn-block btn-default dropdown-toggle" type="button" data-toggle="dropdown" <?php if (!$db->GetIsOn()) { echo "disabled"; }?>>
        <?php echo $db->GetTimeoutStr(); ?> <span class="caret" />
      </button>
      <ul class="dropdown-menu">
        <li><a href="#o-0" class="btn-input">Off</a></li>
        <li><a href="#o-20" class="btn-input">20 min</a></li>
        <li><a href="#o-40" class="btn-input">40 min</a></li>
        <li><a href="#o-60" class="btn-input">60 min</a></li>
        <li><a href="#o-90" class="btn-input">90 min</a></li>
        <li><a href="#o-120" class="btn-input">120 min</a></li>
        <li><a href="#o-240" class="btn-input">240 min</a></li>
      </ul>
    </div>
  </div>

  <hr>

  <!-- On-Off Cycle control -->
  <div class="control-item">
    <div class="control-label">On-Off Cycle</div>
    <div class="control-input">
      <input class="enable-onoffcycle" type="checkbox" data-toggle="toggle"
        <?php if ($db->GetCycleActive()) printf("checked"); ?>>
    </div>
  </div>

  <?php
    function DurationOptions($selected)
    {
      for ($min = 5; $min <= 40; $min += 5) {
        if ($selected == $min) {
          printf("<option id=\"%d\" selected>%d min</option>", $min, $min);
        } else {
          printf("<option id=\"%d\">%d min</option>", $min, $min);
        }
      }
    }
  ?>

  <div class="control-item">
    <div class="control-label">On Duration</div>
    <div class="control-input">
      <select class="form-control input-lg change-oncycletime">
        <?php DurationOptions($db->GetOnDuration()); ?>
      </select>
    </div>
  </div>

  <div class="control-item">
    <div class="control-label">Off Duration</div>
    <div class="control-input">
      <select class="form-control input-lg change-offcycletime">
        <?php DurationOptions($db->GetOffDuration()); ?>
      </select>
    </div>
  </div>

  <hr>

  <!-- Schedules -->
  <table class="table table-schedule">
    <thead>
      <tr>
        <th></th>
        <th class="col-xs-3">Days</th>
        <th>Start</th>
        <th>End</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <!-- List schedules -->
      <?php
        foreach ($db->GetScheduleArray() as $schedule) {
          $key = $schedule["day_id"];
          printf(
            "<tr>
              <td><input class=\"enable-schedule\" id=\"%s\" type=\"checkbox\" data-toggle=\"toggle\" data-size=\"mini\" %s></td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td><button class=\"btn btn-danger delete-schedule\" id=\"%s\" type=\"button\" title=\"Remove\">&times;</button></td>
             </tr>",
            $schedule["schedule_id"],
            $schedule["is_enable"] == 1 ? "checked" : "",
            $db->GetDayArray()[$key]["name"],
            $schedule["start_time"],
            $schedule["end_time"],
            $schedule["schedule_id"]
          );
        }
      ?>
      <!-- Insert schedule -->
      <tr>
        <td></td>
        <td>
          <select class="form-control" id="scheduleDays">
            <?php
              $days = $db->GetDayArray();
              $keys = array_keys($days);
              foreach (array_combine($keys, $days) as $key => $day) {
                printf("<option id=\"%d\">%s</option>", $key, $day["name"]);
              }
            ?>
          </select>
        </td>
        <td>
          <div class="input-group clockpicker">
            <input type="text" class="form-control" value="08:00" id="scheduleStart" readonly>
            <span class="input-group-addon no-indent"><span class="glyphicon glyphicon-time"></span></span>
          </div>
        </td>
        <td>
          <div class="input-group clockpicker">
            <input type="text" class="form-control" value="08:30" id="scheduleEnd" readonly>
            <span class="input-group-addon no-indent"><span class="glyphicon glyphicon-time"></span></span>
          </div>
        </td>
        <td><button class="btn btn-primary add-schedule" type="button" title="Add">&plus;</button></td>
      </tr>
    </tbody>
  </table>

</div>

<?php
  require "footer.php";
?>

<script type="text/javascript" src="bootstrap/js/bootstrap-clockpicker.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap-toggle.min.js"></script>
<script type="text/javascript">
  // Clockpicker
  $(".clockpicker").clockpicker({
    autoclose: true,
    placement: 'top'
  });

  var fnReload = function() { location.reload(); };

  // Heater controls
  // TODO fix this retarded message encoded in href hack
  $(document).ready(function() {
    $(".btn-input").click(function() {
      var url = "control-update.php";
      var message = $(this).attr("href");
      var data = { "message": message };
      $.post(url, data, fnReload);
    });
  });

  // Insert schedule
  $(document).ready(function() {
    $(".add-schedule").click(function() {
      var daysElem = document.getElementById("scheduleDays");
      var dayKey = daysElem[daysElem.selectedIndex].id;
      var start = document.getElementById("scheduleStart").value;
      var end = document.getElementById("scheduleEnd").value;

      if (start >= end) {
        alert("Invalid times.");
        return;
      }

      $.ajax({
        url: "schedule-update.php",
        data: {
          "action": "add",
          "dayKey": dayKey,
          "start": start,
          "end": end
        },
        type: "POST",
        dataType: "json"
      }).done(function(valid) {
        if (valid) {
          fnReload();
        } else {
          alert("Times overlap with an existing schedule.");
        }
      });
    });
  });

  // Delete schedule
  $(document).ready(function() {
    $(".delete-schedule").click(function() {
      if (window.confirm("Click OK to remove.")) {
        var url = "schedule-update.php";
        var data = {
          "action": "delete",
          "scheduleKey": this.id
        };
        $.post(url, data, fnReload);
      }
    });
  });

  // Enable schedule
  $(document).ready(function() {
    $(".enable-schedule").change(function() {
      var url = "schedule-update.php";
      var data = {
        "action": "enable",
        "scheduleKey": this.id,
        "value": this.checked ? 1 : 0
      };
      $.post(url, data);
    });
  });

  // Enable on-off cycle
  $(".enable-onoffcycle").bootstrapToggle({
    on: "Active",
    off: "Inactive",
    size: "large",
    width: "100%"
  });
  $(document).ready(function() {
    $(".enable-onoffcycle").change(function() {
      var url = "cycle-update.php";
      var data = {
        "action": "enable",
        "value": this.checked ? 1 : 0
      };
      $.post(url, data);
    });
  });

  // Change on duration
  $(document).ready(function() {
    $(".change-oncycletime").change(function() {
      var duration = this[this.selectedIndex].id;
      var url = "cycle-update.php";
      var data = {
        "action": "changeOn",
        "value": duration
      };
      $.post(url, data);
    });
  });

  // Change off duration
  $(document).ready(function() {
    $(".change-offcycletime").change(function() {
      var duration = this[this.selectedIndex].id;
      var url = "cycle-update.php";
      var data = {
        "action": "changeOff",
        "value": duration
      };
      $.post(url, data);
    });
  });

  // Refresh on active
  // var blurred = false;
  // window.onblur = function() { blurred = true; };
  // window.onfocus = function() { blurred && (location.reload()); };
</script>