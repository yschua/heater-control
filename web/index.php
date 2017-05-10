<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  <?php
    printf("current_power: %d<br>", GetCurrentPower());
    printf("selected_power: %d<br>", GetSelectedPower());
    printf("current_temperature: %.1f<br>", GetCurrentTemperature());
    printf("selected_temperature: %.1f<br>", GetSelectedTemperature());
  ?>

  <h5>
    Heater is
    <div class="btn-group">
      <a href="#p-1" class="btn btn-primary btn-input">ON</a>
      <a href="#p-0" class="btn btn-default btn-input">OFF</a>
    </div>
  </h5>

  <h5>
    <div class="dropdown">
      Thermostat set at
      <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
        <?php $tempCurr = 12.5; echo $tempCurr; ?> &deg;C <span class="caret"></span>
      </button>

      <ul class="dropdown-menu">
        <?php
          for ($temperature = 10.0; $temperature <= 20.0; $temperature += 0.5) {
            printf(
              "<li><a href=\"#t-%.1f\" class=\"btn-input\">%.1f</a></li>",
              $temperature,
              $temperature
            );
          }
        ?>
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