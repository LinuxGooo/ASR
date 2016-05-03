<?php
//
// scraper.php
// (C) 2016 Akisora Corporation
//
// Requires the header files as included.
//
// Use: wget via cron job. You can look at it in a browser for debugging.
//
// Goals: Harvest links from a kgs user's account,
// scheduling them for download over time.
//
// TODO: Take all user membership info from WordPress API (but store league data in a separate DB for safety).
//

$start_time = microtime(true);

error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/IPLog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/registry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/SGF.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/debug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/makealpha.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/scraperlib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cachelib.php';


define('KGS_CRAWL_TIME', 31); // 31
$debug = true;
// log ip of anyone using admin scripts.
$iplogger = new IPlog();
$iplogger->iplog("minutely");
$debug = false;

function scrape() {
$debug = false;

// Determine crawl timings; is it ok to crawl now?
$ok_to_crawl_kgs = false;
$last_crawl_kgs = get_registry("crawls", "kgs");
$sincetime_kgs = time() - $last_crawl_kgs;
if ($sincetime_kgs >= KGS_CRAWL_TIME) {
  $ok_to_crawl_kgs = true;
}

// determine our primary event id for scraping.
$event_id = get_registry("admin-control-panel", "primary_event");

// human readable output given if $debug is true. For debugging purposes.
if ($debug) echo "<h2>match crawler</h2>";
if ($debug) echo "<p><em>note: This command is executed every five minutes by cron.</em></p>";
if ($debug) echo "<hr>";
if ($debug) echo "Time since last KGS crawl: " . $sincetime_kgs . "\n<br>";
if ($debug) echo '<hr>';


////////////////////////////////////////
// Preliminary WordPress System checks: (UID)
////////////////////////////////////////
//if ($debug) echo '<u>Logged into WordPress?</u> <strong>' . (is_user_logged_in() ? 'yes' : 'no') . "</strong>\n<br>";
//if ($debug && is_user_logged_in()) {
//    echo 'User ID: [' . $user->ID . "]\n<br>";
//    echo 'Username: [' . $user->user_login . "]\n<br>";
//    echo 'User display name: [' . $user->display_name . "]\n<br>";
//    echo 'User email: [' . $user->user_email . "]\n<br>";
//    echo 'User first name: [' . $user->user_firstname . "]\n<br>";
//    echo 'User last name: [' . $user->user_lastname . "]\n<br>";
//    echo '<hr>';
//}

 if ($ok_to_crawl_kgs == false) {
    // not ok to crawl yet
    if ($debug) echo "You can't do that... YET!<br>\n";
    if ($debug) echo "Please try again in " . (KGS_CRAWL_TIME - $sincetime_kgs) . " seconds.<br>\n";
    return;
}

////////////////////////////////////////
// 1.
// Check for requests to pull existing games.
////////////////////////////////////////
if ($ok_to_crawl_kgs) {
    if ($debug) echo "Checking KGS pull requests...<br>\n";

    $pull_request = DB::queryFirstRow("SELECT * FROM sgf WHERE pstatus=%i", 2); // 2 = PSTATUS_PULL_REQUEST (see schema.php)
    if ($pull_request != null) {
        $sgf_id = handle_pull_request($pull_request);
    
        if ($sgf_id == 0) {
            if ($debug) echo "could not handle pull request (error #152).<br>\n";
            return;
        }
        if ($sgf_id > 0) {
            // request is not null and we have an insert id
            // completed; wind-down phase
            set_registry("crawls", "kgs", time());
            $ok_to_crawl_kgs = false; // mark that we touched the server this round
	  //  cache_all_results($event_id);
            if ($debug) echo "Added game id#" . $sgf_id . " to database and updated cache.\n <br>";
            return;
        }
    } else {
        if ($debug) echo "no pull requests found.\n<br>";
    }
}

////////////////////////////////////////
// 2.
// get next participant by scrape priority.
////////////////////////////////////////
if ($ok_to_crawl_kgs) {
    $participant = scraper_find_next_player($event_id);
if ($participant['division']=='deleteme'){
	DB::query("DELETE FROM participants WHERE pid=%i AND eid=%i;", $participant['pid'],$participant['eid']);
	echo 'removed player'.$participant['kgsname'] . 'from deleteme';
	}
	else{
    scan_kgs_player($participant['kgsname'], $event_id);
    scraper_mark_player_scraped($event_id, $participant['kgsname']);
}
    set_registry("crawls", "kgs", time());
    $ok_to_crawl_kgs = false;
    
}


  if (false && $debug) {
    echo "SGF analyzer report:\n<br>";
    echo "Player White: " . $sgf->getprop("PW") . "\n<br>";
    echo "Player Black: " . $sgf->getprop("PB") . "\n<br>";
    echo " Date Played: " . $sgf->getprop("DT") . "\n<br>";
    echo "    Location: " . $sgf->getprop("PC") . "\n<br>";
    echo "     Ruleset: " . $sgf->getprop("RU") . "\n<br>";
    echo "        Komi: " . $sgf->getprop("KM") . "\n<br>";
    echo "        Time: " . $sgf->getprop("TM") . " seconds (" . floor($sgf->getprop("TM")/60) . " minutes and " . number_format($sgf->getprop("TM")%60, 0)  .  " seconds).\n<br>";
    echo "    Overtime: " . $sgf->getprop("OT") . "\n<br>";
    echo "      Result: " . $sgf->getprop("RE") . "\n<br>";
    echo "\n<br>";
    echo "  Black Wins? (derived): " . ($sgf->getprop("blackwins") ? "true" : "false") . "\n<br>";
    echo "  White Wins? (derived): " . ($sgf->getprop("whitewins") ? "true" : "false") . "\n<br>";
    echo "Winner's Name (derived): " . $sgf->getprop("winner") . "\n<br>";
    echo "Overtime Type (derived):" . $sgf->getprop("overtime") . "\n<br>";
    echo "Overtime Periods (derived): " . $sgf->getprop("periods") . "\n<br>";
    echo "Overtime Seconds (derived): " . $sgf->getprop("seconds") . "\n<br>";
    echo '<hr>';
    echo "Game Key (derived): " . $sgf->info_key . " (info string: " . $sgf->info_str . ")\n<br>";
    echo "File Key (derived): " . $sgf->data_key . "\n<br>";
    echo "Move Key (derived): " . $sgf->game_key . "\n<br>";
    echo '<hr>';
  }

echo "<hr>";
$end_time = microtime(true);
$run_time = ($end_time - $start_time) * 1000;
if ($debug) echo number_format($run_time, 1) . "ms\n<br>";
}
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// Attempts to pull ONE file, save it to DB, and remove the pull request.
// Returns the id of the pulled file if we inserted it.
// Returns 0 if no request was pulled.
function handle_pull_request($pull_request = NULL) {
    global $debug;
    
    // parameters check
    if ($pull_request == NULL) {
        $pull_request = DB::queryFirstRow("SELECT * FROM sgf WHERE pstatus=%i", 2); // 2 = PSTATUS_PULL_REQUEST (see schema.php)
        if ($pull_request == NULL) {
            // If it's *still* null, then we were passed nothing but there was no request in the sgf table.
            if ($debug) echo "handler: still null error #153<br>\n";
            return 0;
        }
    }
  
    // Pull the link into $data.
    if (($pull_request['urlto'] == null) || (strlen($pull_request['urlto']) < 5))
    {
        // drop ridiculous requests.
        //if ($debug) echo "--- pull_one_file() status: dropping pull error #154<br>\n";
        DB::query("DELETE FROM sgf WHERE id=%i;", $pull_request['id']);
        return 0;
    }

    // pull the actual (assumed to be SGF) data out of the URL.
    //if ($debug) echo "--- pulling: [" . $pull_request['urlto'] . "]<br>\n";
    $data = file_get_contents($pull_request['urlto']);

    // Scan it and save it to DB.
    $sgf = new SGF();
    $sgf->scan($data);  // This inserts a new record if the info_key is different. So we can check if it did something via insertId.
    $sgf->prop['urlto'] = $pull_request['urlto'];
    $sgf->prop['filename'] = basename($pull_request['urlto']);
	$sgf->prop['type'] = $pull_request['type']; // retail type info.
  
    // Let this date stuff be done here, post-scan, because it is KGS-specific and obtained via the scrape not via sgf analysis.
    if (!isset($pull_request['date'])) {
        // not set, do nothing.
    } else {
        // It's set. use it.
        if ($debug)  { echo "Found KGS datetime: " . date("Y-m-d G:i:s", strtotime($pull_request['date'])) . "\n<br>"; }
        $sgf->prop['DT'] = date("Y-m-d G:i:s", strtotime($pull_request['date'])); // ...then keep it.
    }
    
    if (!isset($pull_request['tags'])) {
        // not set, do nothing.
    } else {
        // It's set. use it.
        $sgf->tags = $pull_request['tags']; // ...then keep it.
    }
    $sgf->derive_info();
    $sgf->derive_keys();
	$new_id = find_lowest_unused_id();
    $pulled = $sgf->save_to_db($new_id);

    // remove the pull request if a new sgf has been saved.
    if ($pulled > 0) {
        DB::delete("sgf", "id=%i", $pull_request['id']);
  }
  
  //if ($debug) echo "--- pull_one_file() status: " . $pulled . "\n<br>";
  
  return $pulled;
}

//
// Check if a string conforms to a particular datetime format.
//
function is_datetime($str_dt, $str_dateformat="n/j/y g:i A", $str_timezone="Asia/Tokyo") {
	global $debug;

  $dt = strtotime($str_dt);
	$dt2 = date($str_dateformat, $dt);
	if (strcmp($str_dt, $dt2) == 0)
	{
		if ($debug) echo "--- is_datetime: true.\n<br>";
		return true;
	}
	if ($debug) echo "--- is_datetime: false.\n<br>";
	if ($debug) echo "--- D1: [" . $str_dt . "]\n<br>";
	if ($debug) echo "--- D2: [" . $dt2 . "]\n<br>";
  return false;
}

// Call with TABLE and ID to look at.
// returns the lowest ID we can use to try an insert.
function find_lowest_unused_id($table="sgf", $column="id") {
	$r = DB::query ( "SELECT %l FROM %l; ", $column, $table );
	$max = 0;
	$new_id = 1;
	$ids = array();
	foreach ($r as $k => $an_id) {
		$ids[$an_id['id']] = $an_id['id'];
	}
	foreach ($ids as $id) {
		if (in_array($new_id, $ids)) {
			$new_id++; // the new id is in there.
		} else {
			// we found one!
			return ($new_id);
		}
	}
	
	// for one item if id was 1, new_id is now 2. for 2 items at 1 and 2, new id is now 3.
	// but if the ids were different, i.e. not contiguous, we would have found a hole and returned it by now.
	return ($new_id);
	
}


// This checks the kgs archives to see if a player has any games we didn't pull yet.
// That means, only add links which don't already exist in the urlto column.
function scan_kgs_player($kgsname, $event_id) {
  global $debug;

  // get $yyyy and $mm
  $event = DB::queryFirstRow("SELECT * FROM events WHERE event_id=%i;", $event_id);
  $yyyy = date("Y", strtotime($event['date_starts']));
  $m1 = date("n", strtotime($event['date_starts']));
  $m2 = date("n", strtotime($event['date_ends']));
  if ($m2 < $m1) { $m2 += 12; }
  for ($mm = $m1; $mm <= $m2; $mm++) {
    $year = $yyyy;
    $month = $mm;
    if ($mm > 12) { $year = $yyyy +1; $month = $mm-12; }

    $kgs_link = 'http://www.gokgs.com/gameArchives.jsp?year=' . $year . '&month=' . $month . '&user=' . $kgsname;
    echo "Scanning KGS player " . $kgsname . " month " . $year . "-" . $month . "<br>\n";
    $html = file_get_contents($kgs_link);
      
    // no games check
    if ((strpos($html, "(0 games)") !== false) && (strpos($html, " did not play any games during ") !== false)) {
        // Mark him scraped.
        scraper_mark_player_scraped($event_id, $kgsname);
        if ($debug) echo "No games found for player " . $kgsname . ".<br>\n";
        return false;
    }

    // convert linked "Yes" into links.
  	// Note: I would like to optimize this to only convert "Yes" links.
  	// It currently converts "all" links.

  	$html = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\1", $html);

  	// fix table headings into array keys
  	$html = str_replace("<th>Viewable?</th>", "<th>link</th>", $html);
  	$html = str_replace("<th>White</th>", "<th>wplayer</th>", $html);
  	$html = str_replace("<th>Black</th>", "<th>bplayer</th>", $html);
  	$html = str_replace("<th>Setup</th>", "<th>boardsize</th>", $html);
  	$html = str_replace("<th>Start Time<br>(tzList.jsp)</th>", "<th>gtime</th>", $html);
  	$html = str_replace("<th>Type</th>", "<th>type</th>", $html);
  	$html = str_replace("<th>Result</th>", "<th>result</th>", $html);
  
    // fix misreported board size (fix weird unicode or whatever)
  	$html = str_replace("<td>19Ãƒâ€”19 </td>", "<td>19</td>", $html);
    $html = str_replace("<td>19Ã—19 </td>", "<td>19</td>", $html);

  	// clean player names
  	$html = str_replace("gameArchives.jsp?user=", "", $html);

  	// Find the table
  	preg_match("/<table.*?>.*?<\/[\s]*table>/s", $html, $table_html);

  	// Get title for each row
  	preg_match_all("/<th.*?>(.*?)<\/[\s]*th>/", $table_html[0], $matches);
  	$row_headers = $matches[1];

    // Iterate each row
  	preg_match_all("/<tr.*?>(.*?)<\/[\s]*tr>/s", $table_html[0], $matches);
  	$table = array();
  	foreach($matches[1] as $row_html) {
      preg_match_all("/<td.*?>(.*?)<\/[\s]*td>/", $row_html, $td_matches);
      $row = array();
      for($i=0; $i<count($td_matches[1]); $i++) {
        $td = strip_tags(html_entity_decode($td_matches[1][$i]));
        if (!isset($row_headers[$i])) {
          // Oops -- we must be dealing with a "did not play any games" or other unknown table. Abort! Abort!
          if ($debug) echo "scan_kgs_player(): no result.\n<br>";
            return 0;
        }
        $row[$row_headers[$i]] = $td;
      }

      if(count($row) > 0) {
        if (strcmp($row['link'],"No") != 0) {
          // Don't save "no" links; they're P games or other, which we don't need.
            $table[] = $row;
        } //if link is available
      } // if count $row
    } // for each matches as row

    // For every row in the now fixed-up table,
      $games_added=0;
    foreach ($table as $row) {
      $exists = DB::queryFirstField("SELECT COUNT(*) FROM sgf WHERE urlto=%s;", $row['link']);
      // fix the kgs archives' review table column screwup if it's there.
      if ((strcmp(strtolower($row['gtime']), "review") == 0) || (strcmp(strtolower($row['gtime']), "demonstration") == 0)) {
        $row['result'] = $row['type'];
        $row['type'] = $row ['gtime'];
        $row['gtime'] = $row ['boardsize'];
        $row['boardsize'] = $row['bplayer'];
        $row['bplayer'] = $row['wplayer'];
      }

      // perform a player name check to make sure we only look at games between participants.
      $participants = DB::query("SELECT * from participants WHERE eid=%i;", $event_id);

      $pwhite_ok = false;
      $pblack_ok = false;
      foreach ($participants as $p) {
        if (strcmp(strtolower($p['kgsname']), strtolower($row['wplayer'])) == 0) { $pwhite_ok = true; }
        if (strcmp(strtolower($p['kgsname']), strtolower($row['bplayer'])) == 0) { $pblack_ok = true; }
      }
      
      // don't add unfinished games
      $unfinished = false;
      if (!isset ($row['result'])) {
        echo "result not set? row gtime contains: [" . $row['gtime'] . "]\n<br>";
      } else {
        $unfinished = strcmp(strtolower($row['result']), "unfinished") == 0;
      }
      $game_ok = (!$unfinished) && ($exists == 0) && ($pwhite_ok) && ($pblack_ok);
      
      // status messages for debugging
      //if ($unfinished) echo "Game OK: no, unfinished<br>\n";
      //if ($exists) echo "Game OK: no, exists<br>\n";
      //if ($pwhite_ok) echo "Game OK: no, pwhite_ok<br>\n";
      //if ($pblack_ok) echo "Game OK: no, pblack_ok<br>\n";
        
      // save game if it passes the filters.
      if ($game_ok) {
        $new_id = find_lowest_unused_id();
        // insert with gtime & tags
        DB::insert("sgf", array( 'id' => $new_id,
                                 'urlto' => $row['link'],
                                 'data_key' => 'pull_request',
                                 'pstatus' => 2, /* 2 = PSTATUS_PULL_REQUEST (see schema.php) */
                                 'place' => 'KGS', 
                                 'filename' => $row['link'], 
                                 'type' => $row['type'],
                                 'date' => date("Y-m-d G:i:s", strtotime($row['gtime']))   )     );
        $games_added += 1;
        if ($debug) echo "added pull request for: " . htmlentities($row['link']) . "\n<br>";
      } else {
        if ($debug) {
          if ($pwhite_ok && $pblack_ok) {
            if ($debug) echo "we already have a record for game: " . htmlentities($row['link']) . "\n<br>";
          } else {
            // silently discard non-ok (non-league-member) games.
          }
        }
      }
    } // for every row (game) in the fixed-up table.
  } // the massive for loop for months.
    
    if ($games_added == 0) echo "summary: no new games found.<br>\n";
    if ($games_added == 1) echo "summary: one new game found.<br>\n";
    if ($games_added > 1) echo "summary: " . $games_added . " new games found.<br>\n";
  // return what?
  return;
}
?>
