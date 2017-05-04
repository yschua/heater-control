<?php
  require "header.php";
  require "db.php";
?>

<div class="container">

  Current power is <?php echo GetCurrentPower() ? 'on' : 'off'; ?>

  <h5>
    Heater is
    <div class="btn-group">
      <a href="#power-on" class="btn btn-primary btn-power">ON</a>
      <a href="#power-off" class="btn btn-default btn-power">OFF</a>
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
          for ($temp = 10.0; $temp <= 20.0; $temp += 0.5) {
            $str = ($temp == $tempCurr) ? '<li class="active">' : '<li>';
            $str .= '<a href="#">';
            $str .= number_format($temp, 1);
            $str .= '</a></li>';
            echo $str;
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
    $(".btn-power").click(function() {
      var url = "db_writer.php";
      var action = $(this).attr("href");
      var data = { "action": action };
      var fnReload = function() { location.reload(); };

      $.post(url, data, fnReload);
    });
  });

  // TODO reload on page active
</script>