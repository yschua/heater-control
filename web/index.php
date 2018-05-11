<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  <div class="control-item">
    <div class="control-label">Power</div>
    <div class="btn-group control-input">
      <a href="#p-1" class="btn btn-input btn-<?php echo GetSelectedPower() ? "primary" : "default";?>">ON</a>
      <a href="#p-0" class="btn btn-input btn-<?php echo !GetSelectedPower() ? "danger" : "default";?>">OFF</a>
    </div>
  </div>

  <div class="control-item">
    <div class="dropdown">
      <div class="control-label">Temperature</div>
      <button class="btn btn-primary dropdown-toggle control-input" type="button" data-toggle="dropdown">
        <?php echo GetSelectedTemperature();?> &deg;C <span class="caret"></span>
      </button>

      <ul class="dropdown-menu">
        <?php
          for ($temp = 10.0; $temp <= 20.0; $temp += 0.5) {
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

  <div class="control-item">
    <div class="dropdown">
      <div class="control-label">Timeout</div>
      <button class="btn btn-primary dropdown-toggle control-input" type="button" data-toggle="dropdown" <?php if (!GetSelectedPower()) { echo "disabled"; }?>>
        <?php echo GetTimeoutStr(); ?> <span class="caret"></span>
      </button>

      <ul class="dropdown-menu">
        <li><a href="#o-0" class="btn-input">OFF</a></li>
        <li><a href="#o-30" class="btn-input">30 min</a></li>
        <li><a href="#o-60" class="btn-input">60 min</a></li>
        <li><a href="#o-120" class="btn-input">120 min</a></li>
        <li><a href="#o-180" class="btn-input">180 min</a></li>
      </ul>
    </div>
  </div>

  <hr>

  <table class="table">
    <thead>
      <tr>
        <th class="col-xs-4">Schedule</th>
        <th>Start</th>
        <th>End</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Daily</td>
        <td>10:00</td>
        <td>11:20</td>
        <td><button class="btn btn-danger" type="button">&times;</button></td>
      </tr>
      <tr>
        <td>
          <select class="form-control">
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
            <input type="text" class="form-control" value="09:30">
            <span class="input-group-addon">
              <span class="glyphicon glyphicon-time"></span>
            </span>
          </div>
        </td>
        <td>
          <div class="input-group clockpicker">
            <input type="text" class="form-control" value="09:30">
            <span class="input-group-addon">
              <span class="glyphicon glyphicon-time"></span>
            </span>
          </div>
        </td>
        <td><button class="btn btn-primary" type="button">&plus;</button></td>
      </tr>
    </tbody>
  </table>

</div>

<?php
  require "footer.php";
?>

<script>
  $(document).ready(function() {
    $(".btn-input").click(function() {
      var url = "updatedb.php";
      var message = $(this).attr("href");
      var data = { "message": message };
      var fnReload = function() { location.reload(); };

      $.post(url, data, fnReload);
    });
  });
</script>
<!-- TODO reload on page active -->
<script type="text/javascript" src="bootstrap/js/bootstrap-clockpicker.min.js"></script>
<script type="text/javascript">
  $(".clockpicker").clockpicker({
    autoclose: true
  });
</script>
