<?php
//
// leaguelib.php for tsumego.ca
// (C) 2015 Akisora Corporation
//
// Support functions for league admin
//

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/SGF.php';


// Gets the list of events.
function get_events_data() {
  $r = DB::query("SELECT * FROM events;");
  return $r;
}

// Gets a list of member IDs who are signed up to a particular event.
function get_participants($event_id) {
  $r = DB::query("SELECT * FROM participants WHERE eid=%i;", $event_id);
  return $r;
}

// add a member to an event.
function add_participant($uid, $eid) {
  remove_participant($uid, $eid);
  DB::insert('participants', array(
    'uid' => $uid,
    'eid' => $eid
  ));
  return;
}

function remove_participant($uid, $eid) {
  // ensure the member is not already in the event
  DB::query("DELETE FROM participants WHERE uid=%i AND eid=%i;", $uid, $eid);
  return;
}

// see data calc below.
function display_room($room_calc_out) {
  $out = '';
  $participants = $room_calc_out['participants'];
  $out .= '<div class="table-responsive">';
  $out .= '<table class="table table-bordered table-player table-league">';
  $out .= '<thead class="table-league-highlight"><th>#. kgsname</th><th>score</th>';
  foreach ($participants as $p) {
    $out .= '<th style="text-align: center; font-weight: normal;">' . $p['kgsname'] . '</th>';
  }
  $out .= "</thead><tbody>"; // no <br> here.
  foreach ($participants as $rk => $row) {
    $out .= '<tr><td class="table-league-highlight">' . ($rk+1) . ". " . $row['kgsname'] . /* " (" . $row['uid'] . ')' . */ '</td><td class="table-league-score-highlight">' . $row['score'] . '</td>';
    foreach ($participants as $col) {
      $out .= '<td style="text-align: center;">' . visual_results_to_icons($row['visual'][strtolower($col['kgsname'])]) . '</td>';
    }
    $out .= '</tr>'; // no <br> here.
  }
  $out .= '</tbody></table></div>'."\n";
  
  return $out;
  
}

// The League Data Calc Function!
// call with event and room.
// returns $out, which is an array of:
// $out['participants'] --> score-sorted list of participants. includes ['score'], ['kgsname'], ['uid'] etc.
// $out['visual'] --> 2d array of wins and losses.
// $out['games'] --> list of games which made it thru the filter.
function calculate_room($event_id, $division) {
  global $debug;

  // Get all participants.
  $participants = DB::query("SELECT * FROM participants WHERE eid=%i AND division=%s;", $event_id, $division);

  if ($debug) echo "0. Participants: " . count($participants) . "\n<br>";

  //
  // 1.
  //
  // get all games they've played in league.
  // Note that if anyone only played black, we will still get their game via the white player if it was a league game.
  // So for each participant we need only check their games as white.
  $games = array();
  foreach($participants as $k => $participant) {
    // get games the participants played.
    $some_games = DB::query("SELECT * FROM sgf WHERE player_white=%s;", $participant['kgsname']);
    $games = array_merge($games, $some_games);
  }

    ////////////////////////////////////////////////////////////////////////////////
    // Filter out all non-league games
    ////////////////////////////////////////////////////////////////////////////////
    // Now we can go back and check to make sure each of these games is vs. a league player (as black).
    // This will cut out all games played with people who were not in the league.
    ////////////////////////////////////////////////////////////////////////////////
    $filtered_games = array();
    foreach ($games as $game) {
        foreach($participants as $participant) {
            // Note the use of strtolower to exact kgs standards compliance.
            // While kgs names appear case sensitive the lookup functions perform a case insensitive search.
            if (strcmp(strtolower($participant['kgsname']), strtolower($game['player_black'])) == 0) {
                // match.
                $filtered_games[] = $game;
                continue;
            }
        }
    }
    $games = $filtered_games;

    ////////////////////////////////////////////////////////////////////////////////
    // filter out reviews.
    ////////////////////////////////////////////////////////////////////////////////
    $filtered_games = array();
    foreach ($games as $game) {
        if (strcmp(strtolower($game['type']), "review") != 0) {
            // match "not review".
            $filtered_games[] = $game;
        }
    }
    $games = $filtered_games;
  
    ////////////////////////////////////////////////////////////////////////////////
    // filter by byo-yomi and 30s (25s whatever)
    ////////////////////////////////////////////////////////////////////////////////
    // (Allowing Canadian time for now, 20s+)
    ////////////////////////////////////////////////////////////////////////////////
    $filtered_games = array();
    foreach ($games as $game) {
        if ((strcmp($game['OTtype'], "byo-yomi") == 0) && ($game['OTmeta2'] >= 25)) {
            $filtered_games[] = $game;
        }
        if ((strcmp($game['OTtype'], "Canadian") == 0) && (($game['OTmeta2']/$game['OTmeta1']) >= 24)) {
            $filtered_games[] = $game;
        }
    }
    $games = $filtered_games;
    
    // debugging checkpoint
    //if ($debug) echo "4. Games inside byo-yomi/Canadian time restrictions: " . count($games) . "\n<br>";
  
  
    ////////////////////////////////////////////////////////////////////////////////
    // filter by date and #ASR
    $filtered_games = array();
    $dates = DB::queryFirstRow("SELECT date_starts,date_ends FROM events WHERE event_id=%i;", $event_id);
    $date_starts = strtotime($dates['date_starts']);
    $date_ends = strtotime($dates['date_ends']);

    foreach ($games as $game) {
        $game_date = strtotime($game['date']);
        if (($game_date >= $date_starts) && ($game_date <= $date_ends)) {
            $is_asr_game = ((strpos($game['sgf'], "#asr") !== false) || (strpos($game['sgf'], "#ASR") !== false));
            if ($is_asr_game) {
                $filtered_games[] = $game;
            }
        }
    }
    $games = $filtered_games;
  
    // debugging checkpoint
    //if ($debug) echo "5. #asr Games inside Start/End: " . count($games) . "\n<br>";
  
    ////////////////////////////////////////////////////////////////////////////////
    //
    // 2. POST FILTER ADDS.
    //
    ////////////////////////////////////////////////////////////////////////////////
    //
    // Add any games explicitly tagged as belonging to this event.
    // Such as, now, go thru and look for games tagged as being in this event and division.
    //
    ////////////////////////////////////////////////////////////////////////////////
    $event_tag = '#event:' . $event_id . ';';
    $division_tag = '#division:' . $division . ';';
    $some_games = DB::query("SELECT * FROM sgf WHERE tags like %ss and tags like %ss;", $event_tag, $division_tag);
    $games = array_merge($games, $some_games);

    // remove duplicates since we added by tags.
    $filtered_games = array();
    foreach ($games as $g) {
        $filtered_games[$g['data_key']] = $g;
    }
    $games = $filtered_games;
  
    ////////////////////////////////////////////////////////////////////////////////
    //
    // 3. DISPLAY & CALCULATION
    //
    ////////////////////////////////////////////////////////////////////////////////
    //
    // sort games by date played.
    //
    ////////////////////////////////////////////////////////////////////////////////
    usort($games, function ($a, $b) {
        if (strtotime($a['date']) == strtotime($b['date'])) {
            return 0;
        }
        return (strtotime($a['date']) < strtotime($b['date'])) ? -1 : 1;
    });

    // calculate points.
    // set player's points to zero.
    $score = array();
    $wins = array();
    $losses = array();
    $visual_results = array(); // stores order of wins/losses.
    foreach ($participants as $p) {
        $score[strtolower($p['kgsname'])] = 0;
        $visual_results[strtolower($p['kgsname'])] = array();
        foreach ($participants as $p_vs) {
            $wins[strtolower($p['kgsname'])][strtolower($p_vs['kgsname'])] = 0;
            $losses[strtolower($p_vs['kgsname'])][strtolower($p['kgsname'])] = 0;
            $visual_results[strtolower($p['kgsname'])][strtolower($p_vs['kgsname'])] = '';
        }
    }
  
    // Calculate points, matches, scores, etc.
    // step 1 calculate the variables and set up $matches and $score and other scratchpad variables.
    $event = DB::queryFirstRow("SELECT * FROM events WHERE event_id=%i;", $event_id);
    $points_per_win = array (0 => $event['points_per_win']);
    $points_per_loss = array (0 => $event['points_per_loss']);
    if (strpos($points_per_win[0], ",") != FALSE) {
        $points_per_win = explode(",", $event['points_per_win']);
    }
    if (strpos($points_per_loss[0], ",") != FALSE) {
        $points_per_loss = explode(",", $event['points_per_loss']);
    }
    $max_matches_per = max(count($points_per_win), count($points_per_loss));

    // for each game, tabulate who the winner is, add points, and record number of matches etc.
    foreach ($games as $k => $game) {
        $pw = strtolower($game['player_white']);
        $pb = strtolower($game['player_black']);
    
        if (!isset($game['result']) || ($game['result'] == null)) {
            if (strcmp($game['data_key'], "pull") != 0) {
                // If a downloaded game has no result pull it again on the next cycle.
                DB::query("DELETE FROM sgf WHERE id=%i;", $game['id']);
            } else {
                // The game hasn't been pulled yet so just ignore it.
                unset ($games[$k]);
            }
            continue;
        }
    
        $blackwin = $game['result'][0] == 'B';
        $whitewin = $game['result'][0] == 'W';
        //$out .= "Whitewin: " . ($whitewin ? "yes" : "no") . "  Blackwin: " . ($blackwin ? "yes" : "no") . "\n<br>"; // test wins
        // We can probably optimize out the need for these two queries using a table lookup. We have probably already pulled the data too.
        if (!isset($wins[$pw][$pb])) { $wins[$pw][$pb] = ""; }
        if (!isset($losses[$pw][$pb])) { $losses[$pw][$pb] = ""; }
        //echo $pw . ":" . $pb . "<br>\n"; //checkpoint
        $m = $wins[$pw][$pb] + $losses[$pw][$pb];
        if ($m < $max_matches_per) {
            // Allow this match. Now calc and add points.
            $winner_id = $pw;
            $loser_id = $pb;
            if ($blackwin) { $winner_id = $pb; $loser_id = $pw; }
      
            // get the wins and losses into a variable...
            if (!isset($wins[$winner_id][$loser_id])) { $wins[$winner_id][$loser_id] = 0; }
            if (!isset($losses[$loser_id][$winner_id])) { $losses[$loser_id][$winner_id] = 0; }
            $numwins = $wins[$winner_id][$loser_id];
            $numlosses = $losses[$loser_id][$winner_id];
           //echo "wins: " . $numwins . " -- id: " . $winner_id . "<br>\n"; //checkpoint

            // to add score based on howmany wins or losses they have 
            if (!isset($score[$winner_id])) { $score[$winner_id] = 0; }
            if (!isset($score[$loser_id])) { $score[$loser_id] = 0; }
            if (isset($points_per_win[$numwins])) { $score[$winner_id] += $points_per_win[$numwins]; }
            if (isset($points_per_loss[$numlosses])) { $score[$loser_id] += $points_per_loss[$numlosses]; }

            // then increment these variables by one.
            $wins[$winner_id][$loser_id] += 1;
            $losses[$loser_id][$winner_id] += 1;
            //echo "player: " . $winner_id . " -- score: " . $score[$winner_id] . "<br>\n"; //checkpoint
            // add visual results, too.
            if (!isset($visual_results[$winner_id][$loser_id])) { $visual_results[$winner_id][$loser_id] = ''; }
            if (!isset($visual_results[$loser_id][$winner_id])) { $visual_results[$loser_id][$winner_id] = ''; }
            $visual_results[$winner_id][$loser_id] .= "o";
            $visual_results[$loser_id][$winner_id] .= "x";

            // So in summary since games are date-sorted, only the first games played will be counted.
            // Once points_per_win/points_per_loss are exhausted games will simply not add points.
        } // if there aren't too many matches between these two already.
    } // for every game remaining in the filter.

   $num_active=0;
   foreach ($participants as $pk => $p) {//add nwins nlosses and active to participants and count actives
	$win_p=array_sum($wins[strtolower($p['kgsname'])]);
	$loss_p=array_sum($losses[strtolower($p['kgsname'])]);
	$p_is_active=0;
	if (($win_p +$loss_p) >3){
		$p_is_active=1;
		$num_active +=1;
	}
	$participants[$pk]['nwins']=$win_p;
	$participants[$pk]['nlosses']=$loss_p;
	$participants[$pk]['active']= $p_is_active;
   }


    // add score to participants (nasty double loop!)
    foreach ($participants as $pk => $p) {
        foreach ($score as $sk => $s) {
            if (strcmp(strtolower($sk), strtolower($p['kgsname']))==0) {
                // Note strtolower -- case insensitive for KGS name comparison.
                // if the uid key in the score list matches the uid of a participant, set that participant's score.
                $participants[$pk]['score'] = $s;
                break; // still a double loop but at least we save some time.
            }
        }
    }
  
    // sort participants by score.
    usort($participants, function ($a, $b) {
        if ($a['score'] == $b['score']) {
            return 0;
        }
        return ($a['score'] > $b['score']) ? -1 : 1;  // greatest to least; < means least to greatest.
    });

    $out = array();

    // add visual results to participants.
    foreach ($participants as $rk => $row) {
        foreach ($participants as $ck => $col) {
            if (!isset($participants[$rk]['visual'])) {
                $participants[$rk]['visual'] = array();
            }
            $participants[$rk]['visual'][strtolower($col['kgsname'])] = $visual_results[strtolower($row['kgsname'])][strtolower($col['kgsname'])];
        }
        $participants[$rk]['visual'][strtolower($row['kgsname'])] = "--";
    }

    // add $out['participants'];
    $out['participants'] = array();
    foreach ($participants as $k => $sp) {
        $out['participants'][] = $sp;
    }

    // add $out['games']
    $out['games'] = array();
    foreach ($games as $fg) {
        $out['games'][] = $fg['id'];
    }

   // add $out['num_active'] (numberof actives players)
   $out['num_active'] = $num_active;

    return $out;
}


// functions like this only usually need to be run once, when the spec for the related fields changes.
// during development tests seemed to indicate ~300 games per second processing time.
function fix_OTtype_shim() {
  $debug = false;
  
  $r = DB::query("SELECT id,sgf FROM sgf LIMIT 1000;");
  $skip = 0;
  $affected = 0;
  while ($r != null) {
    $r = DB::query("SELECT id,OTtype,sgf FROM sgf LIMIT 1000 OFFSET %i;", $skip);
    
    foreach($r as $game) {
      // fix OTtype etc.
      if ($debug) echo "Fixing " . $game['id'] . " old OTtype: " . $game['OTtype'];
      $sgf = new SGF();
      $sgf->scan($game['sgf']); // recalculate game info including TM, OT, etc.
      $u = array ( 'TM' => $sgf->prop['TM'],
                   'OT' => $sgf->prop['OT'],
                   'OTtype' => $sgf->prop['OTtype'],
                   'OTmeta1' => $sgf->prop['OTmeta1'],
                   'OTmeta2' => $sgf->prop['OTmeta2']
      ); // $u
      if ($debug) echo " --> new OTtype: " . $sgf->prop['OTtype'] . "\n<br>";
      DB::update("sgf", $u, "id=%i", $game['id']); // write it out
      $affected += DB::affectedRows();
    }
    $skip +=1000; // look at next 1000 games.
  }
  return $affected;
}

function visual_results_to_icons($vr) {
  $out = '';
  $win_icon = '<i class="fa fa-circle text-primary"></i>';
  $loss_icon = '<i class="fa fa-circle-o text-primary"></i>';
  $doublewin = $win_icon . " " . $win_icon;
  $doubleloss = $loss_icon . " " . $loss_icon;
  $winloss = $win_icon . " " . $loss_icon;
  $losswin = $loss_icon . " " . $win_icon;
  $player_diagonal = '<i class="fa fa-times"></i>';
  
  switch ($vr) {
    case 'x':
      $out = $loss_icon;
      break;
    case 'o':
      $out = $win_icon;
      break;
    case 'xx':
      $out = $doubleloss;
      break;
    case 'xo':
      $out = $losswin;
      break;
    case 'ox':
      $out = $winloss;
      break;
    case 'oo':
      $out = $doublewin;
      break;
    case '--':
      $out = $player_diagonal;
      break;
    default:
      $out = '';
      break;
  }
  
  return $out;
}


function matches_played($rco) {
  $out = '';
  
  $total = 0;
  $played = 0;
  foreach ($rco['participants'] as $p1) {
    foreach ($rco['participants'] as $p2) {
        // if the names are different
      if (strcmp (strtolower($p1['kgsname']),strtolower($p2['kgsname'])) != 0) {
        $total += 2;
      }
    }
  }
  $played = count ($rco['games']);
  $total = $total / 2;
  if ($total < 1) $total = 1;
  $percent = floatval(100*$played/$total);
  $barcolor = "progress-bar-danger";
  if ($percent >= 20) $barcolor = "progress-bar-warning";
  if ($percent >= 30) $barcolor = "progress-bar-info";
  if ($percent >= 40) $barcolor = "progress-bar-primary";
  if ($percent >= 50) $barcolor = "progress-bar-success";
  $percent = number_format(100*$played/$total, 1);
  
  $out .= '<div class="progress" style="min-height: 35px; margin-bottom: 0px;">'; // <!-- margin-bottom was -7px, seems to have changed. Well, our CSS and such is different now. -->
  $out .= '<div class="progress-bar ' . $barcolor . ' progress-bar-striped" role="progressbar" aria-valuenow="' . $played . '" aria-valuemin="0" aria-valuemax="' . $total . '" style="min-width: 30rem; width: ' . $percent . '%;">';
  $out .= '<h5>' . $percent . '% Matches Played (' . $played . '/' . $total . ')</h5>';
  $out .= '</div>';
  $out .= '</div>';

  return $out;
}

function fetch_availability_times ($current_event, $my_room) {
  $times = DB::query("SELECT uid,available FROM participants WHERE eid=%i and division=%s ORDER BY available DESC;", $current_event, $my_room);
  $times_data = "";
  foreach ($times as $t) {
    $p_name = DB::queryFirstField("SELECT kgsname FROM members WHERE uid=%i;", $t['uid']);
    $t = time()-$t['available'];
    $tword = ' (' . $t . " sec.)";
    if ($t == 0) { $t = $t / 60; $tword = ' (now)'; }
    if ($t == 3) { $t = $t / 60; $tword = ' -- <a href="https://www.youtube.com/watch?v=PQYbFdSu-F4">ただいまかえりました (easter egg)</a>'; }
    if ($t > 60) { $t = $t / 60; $tword = ' (' . number_format($t,0) . " min.)"; }
    if ($t > 60) { $t = $t / 60; $tword = ' (' . number_format($t,0) . " hrs.)"; }
    if ($t > 24) { $t = $t / 24; $tword = ' (' . number_format($t,0) . " days)"; }
    if ($t > 100) { $tword = ""; }
  
    $times_data .= $p_name  . $tword . "\n<br>";
  }
  return $times_data;
}
  
?>
