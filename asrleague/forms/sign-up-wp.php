<?php
//
// sign-up.php
// (C) 2015 Akisora Corporation
//
// Allow self-sign-up for WordPress registered users.
// Record WordPress ID to track possible abuses.
//

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/IPLog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';

//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/schema.php';
//drop_table_participants();
//create_table_participants();

// 1. Command processing. similar to action.php
//
// log ip of anyone doing a search (will be repladed with a usage pattern monotir later on)
$iplogger = new IPlog();
$iplogger->iplog("self-signup");

// default values
//$req = array_merge($_GET, $_POST);
//$msg = "";

// load proper values from $req
//$action = "";
//if (isset($req['action'])) {/
//	$action = htmlentities(substr($req['action'], 0, 256));
//}

//if (isset($req['event_id'])) {	$event_id = htmlentities($req['event_id']); }
//if ($event_id == 0) {
    $event_id = get_registry("admin-control-panel", "primary_event");
//}

// Are we saving player data?
////////////////////////////////////////////////////////////////////////////////
// Note the check for user logged in...
if ( is_user_logged_in() && current_user_can('asr_league_member') ) {
 //1
get_currentuserinfo();
$k=$current_user->kgs_username;
//echo' You are ' .$k;  
  if ((strlen($k) > 0)) {
            $c = DB::query('SELECT * FROM participants WHERE kgsname=%s AND eid=%i;', $k, $event_id);
            if (count($c) == 0) {
                DB::insert('participants',
                           array('eid'      => $event_id,
                                 'kgsname'  => $k,
                                 'division' => 'unassigned',
                                 'wp_id'    => get_current_user_id()
                            ));
                
                header("Location: http://www.advancedstudyroom.org/profile/");
                die();

            } else {
                header("Location: http://www.advancedstudyroom.org/profile/");
                die();

                // do nothing, they already joined with that name.
            }
        }
    } // 1


?>
