<?php
////////////////////////////////////////////////////////////////////////
////  this file scrape every 32 sec 10 times. To use carefully at rolover time.
////  a bit violent for the server
//////////////////////////////////////////////////////////////////

//error_reporting(E_ALL); //error_reporting(-1);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);


//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/scraper-rol.php';
//DB::query("UPDATE participants SET scrape_priority=1 WHERE eid=%i", $event_id); // met tout le monde à 1
//$query='SELECT DISTINCT count(pid) FROM participants WHERE scrape_priority = 1 AND eid = 1;';
//$iNumRuns = DB::query($query);
//$iNumRuns = $iNumRuns[0]['count(pid)'];
//$iBuffer = 10; //contient le nombre de fois qu'on veut faire runner le scraper après qu'il n'ait plus trouvé personne
//$icount=5;
//while (( $iNumRuns > 1 )&&($icount>1)) {
//	$iNumRuns = DB::query($query);
///	$iNumRuns = $iNumRuns[0]['count(pid)'];
 //	scrape();
//	sleep(32);
//	$icount--;
//set_time_limit(260);
//}/
//if  ($iNumRuns < 2){/
//echo '<p>All participants scraped ! Just run me one more time and you are done. Thanks Litchee !</p>';
//}
//else {
//echo '<p> We just scraped 5 times...</p>';
//echo '<p> Still ' . $iNumRuns  . ' player to scraper</p>';
//echo '<p>still not bored? run me again !</p>';
//}
?>
