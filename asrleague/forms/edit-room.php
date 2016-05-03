<?php
//
// edit-room.php
// (C) 2015 Akisora Corporation
//
// Edit a room which is attached to an event.
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
$iplogger->iplog("edit-room");

/// If the user is not logged in and/or an admin, redirect to member's dashboard
if ( current_user_can('asr_edit_rooms') == false ) {
  die("Please log in using a League Administrator account before trying to edit rooms and events.");
}

// default values
$req = array_merge($_GET, $_POST);
$action = "";
$r = array();
$r['event_id'] = 0;
$r['room_id'] = "";
$r['class_name'] = "";
$r['room_name'] = "";
$r['sort_order'] = "";
$r['promotes_to'] = "";
$r['demotes_to'] = "";
if (isset($req['event_id'])) {	$r['event_id'] = $req['event_id']; }
if (isset($req['room_id'])) {	$r['room_id'] = $req['room_id']; }
if (isset($req['class_name'])) {	$r['class_name'] = $req['class_name']; }
if (isset($req['room_name'])) {	$r['room_name'] = $req['room_name']; }
if (isset($req['sort_order'])) {	$r['sort_order'] = $req['sort_order']; }
if (isset($req['promotes_to'])) {	$r['promotes_to'] = $req['promotes_to']; }
if (isset($req['demotes_to'])) {	$r['demotes_to'] = $req['demotes_to']; }


// load proper values from $req
if (isset($req['action'])) {
	$action = htmlentities(substr($req['action'], 0, 256));
}

////////////////////////////////////////////////////////////////////////////////
// Edit an event? i.e. should we pre-load an event?
////////////////////////////////////////////////////////////////////////////////
if ((strcmp($action, "edit") == 0))
{
  $r = DB::queryFirstRow("SELECT * FROM rooms WHERE room_id=%i", $r['room_id']);
}

if (strcmp($action, "saveasnew") == 0) {
  $r['room_id'] = 0;
  unset($r['room_id']);
  DB::insert("rooms", $r);
  die();
}
    
if (strcmp($action, "update") == 0) {
  DB::update("rooms", $r, "room_id=%i", $r['room_id']);
  die();
}

if (strcmp($action, "delete") == 0) {
  $r = DB::query("DELETE FROM rooms WHERE room_id=%i;", $r['room_id']);
  die();
}

// Fallthru: form is loaded, or default action is new (blank) event.
echo bootstrap_head("Advanced Study Room -- Edit Room Form");
?>

  <div class="text-center well" style="background: #efefef; padding-bottom: 32px;">
    <h1>Edit Room<?php if ($r['room_id'] > 0) echo ' ' . $r['room_id']; ?> for Event <?php echo $r['event_id']; ?></h1>
    <hr style="border-color: DarkGray;">
    <!-- Main Form -->
    <form class="form-horizontal" id="nosubmit">
      <input type="hidden" id="event_id" name="event_id" value="<?php echo $r['event_id']; ?>">
			<input type="hidden" id="room_id" name="room_id" value="<?php echo $r['room_id']; ?>">
      <div class="form-group">
        <label for="class_name" class="col-sm-2 control-label">Class Name</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="class_name" name="class_name" placeholder="(ex. Alpha, Beta, Gamma, Delta, ...)"
                 <?php if (strlen($r['class_name'])>0) echo 'value="' . $r['class_name'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
			<div class="form-group">
        <label for="room_name" class="col-sm-2 control-label">Room Name</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="room_name" name="room_name" placeholder="(ex. Gamma 1, Gamma 2, Gamma 3, ...)"
                 <?php if (strlen($r['room_name'])>0) echo 'value="' . $r['room_name'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <div class="form-group">
        <label for="sort_order" class="col-sm-2 control-label">Sort Order</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="sort_order" name="sort_order" placeholder="(ex. 1, 2, 3, ...)"
                 <?php if (strlen($r['sort_order'])>0) echo 'value="' . $r['sort_order'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <div class="form-group">
        <label for="promotes_to" class="col-sm-2 control-label">Promotes To</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="promotes_to" name="promotes_to" placeholder="(ex. Alpha, Beta, ...)"
                 <?php if (strlen($r['promotes_to'])>0) echo 'value="' . $r['promotes_to'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <div class="form-group">
        <label for="demotes_to" class="col-sm-2 control-label">Demotes To</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="demotes_to" name="demotes_to" placeholder="(ex. Beta, Gamma, ...)"
                 <?php if (strlen($r['demotes_to'])>0) echo 'value="' . $r['demotes_to'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <hr style="border-color: DarkGray;">
    </form>
    <div class="row">
			<div class="col-md-2"></div>
		  <div class="col-md-2"><button class="btn btn-lg btn-success" id="button_back" style="min-width: 100%;" role="button">Back</a></div>
      <div class="col-md-2"><button class="btn btn-lg btn-info" id="button_saveasnew" style="min-width: 100%;" role="button">Save New</a></div>
      <div class="col-md-2"><button class="btn btn-lg btn-warning" id="button_update" style="min-width: 100%;" role="button">Update</a></div>
      <div class="col-md-2"><button class="btn btn-lg btn-danger" id="button_delete" style="min-width: 100%;" role="button">Delete</a></div>
			<div class="col-md-2"></div>
	  </div>
  </div> <!-- /text center well -->

  <div style="min-height: 200px;"><!-- give the login form some room to scroll up past the keyboard on mobile devices. --></div>

  <!-- Bootstrap core JavaScript
  ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="/jquery/jquery-2.1.4.min.js"></script>
  <script src="/bootstrap/js/bootstrap.min.js"></script>
  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>

  <!-- inline script for local page -->
  <script>
    $(document).ready(function() {
      // Start
      $("#nosubmit").submit(function (e) {
        e.preventDefault(); // do this or buttons/enter will reload page and ruin our javascript
       // Just do nothing because we have a keypress capture.
	    });
    });
    
		$("#button_back").click(function(){
      parent.history.back();
			return false;
    });
		
    $("#button_saveasnew").click(function(){
      post_edit_room_form('saveasnew');
    });
    
    $("#button_update").click(function(){
      post_edit_room_form('update');
    });
    
    $("#button_delete").click(function(){
      post_edit_room_form('delete');
    });

    function post_edit_room_form(a) {
      // get data from form
      var eid = $("#event_id").val();
      var rid = $("#room_id").val();
      var cname = $("#class_name").val();
      var rname = $("#room_name").val();
      var sorder = $("#sort_order").val();
      var pto = $("#promotes_to").val();
      var dto = $("#demotes_to").val();

      // post it!
	    var cmd = {
        action: a,
        event_id: eid,
        room_id: rid,
        class_name: cname,
        room_name: rname,
        sort_order: sorder,
        promotes_to: pto,
        demotes_to: dto
      }
      
		  $.post("http://www.advancedstudyroom.org/asrleague/forms/edit-room.php", cmd,
			  function(data) {
          //alert(data);// ignore status messages ("set to true" etc) unless we need to debug action.php again.
          window.location = "http://www.advancedstudyroom.org/asrleague/rooms.php?event_id=<?php echo $r['event_id']; ?>";
				} // function (data)
		  ); // $.post()
	  } // function
  </script>
</body>
</html>