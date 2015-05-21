<?php
/**
 * Created using PhpStorm.
 * User: shad
 * Date: 4/15/15
 * Time: 10:17 AM
 * File Name: header.php
 * Project: starter-app
 */
// Are we in development mode?
$isDevMode = true;

if ($isDevMode) {

//get the relative path of the project.
    $root = $_SERVER['DOCUMENT_ROOT'];
    $directory =  __DIR__;
    $project_path = str_replace('php_includes', 'public_html' ,str_replace($root, '', $directory));
    define('WEB_ROOT', $project_path);

} else {

    define('WEB_ROOT', '' );

}


?>
<!DOCTYPE html>
<html>
<head>
<title>Las Vegas PHP Users Group - LVPHP.org</title>
<meta charset="UTF-8">
<meta name="description" content="Las Vegas PHP Users Group is a community of PHP developers looking to share and learn. All events are free to attend and can be found here.">
<meta name="keywords" content="Las Vegas PHP Users Group">
<meta name="author" content="The LVPHPUG Community">
<!-- JS -->
<script src="//www.google.com/recaptcha/api.js"></script>
<!-- JQUERY UI CSS -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css">
<!-- Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="../public_html/css/bootstrap.min.css">
<link rel="stylesheet" href="../public_html/css/bootstrap-theme.min.css">
<link rel="stylesheet" href="../public_html/css/lvphp_custom.css">
<link rel="shortcut icon" type="image/x-icon" href="../public_html/img/favicon.ico"/>
</head>
<body>


<div class="container" id="page_container">
<a href="https://github.com/lv-php/starter-app"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png"></a>
<!--NavBar Start     -->
<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>

      </button>
      <a class="navbar-brand" href="/#">LVPHP.org</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="<?php echo WEB_ROOT;?>/#about">About Us</a></li>
        <li><a href="<?php echo WEB_ROOT;?>/#meetup">Meetups</a></li>
         <li><a href="<?php echo WEB_ROOT;?>/#topic_picker">Upcoming Topics</a></li>
          <li><a href="<?php echo WEB_ROOT;?>/#sponsors">Sponsors</a></li>
          <li><a href="<?php echo WEB_ROOT;?>/meetings.php">Meetings</a></li>
        <!--<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li class="divider"></li>
            <li><a href="#">One more separated link</a></li>
          </ul>
        </li>-->
      </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<!--NavBar End -->

<!-- Header Begin -->
<div id="top_header" class="document-header">
<div>
    <img src="img/LVPHP-logo.png" alt="PHP Logo">
    <h1>Las Vegas PHP User Group - LVPHP.org</h1>
</div>
</div>
<!-- Header End -->
