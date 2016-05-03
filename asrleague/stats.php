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
        <div class="">
        
        <?php

          $games = DB::query("SELECT id,result FROM sgf where $eid;");

          foreach ($games as $game) {


        ?>




</body>
</html>
