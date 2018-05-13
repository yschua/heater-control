<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  <!-- Power control -->
  <div class="control-item">
    <div class="control-label">Power</div>
    <div class="btn-group control-input">
      <a href="#p-1" class="btn btn-lg btn-input col-xs-6 btn-<?php echo GetSelectedPower() ? "primary" : "default";?>">ON</a>
      <a href="#p-0" class="btn btn-lg btn-input col-xs-6 btn-<?php echo !GetSelectedPower() ? "danger" : "default";?>">OFF</a>
    </div>
  </div>

  <!-- Temperature control -->
  <div class="control-item">
    <div class="control-label">Temperature</div>
    <div class="control-input dropdown">
      <button class="btn btn-lg btn-default dropdown-toggle" type="button" data-toggle="dropdown">
        <span class="pull-left"><span class="caret"></span> <?php echo GetSelectedTemperature();?>&deg;C</span>
      </button>
      <ul class="dropdown-menu">
        <?php
          for ($temp = 15.0; $temp <= 20.0; $temp += 0.5) {
            printf(
              "<li><a href=\"#t-%.1f\" class=\"btn-input%s\">%.1f</a></li>\n",
              $temp,
              (GetSelectedTemperature() == $temp) ? " btn-default" : "",
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
      <button class="btn btn-lg btn-default dropdown-toggle" type="button" data-toggle="dropdown" <?php if (!GetSelectedPower()) { echo "disabled"; }?>>
        <span class="pull-left"><span class="caret"></span> <?php echo GetTimeoutStr(); ?></span></span>
      </button>
      <ul class="dropdown-menu">
        <li><a href="#o-0" class="btn-input">OFF</a></li>
        <li><a href="#o-15" class="btn-input">15 min</a></li>
        <li><a href="#o-30" class="btn-input">30 min</a></li>
        <li><a href="#o-45" class="btn-input">45 min</a></li>
        <li><a href="#o-60" class="btn-input">60 min</a></li>
        <li><a href="#o-120" class="btn-input">120 min</a></li>
      </ul>
    </div>
  </div>

  <!-- Schedule control -->
  <div class="control-item">
    <div class="control-label">Schedule</div>
    <div class="btn-group control-input">
      <a href="#s-1" class="btn btn-lg col-xs-6 btn-input btn-<?php echo GetScheduleEnable() ? "primary" : "default";?>">ON</a>
      <a href="#s-0" class="btn btn-lg col-xs-6 btn-input btn-<?php echo !GetScheduleEnable() ? "danger" : "default";?>">OFF</a>
    </div>
  </div>

  <hr>

  <table class="table table-schedule">
    <thead>
      <tr>
        <th class="col-xs-4">Days</th>
        <th>Start</th>
        <th>End</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <!-- List schedules -->
      <?php
        foreach (GetSchedules() as $schedule) {
          printf(
            "<tr>
              <td>%s</td>
              <td>%s</td>
              <td>%s</td>
              <td><button class=\"btn btn-danger delete-schedule\" id=\"%s\" type=\"button\" title=\"Remove\">&times;</button></td>
             </tr>",
            $schedule["days"],
            $schedule["start_time"],
            $schedule["end_time"],
            $schedule["schedule_id"]
          );
        }
      ?>
      <!-- Insert schedule -->
      <tr>
        <td>
          <select class="form-control" id="scheduleDays">
            <option>Daily</option>
            <option>Weekdays</option>
            <option>Weekend</option>
            <option>Monday</option>
            <option>Tuesday</option>
            <option>Wednesday</option>
            <option>Thursday</option>
            <option>Friday</option>
            <option>Saturday</option>
            <option>Sunday</option>
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
      var url = "updatedb.php";
      var message = $(this).attr("href");
      var data = { "message": message };
      $.post(url, data, fnReload);
    });
  });

  // Insert schedule
  $(document).ready(function() {
    $(".add-schedule").click(function() {
      var daysElem = document.getElementById("scheduleDays");
      var days = daysElem[daysElem.selectedIndex].value;
      var start = document.getElementById("scheduleStart").value;
      var end = document.getElementById("scheduleEnd").value;

      if (start >= end) {
        alert("Invalid times.");
        return;
      }

      var url = "schedule-update.php";
      var data = {
        "action": "add",
        "days": days,
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
          "key": this.id
        };
        $.post(url, data, fnReload);
      }
    });
  });
</script>