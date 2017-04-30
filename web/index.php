<?php
  require "header.php";
  require "data.php";
?>

<div class="container" role="main">

  <div class="col-xs-7 display">
    <!-- <div class="glyphicon glyphicon-fire power" <?php setHidden(); ?>></div> -->
    <div id="temperature"><?php print $row->temperature; ?>&deg;</div>
    <div id="power"><?php showPower(); ?></div>
  </div>

  <div class="col-xs-5">
    <div class="btn-group-vertical">
      <button type="button" class="btn btn-xl btn-default" value="increase" <?php disableButton(); ?>>
        <span class="glyphicon glyphicon-triangle-top"></span>
      </button>
      <button type="button" class="btn btn-xl btn-default" value="decrease" <?php disableButton(); ?>>
        <span class="glyphicon glyphicon-triangle-bottom"></span>
      </button>
      <button type="button" class="btn btn-xl btn-default" value="power">
        <span class="glyphicon glyphicon-off"></span>
      </button>
    </div>
  </div>

</div> <!-- /container -->

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