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
$iplogger->iplog("add-players");

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
    $redirect = false;
    // 1
    if (isset($req['add_kgsname_1']) && isset($req['add_to_division_1'])) {
        $k = $req['add_kgsname_1'];
        $d = $req['add_to_division_1'];
        if ((strlen($k) > 0) && (strlen($d) > 0)) {
            $c = DB::queryFirstRow('SELECT kgsname FROM participants WHERE kgsname=%s AND eid=%i;', $k,$event_id);
            if (count($c) == 0) {
                DB::insert('participants', array( 'eid' => $event_id, 'kgsname' => $k, 'division' => $d, 'wp_id' => get_current_user_id() ) );
            } else {
                DB::update('participants', array( 'kgsname' => $k, 'division' => $d ), "eid=%i and kgsname=%s", $event_id, $k);
            }
            $redirect = true;
        }
    } // 1
    
    // 2
    if (isset($req['add_kgsname_2']) && isset($req['add_to_division_2'])) {
        $k = $req['add_kgsname_2'];
        $d = $req['add_to_division_2'];
        if ((strlen($k) > 0) && (strlen($d) > 0)) {
            $c = DB::queryFirstRow('SELECT kgsname FROM participants WHERE kgsname=%s AND eid=%i;', $k,$event_id);
            if (count($c) == 0) {
                DB::insert('participants', array( 'eid' => $event_id, 'kgsname' => $k, 'division' => $d, 'wp_id' => get_current_user_id() ) );
            } else {
                DB::update('participants', array( 'kgsname' => $k, 'division' => $d ), "eid=%i and kgsname=%s", $event_id, $k);
            }
            $redirect = true;
        }
    } // 2
    
    // 3
    if (isset($req['add_kgsname_3']) && isset($req['add_to_division_3'])) {
        $k = $req['add_kgsname_3'];
        $d = $req['add_to_division_3'];
        if ((strlen($k) > 0) && (strlen($d) > 0)) {
            $c = DB::queryFirstRow('SELECT kgsname FROM participants WHERE kgsname=%s AND eid=%i;', $k,$event_id);
            if (count($c) == 0) {
                DB::insert('participants', array( 'eid' => $event_id, 'kgsname' => $k, 'division' => $d, 'wp_id' => get_current_user_id() ) );
            } else {
                DB::update('participants', array( 'kgsname' => $k, 'division' => $d ), "eid=%i and kgsname=%s", $event_id, $k);
            }
        }
        $redirect = true;
    } // 3
    
    // 4
    if (isset($req['add_kgsname_4']) && isset($req['add_to_division_4'])) {
        $k = $req['add_kgsname_4'];
        $d = $req['add_to_division_4'];
        if ((strlen($k) > 0) && (strlen($d) > 0)) {
            $c = DB::queryFirstRow('SELECT kgsname FROM participants WHERE kgsname=%s AND eid=%i;', $k,$event_id);
            if (count($c) == 0) {
                DB::insert('participants', array( 'eid' => $event_id, 'kgsname' => $k, 'division' => $d, 'wp_id' => get_current_user_id() ) );
            } else {
                DB::update('participants', array( 'kgsname' => $k, 'division' => $d ), "eid=%i and kgsname=%s", $event_id, $k);
            }
            $redirect = true;
        }
    } // 4
    
    // 5
    if (isset($req['add_kgsname_5']) && isset($req['add_to_division_5'])) {
        $k = $req['add_kgsname_5'];
        $d = $req['add_to_division_5'];
        if ((strlen($k) > 0) && (strlen($d) > 0)) {
            $c = DB::queryFirstRow('SELECT kgsname FROM participants WHERE kgsname=%s AND eid=%i;', $k,$event_id);
            if (count($c) == 0) {
                DB::insert('participants', array( 'eid' => $event_id, 'kgsname' => $k, 'division' => $d, 'wp_id' => get_current_user_id() ) );
            } else {
                DB::update('participants', array( 'kgsname' => $k, 'division' => $d ), "eid=%i and kgsname=%s", $event_id, $k);
            }
            $redirect = true;
        }
    } // 5
    
    if ($redirect) {
        header("Location: http://www.advancedstudyroom.org/asrleague/events.php");
        die();
    }
} // if action = update

echo bootstrap_head("Advanced Study Room -- Add Players");
?>

  <div class="text-center well" style="background: #efefef; padding-bottom: 32px;">
    <h1>Add Players</h1>
      <p>Here you can add up to five members at a time. Just enter their KGS name and which room you would like them to be in.</p>
    <a class="btn btn-lg btn-success" href="/asrleague/events.php" id="button_back" role="button">Back to Events</a>
    <button class="btn btn-lg btn-warning" onclick="do_submit();">Add Member(s)</button>
    </div> <!-- /text center well -->
    <!-- Main Form -->
    <form class="form-horizontal" id="the_form" method="post">
      <input type="hidden" id="action" name="action" value="update">
      <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>">
      <!-- add player (part a) which is static -->
          <table class="table" id="table-players">
              <thead><th style="text-align: right;">KGS Player Name</th></th><th>Division</th></tr></thead>
                  <tbody>
                      <tr>
                          <td style="text-align: right;"><input type="text" name="add_kgsname_1" autocomplete="off" role="search"></td>
                          <td><input type="text" name="add_to_division_1" autocomplete="off" role="search" value="Placement League"></td>
                      </tr>
                      <tr>
                          <td style="text-align: right;"><input type="text" name="add_kgsname_2" autocomplete="off" role="search"></td>
                          <td><input type="text" name="add_to_division_2" autocomplete="off" role="search" value="Placement League"></td>
                      </tr>
                      <tr>
                          <td style="text-align: right;"><input type="text" name="add_kgsname_3" autocomplete="off" role="search"></td>
                          <td><input type="text" name="add_to_division_3" autocomplete="off" role="search" value="Placement League"></td>
                      </tr>
                      <tr>
                          <td style="text-align: right;"><input type="text" name="add_kgsname_4" autocomplete="off" role="search"></td>
                          <td><input type="text" name="add_to_division_4" autocomplete="off" role="search" value="Placement League"></td>
                      </tr>
                      <tr>
                          <td style="text-align: right;"><input type="text" name="add_kgsname_5" autocomplete="off" role="search"></td>
                          <td><input type="text" name="add_to_division_5" autocomplete="off" role="search" value="Placement League"></td>
                      </tr>
                  </tbody>
              </table>
      <hr style="border-color: DarkGray;">
      <div class="row" style="text-align: center;">
				<a class="btn btn-lg btn-success" href="/asrleague/events.php" id="button_back" role="button">Back</a>
				<button class="btn btn-lg btn-warning" id="button_update" role="submit" onclick="do_submit();">Add</button>
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
