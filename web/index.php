<?php
  require "header.php";
  require "data.php";
?>

<div class="container">

  <h5>
    Heater is
    <div class="btn-group">
      <a href="#" class="btn btn-primary">ON</a>
      <a href="#" class="btn btn-default">OFF</a>
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
    $(".btn-xl").click(function() {
      var url = "write_to_database.php";
      var action = $(this).val();
      data = {"action": action};
      $.post(url, data, function(){
        location.reload();
      });
    });
  });
  // reload on page active
</script>