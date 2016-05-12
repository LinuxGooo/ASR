<?php
//
// league.php for ASR wordpress site
// (C) 2015 Akisora Corporation
//
// Current Event Standings page
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors ', TRUE);
ini_set('display_startup_errors ', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/mdb.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/leaguelib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/debug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cachelib.php';

$time_start = microtime(true);

// Which table and commands should we show?
// If we are given an event_id, we will show the members who have joined that event.
// If we do not have an event_id, we need to let members choose which event they want to look at.

$req = array_merge($_GET, $_POST);
$event_id = 0;

// load proper values from $req
if (isset($req['event_id'])) {
	$event_id = htmlentities(substr($req['event_id'], 0, 256));
}

$display_single_room = false;
$display_room = "";
if (isset($req['room'])) {
	$display_single_room = true;
    $display_room = $req['room'];
}

if ($event_id == 0) {
  // get it from admin control panel if we weren't fed a value.
  $event_id = get_registry("admin-control-panel", "primary_event");
}

// $fixed="";
//$fixed = fix_OTtype_shim(); // see leaguelib.php. only need to run this once when the analyzer for OTttype and so forth changes.




echo bootstrap_head("Advanced Study Room -- Current Results");
if ($display_single_room) {
    $room_calc_out = calculate_room($event_id, $display_room);
    echo matches_played($room_calc_out);
    echo display_room($room_calc_out);
    die;
    exit;
}
echo "<body>";
echo '  <div class="row">';
echo '        <div class="col-md-1">';
echo '            <!-- left sidebar -->';
echo '    </div> <!-- col-md-1 -->';
echo '        <div class="col-md-10">';

echo bootstrap_navbar('league');
    
echo '        <h2>Current Results</h2>';
echo '        <!-- page or section alerts -->';
echo '        <div class="alert alert-info alert-dismissible" role="alert">';
echo '          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

$event = DB::queryFirstRow("SELECT * FROM events where event_id=%i;", $event_id);
echo 'You are currently browsing results for <strong>' . $event['event_name'] . ',</strong>';
$s_left = strtotime($event['date_ends']) - time();
$measure = "seconds";
if ($s_left > 60) {
    $s_left = $s_left / 60;
    $measure = "minutes";

    if ($s_left > 60) {
        $s_left = $s_left / 60;
        $measure = "hours";
        
        if ($s_left > 24) {
            $s_left = $s_left / 24;
            $measure = "days";
        }
    }
}

echo ' which runs from ' . date("F jS", strtotime($event['date_starts'])) . ' to ' . date("F jS", strtotime($event['date_ends'])) . '. inclusive.';
if ($s_left > 0) {
    $m = ' There are ' . number_format($s_left, 1) . ' ' . $measure . ' left to finish your games!';
    echo $m;
}
          

echo '        </div>';
echo '        <!-- Tabbed results -->';
echo '        <ul class="nav nav-tabs">';

$active = true; // to control active. Essentially, looks for lowest sort_order.
$rooms = DB::query("SELECT * FROM rooms WHERE event_id=%i ORDER BY sort_order;", $event_id);

foreach ($rooms as $r) {
    echo '<li';
    if ($active) {
        echo ' class="active"';
        $active = false; // only need to have it set manually on page load. Could also set based on room name.
    }
    echo '><a data-toggle="tab" href="#room_' .  str_replace(" ", "_", $r['room_name']) . '">' . $r['room_name'] . '</a></li>';
}

echo '        </ul>';
echo '        <div class="tab-content">';

//$rooms = DB::query("SELECT * FROM rooms WHERE event_id=%i ORDER BY sort_order;", $event_id); // keep data from prior.
$active = true;
foreach ($rooms as $r) {
              
    echo '<div id="room_' . str_replace(" ", "_", $r['room_name']) . '" class="tab-pane fade in';
    if ( $active ) {
        echo ' active';
        $active = false;
    }
    echo '">';
    echo '<h3>' . $r['room_name'] . '</h3>';

//    $room_calc_out = calculate_room($event_id, $r['room_name']);
// we don't calculate room every click but get those from cache
$room_calc_out=uncache_results($event_id,$r['room_name'] );
    echo matches_played($room_calc_out);
    echo display_room($room_calc_out);

    // display games.
    echo "<hr><h3>Games Played</h3>";
    //asort($room_calc_out['games']);
    foreach ($room_calc_out['games'] as $gid) {
        $g = DB::queryFirstRow("SELECT * from sgf where id=%i;", $gid);
        echo '<a href="http://tsumego.ca/forms/wgo-viewer.php?id=' . $g['id'] . '">' . $g['id'] . "</a>. " . $g['player_white'] . "-" . $g['player_black'] . " [" . date("Y-n-j", strtotime($g['date'])) . "] (" . $g['result'] . ") ";
        if (strlen($g['urlto']) > 0) {
            echo ' -- <a href="' . $g['urlto'] . '"><i class="fa fa-download"></i></a>';
        } else {
            echo ' -- <span class="fa-stack fa-lg"><i class="fa fa-download fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span> <em>cannot find file!</em>';
        }
        echo "\n<br>";
    }
    echo '</div>';
}

echo '        </div> <!-- /tab-content -->';
$time_end = microtime(true);
$time = $time_end - $time_start;
echo $time;

echo '  <!-- Site footer -->';
echo '  <footer class="footer">';
echo '    <hr>';
echo '    <p>&copy; 2016 advancedstudyroom.org</p>';

echo '  </footer>';

echo '        </div> <!-- col-md-10 -->';
echo '    <div class="col-md-1">';
echo '    </div> <!-- col-md-1 -->';
echo '    </div> <!-- row-->';

echo bootstrap_core_js();
        
echo '  <!-- inline script for local page -->';
echo '  <script>';
echo '    $(document).ready(function() {';
echo '      // Start';
echo "      $('#table-players').DataTable( {";
echo '        "paging":   false,';
echo '        "ordering": false,';
echo '        "info":     false,';
echo '        "searching": false,';
echo '        "autoWidth": false,';
echo '        "order": [[ 1, "desc" ]]';
echo '      });';
echo '    });';
echo '  </script>';
echo '</body>';
echo '</html>';
