<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/mdb.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/leaguelib.php';

function cache_results($event_id,$division){
	$room_name=str_replace(" ", "_", $division);
	$file= $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cache/'. $event_id . $room_name .'.bin';
	$calc_out= calculate_room($event_id, $division);
	file_put_contents($file, serialize($calc_out));
}

function uncache_results($event_id,$division){
	$room_name=str_replace(" ", "_", $division);
	$file= $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cache/'.$event_id. $room_name .'.bin';
	$calc_out=unserialize(file_get_contents($file));
	return $calc_out;
}

function cache_all_results($event_id){
	$rooms = DB::query("SELECT room_name FROM rooms WHERE event_id=%i ORDER BY sort_order;", $event_id);
	foreach ($rooms as $r) {
		cache_results($event_id,$r['room_name']);
	}
}

?>
