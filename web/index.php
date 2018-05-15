<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  <!-- Power control -->
  <div class="control-item">
    <div class="control-label">Power</div>
    <div class="btn-group control-input">
      <a href="#p-1" class="btn btn-lg btn-input col-xs-6 btn-<?php echo $db->GetSelectedPower() ? "primary active" : "default";?>">On</a>
      <a href="#p-0" class="btn btn-lg btn-input col-xs-6 btn-<?php echo !$db->GetSelectedPower() ? "danger active" : "default";?>">Off</a>
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
      <button class="btn btn-lg btn-block btn-default dropdown-toggle" type="button" data-toggle="dropdown" <?php if (!$db->GetSelectedPower()) { echo "disabled"; }?>>
        <?php echo $db->GetTimeoutStr(); ?> <span class="caret" />
      </button>
      <ul class="dropdown-menu">
        <li><a href="#o-0" class="btn-input">Off</a></li>
        <li><a href="#o-15" class="btn-input">15 min</a></li>
        <li><a href="#o-30" class="btn-input">30 min</a></li>
        <li><a href="#o-45" class="btn-input">45 min</a></li>
        <li><a href="#o-60" class="btn-input">60 min</a></li>
        <li><a href="#o-120" class="btn-input">120 min</a></li>
      </ul>
    </div>
  </div>

  <hr>

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
          printf(
            "<tr>
              <td><input class=\"enable-schedule\" id=\"%s\" type=\"checkbox\" checked data-toggle=\"toggle\" data-size=\"mini\"></td>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td><button class=\"btn btn-danger delete-schedule\" id=\"%s\" type=\"button\" title=\"Remove\">&times;</button></td>
             </tr>",
            $schedule["schedule_id"],
            $db->GetDayArray()[$schedule["day_id"]],
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
              while ($name = current($days)) {
                $key = key($days);
                next($days);
                printf("<option id=\"%d\">%s</option>", $key, $name);
              }
            ?>
          </select>
        </td>
        <td>
          <div class="input-group clockpicker">
            <input type="text" class="form-control" value="09:30" id="scheduleStart" readonly>
            <span class="input-group-addon no-indent"><span class="glyphicon glyphicon-time"></span></span>
          </div>
        </td>
        <td>
          <div class="input-group clockpicker">
            <input type="text" class="form-control" value="10:00" id="scheduleEnd" readonly>
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

<!-- TODO reload on page active -->
<script type="text/javascript" src="bootstrap/js/bootstrap-clockpicker.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap-toggle.min.js"></script>
<script type="text/javascript">
  // Clockpicker
  $(".clockpicker").clockpicker({
    autoclose: true
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

      var url = "schedule-update.php";
      var data = {
        "action": "add",
        "dayKey": dayKey,
        "start": start,
        "end": end
      };
      $.post(url, data, fnReload);
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
</script>