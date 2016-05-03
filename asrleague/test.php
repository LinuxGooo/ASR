<?php
error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cachelib.php';
$out=uncache_results(1,'Beta');
echo display_room($out);

?>
