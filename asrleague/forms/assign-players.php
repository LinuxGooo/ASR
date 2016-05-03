<?php
//
// assign-players.php
// (C) 2015 Akisora Corporation
//
// Edit player division assignments within an event.
// Also let admins add new players to this event.
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/IPLog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';

// 1. Command processing. similar to action.php
//
// log ip of anyone doing a search (will be repladed with a usage pattern monotir later on)
$iplogger = new IPlog();
$iplogger->iplog("assign-players");

// default values
$req = array_merge($_GET, $_POST);
$action = "";
$value = "";
$event_id = 0;
$msg = "";

// load proper values from $req
if (isset($req['action'])) {
	$action = htmlentities(substr($req['action'], 0, 256));
}

if (isset($req['event_id'])) {	$event_id = htmlentities($req['event_id']); }

// Are we saving player data?
////////////////////////////////////////////////////////////////////////////////
if (strcmp($action, "update") == 0)
{
  if (isset($req['division'])) {	$division = $req['division']; }
	foreach ($division as $k => $v) {
		if (strlen($v) > 0) {
			DB::update('participants', array( 'division' => $v ), "eid=%i and kgsname=%s", $event_id, $k);
		}
	}
	$msg = count($division) . " records saved.";
}

// Fallthru: load and display updated (or initial) data.

// Step 1, get the event data.
$e = DB::queryFirstRow("SELECT * FROM events WHERE event_id=%i",$event_id);

// Step 2, get list of participants in this event.
$p = DB::query("SELECT * FROM participants WHERE eid=%i", $event_id);

// Construct the table...
$t  = '<div="table-responsive">'."\n";
$t .= '  <table class="table" id="table-players">'."\n";
$t .= '    <thead><tr><th>KGS Player Name</th></th><th>Division</th><th>Move to</th><th>Options</th></tr></thead>'."\n";
$t .= '    <tbody>'."\n";
foreach($p as $prow) {
    $t .= '<tr>';
	$t .= '<td>' . $prow['kgsname'] . '</td>'."\n";
	$t .= '<td>' . $prow['division'] . '</td>'."\n";
	$t .= '<td><input type="text" name="division[' . $prow['kgsname'] . ']" autocomplete="off" role="search"></td>'."\n";
	$t .= '<td>' . '</td>'."\n";
	$t .= '</tr>';
}
$t .= '    </tbody>'."\n";
$t .= '  </table>'."\n";
$t .= '</div>'."\n";

echo bootstrap_head("Advanced Study Room -- Assign Players");
?>

  <div class="text-center well" style="background: #efefef; padding-bottom: 32px;">
    <h1>Assign Players</h1>
    <a class="btn btn-lg btn-success" href="/asrleague/events.php" id="button_back" role="button">Back to Events</a>
    <button class="btn btn-lg btn-warning" onclick="do_submit();">Update</button>
    </div> <!-- /text center well -->
    <!-- Main Form -->
    <form class="form-horizontal" id="the_form" method="post">
      <input type="hidden" id="action" name="action" value="update">
      <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>">
      <?php echo $t; ?>
      <hr style="border-color: DarkGray;">
      <div class="row" style="text-align: center;">
				<a class="btn btn-lg btn-success" href="/asrleague/events.php" id="button_back" role="button">Back</a>
				<button class="btn btn-lg btn-warning" id="button_update" role="submit">Update</button>
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