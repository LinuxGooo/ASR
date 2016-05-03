<?php
//
// sign-up.php
// (C) 2015 Akisora Corporation
//
// Allow self-sign-up for WordPress registered users.
// Record WordPress ID to track possible abuses.
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/IPLog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';

//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/schema.php';
//drop_table_participants();
//create_table_participants();

// 1. Command processing. similar to action.php
//
// log ip of anyone doing a search (will be repladed with a usage pattern monotir later on)
$iplogger = new IPlog();
$iplogger->iplog("self-signup");

// default values
$req = array_merge($_GET, $_POST);
$msg = "";

// load proper values from $req
$action = "";
if (isset($req['action'])) {
	$action = htmlentities(substr($req['action'], 0, 256));
}

$event_id = 0;
if (isset($req['event_id'])) {	$event_id = htmlentities($req['event_id']); }
if ($event_id == 0) {
    $event_id = get_registry("admin-control-panel", "primary_event");
}

// Are we saving player data?
////////////////////////////////////////////////////////////////////////////////
// Note the check for user logged in...
if ((strcmp($action, "do-signup") == 0) && is_user_logged_in())
{
    // 1
    if ( isset($req['add_kgsname']) ) {
        $k = $req['add_kgsname'];
        if ((strlen($k) > 0)) {
            $c = DB::query('SELECT * FROM participants WHERE kgsname=%s;', $k);
            if (count($c) == 0) {
                DB::insert('participants',
                           array('eid'      => $event_id,
                                 'kgsname'  => $k,
                                 'division' => 'unassigned',
                                 'wp_id'    => get_current_user_id()
                            ));
                
                header("Location: http://www.advancedstudyroom.org/asrleague/events.php");
                die();
            } else {
                // do nothing, they already joined with that name.
            }
        }
    } // 1

} // if action = do-signup

$current_user = wp_get_current_user();
$display_name = $current_user->display_name;
if (strlen($display_name) == 0) {
    //$display_name = $current_user->user_login;
}

echo bootstrap_head("Advanced Study Room -- League Sign-Up");
?>

  <div class="text-center well" style="background: #efefef; padding-bottom: 32px;">
    <h1>Join Event #<?php echo $event_id; ?></h1>
      <p>Join event number <?php echo $event_id; ?> today! Enter your KGS name and click the big green button to join, or the tiny unimportant blue button to cancel.</p>
    </div> <!-- /text center well -->
    <!-- Main Form -->
    <form class="form-horizontal" id="the_form" method="post">
      <input type="hidden" id="action" name="action" value="do-signup">
      <input type="hidden" id="event_id" name="event_id" value="<?php echo $event_id; ?>">
      <!-- add player (part a) which is static -->
          <table class="table" id="table-players">
              <thead><th style="text-align: center; font-size: 200%;">KGS Player Name</th></th></tr></thead>
                  <tbody>
                      <tr>
                          <td style="text-align: center; font-size: 200%;"><input type="text" name="add_kgsname" autocomplete="off" role="search" value = "<?php echo $display_name; ?>"></td>
                      </tr>
                  </tbody>
              </table>
      <hr style="border-color: DarkGray;">
      <div class="row" style="text-align: center;">
				<button class="btn btn-lg btn-success" id="button_update" role="submit">Join Now!</button>
                <a class="btn btn-lg btn-info" href="/asrleague/events.php" id="button_back" role="button">Abandon Rat</a>
      </div>
    </form>
  <div style="min-height: 200px;"><!-- give the login form some room to scroll up past the keyboard on mobile devices. --></div>

  <?php echo bootstrap_core_js(); ?>

  <!-- inline script for local page -->
  <script>
    function do_submit() {
      document.getElementById("the_form").submit();
    }
  </script>

</body>
</html>