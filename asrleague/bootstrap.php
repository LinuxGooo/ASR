<?php
//
// bootstrap.php for asr league
// (C) 2015 Akisora Corporation
//
// Converted to using wordpress for user ID and auth in april 2016
//

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';

error_reporting(E_ALL); //error_reporting(-1);
ini_set( 'display_errors', TRUE);
ini_set( 'display_startup_errors', TRUE);

// common header code.
function bootstrap_head($page_title = '', $local_insert = '') {
  $data = '';
  $data .='<!DOCTYPE html>'."\n";
  $data .='<html lang="en">'."\n";
  $data .=''."\n";
  $data .='<head>'."\n";
  $data .='    <meta charset="utf-8">'."\n";
  $data .='    <meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n";
  $data .='    <meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
  $data .='    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->'."\n";
  $data .='    <meta name="description" content="Advanced Study Room Go Wei-Qi Baduk League">'."\n";
  $data .='    <meta name="author" content="tu">'."\n";
  $data .='    <link rel="icon" href="/favicon.ico">'."\n";
  $data .=''."\n";
  $data .='    <title>' . $page_title . '</title>'."\n";
  $data .=''."\n";
  $data .='    <!-- Bootstrap core CSS -->'."\n";
  $data .='    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">'."\n";
  $data .='    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/s/bs-3.3.5/dt-1.10.10/datatables.min.css"/>';

  $data .=''."\n";
  $data .='    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->'."\n";
  $data .='    <link href="/bootstrap/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">'."\n";
  $data .=''."\n";
  $data .='    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->'."\n";
  $data .='    <!--[if lt IE 9]>'."\n";
  $data .='      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>'."\n";
  $data .='      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>'."\n";
  $data .='    <![endif]-->'."\n";
  $data .='    <!-- Custom fonts -->'."\n";
  $data .= '   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">'."\n";
  // your own font data here:
  // $data .='    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700">'."\n";
  $data .='  '."\n";
  $data .='    <!-- Custom styles for this template -->'."\n";
  $data .='    <link href="/asrleague/css/custom.css" rel="stylesheet" type="text/css">'."\n";
  $data .=''."\n";
  $data .= $local_insert;
  $data .='</head>'."\n";
  return $data;
}

// Fixed + Fluid -- based on http://www.codeply.com/go/bp/mL7j0aOINa
function bootstrap_navbar($active = 0, $meta = '') {

    $data = "";
    $data .= '<p><a href="http://www.advancedstudyroom.org">advancedstudyroom.org ' ;
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        if ($user->exists()) {
            $data .= " -- logged into wordpress as: ". $user->user_login;
        }
    } else {
        $data .= " -- (not logged in)";
    }
    $data .= "</a></p>\n";
  
    // convert from words
    if (strcmp($active, "home") == 0)    { $active = 0; }
    if (strcmp($active, "league") == 0)  { $active = 1; }
    if (strcmp($active, "events") == 0)  { $active = 2; }
    if (strcmp($active, "games") == 0)   { $active = 3; }
    
    $data .= '<p>[ ';
    if ($active==0) { $data .= '<strong>Home</strong>'; } else { $data .= '<a href="/asrleague/index.php">Home</a>'; }
    if ($active==1) { $data .= " | <strong>League (April 2016)</strong>"; }   else { $data .= ' | <a href="/asrleague/league.php?event_id=1">League Standings (April 2016)</a>'; }
    if ($active==2) { $data .= " | <strong>Events</strong>"; }              else { $data .= ' | <a href="/asrleague/events.php">Events</a>'; }
    if ($active==3) { $data .= " | <strong>Games</strong>"; }               else { $data .= ' | <a href="/asrleague/archive.php">Games</a>'; }

    $data .= ' ]';
    $data .= "</p>\n";
    
    return $data;  
}


function bootstrap_xs_toggle_sidebar() {
  $data = '';
  $data .= '<button type="button" class="btn btn-primary btn-xs visible-xs" data-toggle="offcanvas"><i class="glyphicon glyphicon-chevron-left"></i></button>'."\n";
  return $data;
}

function bootstrap_core_js() {
  $data = '';
  $data .= '    <!-- Bootstrap core JavaScript'."\n";
  $data .= '    ================================================== -->'."\n";
  $data .= '    <!-- Placed at the end of the document so the pages load faster -->'."\n";
//$data .= '    <script src="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/2.2.0/metisMenu.min.js"></script>'."\n";
  $data .= '    <script src="/jquery/jquery-2.1.4.min.js"></script>'."\n";
  $data .= '    <script src="/bootstrap/js/bootstrap.min.js"></script>'."\n";
  $data .= '    <script type="text/javascript" src="https://cdn.datatables.net/s/bs-3.3.5/dt-1.10.10/datatables.min.js"></script>'."\n";

  $data .= '    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->'."\n";
  $data .= '    <script src="/bootstrap/assets/js/ie10-viewport-bug-workaround.js"></script>'."\n";
  $data .= '    <script src="/js/custom.js"></script>'."\n";
  return $data;
}

?>