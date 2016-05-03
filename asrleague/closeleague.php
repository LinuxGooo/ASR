<?php
error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cachelib.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/mdb.php';

$time_start = microtime(true);

$rooms = DB::query("SELECT * FROM rooms WHERE event_id=%i ORDER BY sort_order;", 1);
foreach ($rooms  as $r) {
$room_calc_out=uncache_results(2,$r['room_name']);
echo display_room($room_calc_out);
}


?>
