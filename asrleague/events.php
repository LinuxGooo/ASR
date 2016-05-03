<?php
//
// events.php for asr league
// (C) 2015 Akisora Corporation
//
// Admin events page.
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

// Set $can_join and $edit events for context sensitive user & admin buttons.
$can_join = false;
$edit_events = false;
if ( is_user_logged_in() && current_user_can('asr_league_member') ) {
    $can_join = true;
}
if ( is_user_logged_in() && current_user_can('asr_edit_events') ) {
    $edit_events = true;
}

$edata = get_events_data(); // a basic selec * from events.

$t = '';
$t .= '<div class="table-responsive">';
$t .= '<table class="table" id="table-players">';
$t .= '<thead><tr><th>Event #</th><th>Event Name</th><th>Type</th><th>Participants</th><th>Created</th><th>Start Date</th><th>End Date</th><th>Options</th></tr></thead><tbody>';
foreach ($edata as $e) {
    $t .= '<tr>';
    $t .= '<td>' . $e['event_id'] . '</td>';
    $t .= '<td>' . $e['event_name'] . '</td>';
    $t .= '<td>' . $e['event_type'] . '</td>';

    // assign players button / participant count
    $t .= '<td>';
    $p = DB::queryFirstField("SELECT COUNT(*) FROM participants WHERE eid=%i", $e['event_id']);   
    if ($p == 0) { $t .= "(no players)"; }
    if ($p == 1) { $t .= "(one player)"; }
    if ($p > 1)  { $t .= '(' . $p . ' players)'; }
    
    if ( current_user_can('asr_assign_players') ) {
        $t .= '&nbsp;&nbsp;<a class="btn btn-xs btn-warning" href="/asrleague/forms/add-players.php?event_id=' . $e['event_id'] . '" role="button">Add</a>';
    }
    if ( current_user_can('asr_assign_players') ) {
        $t .= '&nbsp;&nbsp;<a class="btn btn-xs btn-warning" href="/asrleague/forms/assign-players.php?event_id=' . $e['event_id'] . '" role="button">Assign</a>';
    }

  $t .= '</td>';
  $t .= '<td>' . $e['date_created'] . '</td>';
  $t .= '<td>' . $e['date_starts'] . '</td>';
  $t .= '<td>' . $e['date_ends'] . '</td>';
  $t .= '<td>';
    // edit event button for admins only:
    if ( current_user_can('asr_edit_events') ) {
        $t .= '<a class="btn btn-xs btn-warning" href="/asrleague/forms/edit-event.php?action=edit&event_id=' . $e['event_id'].'" role="button">Edit</a>';
    }
    // edit room button for admins only:
    if ( current_user_can('asr_edit_rooms') ) {
        $t .= '<a class="btn btn-xs btn-warning" href="/asrleague/rooms.php?&event_id=' . $e['event_id'].'" role="button" style="margin-left: 7px;">Rooms</a>';
    }
    
    // a join button for this event
    // todo: only show this if the event hasn't ended yet.
    if (is_user_logged_in()) {
        $t .= '&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="/asrleague/forms/sign-up.php?event_id=' . $e['event_id'] . '" role="button">Join</a>';
    }
  
  $t .= '</td>'."\n";
  $t .= '</tr>';
}
$t .= '</tbody></table>';
$t .= '</div>';

$primary_event = get_registry("admin-control-panel", "primary_event");
$secondary_event = get_registry("admin-control-panel", "secondary_event");

echo bootstrap_head("Advanced Study Room -- Event Admin");

?>
<body>
    <div class="row">
        <div class="col-md-1">
            <!-- left sidebar -->
    </div> <!-- col-md-1 -->
        <div class="col-md-10">
  <?php echo bootstrap_navbar('events'); ?>

  <!-- Jumbotron -->
  <div class="jumbotron well">
    <h1>Events</h1>
    <p class="lead">Create, read, update and destroy events.</p>
      <p> The primary event number is <a class="btn btn-lg btn-info" href="/asrleague/forms/primary-event.php" role="button">== <?php echo $primary_event; ?> ==</a></p>
    <?php if ( current_user_can('asr_edit_events') ) { echo '<p><a class="btn btn-lg btn-warning" href="/asrleague/forms/edit-event.php?action=add" role="button">Add Event</a></p>'; } ?>
  </div> <!-- /jumbotron -->
  <h2>Events</h2>
  <?php echo $t; ?>

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
    $(document).ready(function() {
      // Start
      $('#table-players').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     true,
        "order": [[ 6, "desc" ]] /* this puts the most recent ending date first so we can see what events are active.*/
      });
    });
  </script>
</body>
</html>