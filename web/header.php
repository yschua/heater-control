<?php
  function setNavBarTab($name, $page) {
    $currPage = ltrim($_SERVER["PHP_SELF"], "/");
    $str = ($page == $currPage) ? '<li class="active">' : '<li>';
    $str .= '<a href="'.$page.'">';
    $str .= $name;
    $str .= '</a></li>';
    echo $str;
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">

    <title>Heater Control</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css" >
    <!-- Custom styles for this template -->
    <link href="css/theme.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
  </head>

  <body role="document">
    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand">Heater Control</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <?php
              setNavBarTab("Home", "index.php");
              setNavBarTab("Calibrate", "calibrate.php");
              setNavBarTab("Timer", "timer.php");
              setNavBarTab("Log", "log.php");
            ?>
          </ul>
        </div>
      </div>
    </nav>
