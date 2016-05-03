<?php
error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/scraper-rol.php';
//DB::query("UPDATE participants SET scrape_priority=1 WHERE eid=%i", $event_id); // met tout le monde à 1
$query='SELECT DISTINCT count(pid) FROM participants WHERE scrape_priority = 1 AND eid = 1 ;';
$iNumRuns = DB::query($query);
$iNumRuns = $iNumRuns[0]['count(pid)'];
if  (($iNumRuns < 2) || ( $iNumRuns > 100 )) {

echo '<p>All participants scraped ! Just run it one more time and you are done. Thanks Litchee !</p>';
}
else {
echo '<p> Still ' . $iNumRuns  . ' player to scrape</p>';
echo '<p> Be brave Litchee !</p>';
}
?>
