<?php
//
// rooms.php for tsumego.ca league
// (C) 2015 Akisora Corporation
//
// made to work with wordpress version april 2016
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors ', TRUE);
ini_set('display_startup_errors ', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/IPLog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/leaguelib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/schema.php';
//drop_table_rooms();
//create_table_rooms();

// log ip of anyone using command scripts.
$iplogger = new IPlog();
$iplogger->iplog("rooms");

if ( current_user_can ('asr_edit_rooms') == false) {
  die("Please log in to a League Admin account to edit rooms.");
}

// Edit rooms for which event ID?
$req = array_merge($_GET, $_POST);
$event_id = 0;
if (isset($req['event_id'])) {	$event_id = $req['event_id']; }

if ($event_id == 0) {
  die("Please call this script with \"?event_it=#\" so it knows which event to edit rooms for.");
}

$rooms = DB::query("SELECT * FROM rooms WHERE event_id=%i;", $event_id);

$t = '';
$t .= '<div class="table-responsive">';
$t .= '<table class="table" id="table-players">';
$t .= '<thead><tr><th>eid</th><th>rid</th><th>Class</th><th>Room</th><th>Sort Order</th><th>Promotes To</th><th>Demotes To</th><th>Options</th></tr></thead><tbody>';
foreach ($rooms as $r) {
  $t .= '<tr>';
  $t .= '<td>' . $r['event_id'] . '</td>';
  $t .= '<td>' . $r['room_id'] . '</td>';
  $t .= '<td>' . $r['class_name'] . '</td>';
  $t .= '<td>' . $r['room_name'] . '</td>';
  $t .= '<td>' . $r['sort_order'] . '</td>';
  $t .= '<td>' . $r['promotes_to'] . '</td>';
  $t .= '<td>' . $r['demotes_to'] . '</td>';
  $t .= '<td><a class="btn btn-xs btn-warning" href="/asrleague/forms/edit-room.php?action=edit&room_id=' . $r['room_id'].'" role="button">Edit</a></td>'."\n";
  $t .= '</tr>';
}
$t .= '</tbody></table>';
$t .= '</div>';

echo bootstrap_head("Advanced Study Room -- Event Admin (Rooms)");
?>
<body>
    <div class="row">
        <div class="col-md-1">
        </div>
        <div class="col-md-10">
  <?php echo bootstrap_navbar('events'); ?>
  
        <!-- Jumbotron -->
        <div class="jumbotron well">
          <h1>Event Admin (Rooms)</h1>
          <p class="lead">Create, read, update and destroy rooms attached to an event..</p>
          <p><a class="btn btn-lg btn-warning" href="/asrleague/forms/edit-room.php?action=add&event_id=<?php echo $event_id; ?>" role="button">Add Room</a>
            <a class="btn btn-lg btn-success" href="/asrleague/events.php" role="button">Return to Events</a></p>
        </div> <!-- /jumbotron -->

        <h2>Rooms for Event <?php echo $event_id; ?></h2>
        <?php echo $t; ?>

        <!-- Site footer -->
        <footer class="footer">
          <hr>
          <p>&copy; 2015 tsumego.ca</p>
        </footer>
        </div>
        <div class="col-md-1">

      </div> <!-- /col-md-1 -->
    </div><!-- /row -->

  <?php echo bootstrap_core_js(); ?>
        
  <!-- inline script for local page -->
  <script>
    $(document).ready(function() {
      // Start
      $('#table-players').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "order": [[ 4, "asc" ]]
      });
    });

  </script>
</body>
</html>