<?php
// a wgo player page to iframe wherever you want
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';

$req = array_merge($_GET, $_POST);
echo "<!DOCTYPE html>\n<html>\n<head>\n";
echo "<link rel='stylesheet' id='wgo_player-css'  href='http://www.advancedstudyroom.org/wordpress/wp-content/plugins/go-baduk-weiqi/wgo/wgo.player.css?ver=4.5' type='text/css' media='all' />\n";
echo "<link rel='stylesheet' id='go-css-css'  href='http://www.advancedstudyroom.org/wordpress/wp-content/plugins/go-baduk-weiqi/go.css?ver=4.5' type='text/css' media='all' />\n";
echo "<script type='text/javascript' src='http://www.advancedstudyroom.org/wordpress/wp-content/plugins/go-baduk-weiqi/sgf.js?ver=4.5'></script>\n";
echo "<script type='text/javascript' src='http://www.advancedstudyroom.org/wordpress/wp-content/plugins/go-baduk-weiqi/wgo/wgo.min.js?ver=4.5'></script>\n";
echo "<script type='text/javascript' src='http://www.advancedstudyroom.org/wordpress/wp-content/plugins/go-baduk-weiqi/wgo/wgo.player.min.js?ver=4.5'></script>\n";
echo "<script type='text/javascript' src='http://www.advancedstudyroom.org/wordpress/wp-content/plugins/go-baduk-weiqi/wgo/i18n/i18n.en.js?ver=4.5'></script>\n";
echo "</head>\n";
echo "<body>\n";


if (isset($req['id'])){
$q = DB::queryFirstRow("SELECT sgf FROM sgf WHERE id=%i ;",$req['id']);
$sgf= $q['sgf'];
$n=str_replace('"',null,$sgf);
echo '<div data-wgo="'. $n . '"></div>';

}

echo "</body>";


echo "</html>";

 ?>
