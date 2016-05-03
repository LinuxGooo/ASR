<?php
//
// edit-event.php
// (C) 2015 Akisora Corporation
//
// Edit events.
// This file has three parts:
// 1. command processing ex. action is new or add;
// 2. the form, which is displayed (default action is add)
// 3. the redirect code. When we DO an add (submit the save data) we redirect to admin events by default or follow a "redirect" link from earlier.
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
$iplogger->iplog("edit-event");

/// If the user is not logged in and/or an admin, redirect to member's dashboard
if ( is_user_logged_in()  == false) {
  die("Please log into WordPress before editing events.");
}
if ( current_user_can('asr_edit_events') == false) {
     die("Please log into a League Admin WordPress account to edit events.");
}

// default values
$req = array_merge($_GET, $_POST);
$action = "";
$value = "";
$r = array();
$r['event_id'] = 0;
$r['event_name'] = "";
$r['event_type'] = "";
$r['date_created'] = "";
$r['date_starts'] = "";
$r['date_ends'] = "";
$r['points_per_win'] = "";
$r['points_per_loss'] = "";
if (isset($req['event_id'])) {	$r['event_id'] = $req['event_id']; }
if (isset($req['event_name'])) {	$r['event_name'] = $req['event_name']; }
if (isset($req['event_type'])) {	$r['event_type'] = $req['event_type']; }
if (isset($req['date_created'])) {	$r['date_created'] = date('Y-m-d H:i:s', strtotime($req['date_created'])); }
if (isset($req['date_starts'])) {	$r['date_starts'] = date('Y-m-d H:i:s', strtotime($req['date_starts'])); }
if (isset($req['date_ends'])) {	$r['date_ends'] = date('Y-m-d H:i:s', strtotime($req['date_ends'])); }
if (isset($req['points_per_win'])) {	$r['points_per_win'] = $req['points_per_win']; }
if (isset($req['points_per_loss'])) {	$r['points_per_loss'] = $req['points_per_loss']; }

// load proper values from $req
if (isset($req['action'])) {
	$action = htmlentities(substr($req['action'], 0, 256));
}

////////////////////////////////////////////////////////////////////////////////
// Edit an event? i.e. should we pre-load an event?
////////////////////////////////////////////////////////////////////////////////
if ((strcmp($action, "edit") == 0))
{
  $r = DB::queryFirstRow("SELECT * FROM events WHERE event_id=%i", $r['event_id']);
}

if (strcmp($action, "saveasnew") == 0) {
  $r['event_id'] = 0;
  unset($r['event_id']);
  $r['date_created'] = DB::sqleval("NOW()");
  DB::insert("events", $r);
  die();
}
    
if (strcmp($action, "update") == 0) {
  DB::update("events", $r, "event_id=%i", $r['event_id']);
  die();
}

if (strcmp($action, "delete") == 0) {
  $r = DB::query("DELETE FROM events WHERE event_id=%i;", $r['event_id']);
  die();
}

// Fallthru: form is loaded, or default action is new (blank) event.
echo bootstrap_head("Advanced Study Room -- Edit Event Form");
?>

  <!-- Login Form -->
  <div class="text-center well" style="background: #efefef; padding-bottom: 32px;">
    <h1>Edit Event<?php if ($r['event_id'] > 0) echo ' (' . $r['event_id'] . ')'; ?></h1>
    <hr style="border-color: DarkGray;">
    <!-- Main Form -->
    <form class="form-horizontal" id="nosubmit">
      <input type="hidden" id="event_id" name="event_id" value="<?php if (strlen($r['event_id'])>0) echo $r['event_id']; ?>">
      <div class="form-group">
        <label for="event_name" class="col-sm-2 control-label">Event Name</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="event_name" name="event_name" placeholder="(ex. monthly league, annual tournament, asr meijin, ...)"
                 <?php if (strlen($r['event_name'])>0) echo 'value="' . $r['event_name'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <div class="form-group">
        <label for="event_type" class="col-sm-2 control-label">Event Type</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="event_type" name="event_type" placeholder="(ex. league, tournament, playoff, superclass, ...)"
                 <?php if (strlen($r['event_type'])>0) echo 'value="' . $r['event_type'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <input type="hidden" id="date_created" name="date_created" value="<?php if (strlen($r['date_created'])>0) echo $r['date_created']; ?>">      
      <div class="form-group">
        <label for="date_starts" class="col-sm-2 control-label">Start Date</label>
        <div class="col-sm-10">
          <input type="date" class="form-control" id="date_starts" name="date_starts" placeholder="YYYY-MM-DD"
                 <?php if (strlen($r['date_starts'])>0) echo 'value="' . $r['date_starts'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <div class="form-group">
        <label for="date_ends" class="col-sm-2 control-label">End Date</label>
        <div class="col-sm-10">
          <input type="date" class="form-control" id="date_ends" name="date_ends" placeholder="YYYY-MM-DD"
                 <?php if (strlen($r['date_ends'])>0) echo 'value="' . $r['date_ends'] . '"'; ?>
                 autocomplete="off">
        </div>
      </div>
      <div class="form-group">
        <label for="points_per_win" class="col-sm-2 control-label">Points per Win</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="points_per_win" name="points_per_win" placeholder="Number (ex. 2, 1.5)."<?php if (strlen($r['points_per_win'])>0) echo ' value="' . $r['points_per_win'] . '"'; ?> autocomplete="off">
          <p class="help-block">The number of points awarded for a win and subsequent wins vs. the same opponent (comma separated).</p>
        </div>
      </div>
      <div class="form-group">
        <label for="points_per_loss" class="col-sm-2 control-label">Points per Loss</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="points_per_loss" name="points_per_loss" placeholder="Number (ex. 0.75, 0.75)"<?php if (strlen($r['points_per_loss'])>0) echo ' value="' . $r['points_per_loss'] . '"'; ?> autocomplete="off">
          <p class="help-block">The number of points awarded for a loss and subsequent losses vs. the same opponent (comma separated).</p>
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
      return post_edit_event_form('saveasnew');
    });
    
    $("#button_update").click(function(){
      return post_edit_event_form('update');
    });
    
    $("#button_delete").click(function(){
      return post_edit_event_form('delete');
    });

    function post_edit_event_form(a) {
      // get data from form
      var eid = $("#event_id").val();
      var ename = $("#event_name").val();
      var etype = $("#event_type").val();
      var dcreated = $("#date_created").val();
      var dstarts = $("#date_starts").val();
      var dends = $("#date_ends").val();
      var ppwin = $("#points_per_win").val();
      var pploss = $("#points_per_loss").val();

      // post it!
	    var cmd = {
        action: a,
        event_id: eid,
        event_name: ename,
        event_type: etype,
        date_created: dcreated,
        date_starts: dstarts,
        date_ends: dends,
        points_per_win: ppwin,
        points_per_loss: pploss
      }
      
		  $.post("http://www.advancedstudyroom.org/asrleague/forms/edit-event.php", cmd,
			  function(data) {
          //alert(data);// ignore status messages ("set to true" etc) unless we need to debug action.php again.
          window.location = "http://www.advancedstudyroom.org/asrleague/events.php";
              return false;
				} // function (data)
		  ); // $.post()
	  } // function
  </script>
</body>
</html>