<?php
//
// archive.php for ASR WordPress site
// (C) 2015 Akisora Corporation
//
// This is where players can browse the sgf collection.
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors ', TRUE);
ini_set('display_startup_errors ', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/mdb.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cachelib.php';

// messages
$req = array_merge($_GET, $_POST);
$msg = '';

//if (isset($req['msg'])) { $msg = substr($req['msg'], 0, 256); }
$event_id = get_registry("admin-control-panel", "primary_event");

$participants = DB::query("SELECT * FROM participants WHERE eid = ". $event_id."");
 echo "<html><body>"
if (count($participants)>0){
	$out='<div> Participants:<ul>';
	foreach ($participants as $p){
	$out.='<li>'.$p['kgsname'] .' - '.$p['division'].' </li>';
	}
	$out.='</ul></div>';
}

$rooms = DB::query("SELECT * FROM rooms WHERE event_id = ". $event_id."");

foreach($rooms as $r){
	$alpha = uncache_results($event_id, $r['room_name']);
	echo '<pre>';
	print_r($alpha);
	echo '</pre>'
}



?>


</body>
</html>
