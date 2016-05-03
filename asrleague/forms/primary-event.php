<?php
//
// admin.php for tsumego.ca
// (C) 2015 Akisora.ca
//
// Administrator's Dashboard.
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors ', TRUE);
ini_set('display_startup_errors ', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php'; // for create table.

// If the user is not logged in, redirect to index.
if (current_user_can('asr_league_admin') == FALSE) {
    die("Please log in as an ASR League Admin before trying to change the primary event number.");
}

// get values from post/get
$req = array_merge($_GET, $_POST);
$action = "";
$value = "";

// uncomperess iofeo s[ecoproper alues from $req
if (isset($req['action'])) {
	$action = htmlentities(substr($req['action'], 0, 256));
}

if (isset($req['value'])) {
	$value = htmlentities(substr($req['value'], 0, 256));
}

if ((strcmp($action, "update") == 0)) {
    $primary_event = "";
    $secondary_event = "";

    if (isset($req['primary_event'])) {
        $primary_event = substr($req['primary_event'], 0, 64);
        set_registry("admin-control-panel", "primary_event", $primary_event);
    }
    if (isset($req['secondary_event'])) {
        $secondary_event = substr($req['secondary_event'], 0, 64);
        set_registry("admin-control-panel", "secondary_event", $secondary_event);
    }
    
    header("Location: http://www.advancedstudyroom.org/asrleague/events.php");
    die();
}

$primary_event = get_registry("admin-control-panel", "primary_event");
$secondary_event = get_registry("admin-control-panel", "secondary_event");

echo bootstrap_head("Advanced Study Room -- Primary and Secondary Event Control");
?>
 <div class="text-center well" style="background: #efefef; padding-bottom: 32px;">
    <h1>League Options</h1>
    <a class="btn btn-lg btn-success" href="/asrleague/events.php" id="button_back" role="button">Back to Events</a>
     <button class="btn btn-lg btn-warning" onclick="do_submit();">Update</button>
 </div> <!-- /text center well -->
        <form id="the_form" class="form form-horizontal well" action="/asrleague/forms/primary-event.php" method="post">
          <input type="hidden" name="action" value="update">
          <div class="form-group">
            <label for="primary_event" class="col-sm-2 control-label">Primary Event No.</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="primary_event" placeholder="Primary Event No. (ex. 9)" value="<?php echo $primary_event; ?>" autocomplete="off">
              <p class="help-block">This will be considered the "current event" when looking at major pages such as "current results".</p>
            </div>
          </div>
          <div class="form-group">
            <label for="secondary_event" class="col-sm-2 control-label">Secondary Event No.</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="secondary_event" placeholder="Secondary Event No." value="<?php echo $secondary_event; ?>" autocomplete="off">
              <p class="help-block">Secondary event promotion ex. championship playoff matches, superleague, or special/one-off tournaments.</p>
            </div>
          </div>
          <hr style="border-color: DarkGray;">
          <div style="text-align: center;"?>
            <button class="btn btn-lg btn-warning" type="submit" role="submit">Update Info</button>
          </div>
        </form>
 

  <div style="min-height: 200px;"><!-- give the login form some room to scroll up past the keyboard on mobile devices. --></div>

  <?php echo bootstrap_core_js(); ?>

  <!-- inline script for local page -->
  <script>
    $(document).ready(function() {
      // Start
      $('#table-players').DataTable( {
        "paging":   true,
		"iDisplayLength": 100,
        "ordering": true,
        "info":     true,
        "order": [[ 3, "asc" ]] /* this puts the most recent ending date first so we can see what events are active.*/
      });
    });
    
    function do_submit() {
      document.getElementById("the_form").submit();
    }
  </script>
</body>
</html>