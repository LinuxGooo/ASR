<?php
//
// index.php for asr league
// (C) 2016 Akisora Corporation
//
// A welcome/basic info page which links back to wordpress.
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors ', TRUE);
ini_set('display_startup_errors ', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/leaguelib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/debug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/schema.php';

echo bootstrap_head("Advanced Study Room -- Event Admin");

?>
<body>
    <div class="row">
        <div class="col-md-1">
            <!-- left sidebar -->
    </div> <!-- col-md-1 -->
        <div class="col-md-10">
  <?php echo bootstrap_navbar('home'); ?>

  <!-- Jumbotron -->
  <div class="jumbotron well">
    <h1>ASR League</h1>
    <p class="lead">ASR League Software</p>
    <p><a class="btn btn-lg btn-success" href="http://www.advancedstudyroom.org" role="button">Back to WordPress</a></p>
  </div> <!-- /jumbotron -->

  <h2>News</h2>
            <p>Welcome to the Advanced Study Room 2016 WordPress edition.</p>

  <!-- Site footer -->
  <footer class="footer">
    <hr>
    <p>&copy; 2016 advancedstudyroom.org</p>
  </footer>

        </div> <!-- col-md-10 -->
    <div class="col-md-1">
    </div> <!-- col-md-1 -->
    </div> <!-- row-->
  <?php echo bootstrap_core_js(); ?>
        
  <!-- inline script for local page -->
  <script>
  </script>
</body>
</html>