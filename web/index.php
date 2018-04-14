<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  <h5>
    Heater
    <div class="btn-group">
      <a href="#p-1" class="btn btn-input btn-<?php echo GetSelectedPower() ? "primary" : "default";?>">ON</a>
      <a href="#p-0" class="btn btn-input btn-<?php echo !GetSelectedPower() ? "primary" : "default";?>">OFF</a>
    </div>
  </h5>

  <h5>
    <div class="dropdown">
      Thermostat
      <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
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
  </h5>

  <h5>
    <div class="dropdown">
      Timeout <?php if (GetTimeoutStr() != "OFF") { echo "at "; } ?>
      <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" <?php if (!GetSelectedPower()) { echo "disabled"; }?>>
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
  </h5>

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

  // TODO reload on page active
</script>