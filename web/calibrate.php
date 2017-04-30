<?php
  include "header.php"
?>

<div class="container" role="main">

  <div class="col-xs-7 display">
    <div id="temperature">10.0&deg;</div>
    <div id="power">ON</div>
  </div>

  <div class="col-xs-5">
    <div class="btn-group-vertical">
      <button type="button" class="btn btn-default btn-xl " value="increase">
        <span class="glyphicon glyphicon-triangle-top"></span>
      </button>
      <button type="button" class="btn btn-default btn-xl " value="decrease">
        <span class="glyphicon glyphicon-triangle-bottom"></span>
      </button>
      <button type="button" class="btn btn-default btn-xl " value="power">
        <span class="glyphicon glyphicon-off"></span>
      </button>
      <button type="button" class="btn btn-primary btn-calibrate" value="calibrate">
        Calibrate
      </button>
    </div>
  </div>

  
</div> <!-- /container -->

<?php
  include "footer.php"
?>