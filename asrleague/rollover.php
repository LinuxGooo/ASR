<?php
//
// archive.php for ASR WordPress site
// (C) 2015 Akisora Corporation
//
// This is where players can browse the sgf collection.
//

error_reporting(E_ALL); //error_reporting(-1);
//ini_set('display_errors ', TRUE);
//ini_set('display_startup_errors ', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/mdb.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cachelib.php';

if ( is_user_logged_in() && current_user_can('asr_edit_events') ){



// messages
$req = array_merge($_GET, $_POST);
$msg = '';

//if (isset($req['msg'])) { $msg = substr($req['msg'], 0, 256); }
$event_id = get_registry("admin-control-panel", "primary_event");
//
//echo "coucou";
//echo $event_id;
$participants = DB::query("SELECT * FROM participants WHERE eid=%i", $event_id);
 echo "<html><body>";

$rooms = DB::query("SELECT * FROM rooms WHERE event_id=%i",$event_id);

foreach($rooms as $r){
	$alpha = uncache_results($event_id, $r['room_name']);
//	echo '<pre>';
//	print_r($alpha);
//	echo '</pre>';
	foreach($alpha["participants"] as $p){
		$active[$p["kgsname"]] = $p["active"];
	}

}



$query = "INSERT INTO participants (kgsname, eid, division, scrape_priority, avaliable, wp_id) VALUES ";

 echo "actual event : ". $event_id;
$new_event_id = $event_id+1;
echo "<br/> new event : ". $new_event_id; 
$ct = 0;
$ignored = 0;
$res = '';
//if (count($participants)>0){
	$res .= '<div> Participants:<ul>';
	foreach ($participants as $p){
		$res .= '<li>'.$p['kgsname'] .' - '.$p['division'] ;
		$res .= ' '.(($active[$p['kgsname']])?'<span style="color:green">active</span>':'<span style="color:red">inactive</span>').'</li>';

		if($active[$p['kgsname']]){
			if ($ct > 0) $query .= ',';
			$query .= "('".$p['kgsname']."','".$new_event_id."','".$p['division']."','".$p['scrape_priority']."', NULL ,'".$p['wp_id']."')";
			$ct++;
		}
		else $ignored++;
	}
	#res .= '</ul></div>';
//}//
//	echo $out;

echo '<br /><br />'. $ct ." participants added to event : ".$new_event_id;
echo '<br />'.$ignored .' particpants were inactive and were not added to the new event';

echo '<br />'. $res;
$query .= " ON DUPLICATE KEY UPDATE division = division ";

//echo $query;

DB::query($query);


}
else {
	echo "no rights";
}
?>

</body>
</html>
