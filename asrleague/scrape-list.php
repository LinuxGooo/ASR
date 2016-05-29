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

// messages
$req = array_merge($_GET, $_POST);
$msg = '';

if (isset($req['msg'])) { $msg = substr($req['msg'], 0, 256); }
?>


<body>
        
        <?php
 	 $games = DB::query("SELECT id,pstatus,data_key,player_white,player_black,date,place,type,result FROM sgf WHERE player_white=%s OR player_black=%s ORDER BY date LIMIT 10000;",'climu','climu');
          $manuals = DB::query("SELECT kgsname FROM participants where scrape_priority>%i;",1);
          $to_scrape= DB::queryFirstRow("SELECT COUNT(kgsname) FROM participants where scrape_priority=%i;",1);
          $scraped = DB::queryFirstRow("SELECT COUNT(kgsname) FROM participants where scrape_priority=%i;",0);
	  $to_scrape=$to_scrape['COUNT(kgsname)'];
	  $scraped=$scraped['COUNT(kgsname)'];
	  $total= count($manuals) + $to_scrape + $scraped;
if (count($manuals)>0){
	$out='<div> Participants who requested manual game check:<ul>';
	foreach ($manuals as $p){
	$out.='<li>'.$p[kgsname] .'</li>';
	}
	$out.='</ul></div>';
}
$out.='<p> Scraped ' .$scraped .'/' . $total . '. ' . $to_scrape .' remaining.</p>';
echo $out;
        ?>




</body>
</html>
