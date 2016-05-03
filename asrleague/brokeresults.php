<?php
error_reporting(E_ALL); //error_reporting(-1);
ini_set('display_errors ', TRUE);
ini_set('display_startup_errors ', TRUE);

// just show results from cash
//
echo '<h1> ASR safe mode results </h1>';
echo '<p> We are sorry the website is down. In the meantime, you can check what class you are in .</p>';
echo '<p>Those results are not up to date but the class are.</p>';

$rooms = array('Alpha','Beta','Gamma', 'Delta', 'Placement League');
foreach ($rooms as $room){
$out=uncache_results(2,$room);
$out=display_room($out);
echo '<h2>'. $room . '</h2>';
echo $out;
}

function uncache_results($event_id,$division){
        $room_name=str_replace(" ", "_", $division);
        $file= $_SERVER['DOCUMENT_ROOT'] . '/asrleague/cache/'.$event_id. $room_name .'.bin';
        $calc_out=unserialize(file_get_contents($file));
        return $calc_out;
}


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
      $out .= '<td style="text-align: center;">'  . '</td>';
    }
    $out .= '</tr>'; // no <br> here.
  }
  $out .= '</tbody></table></div>'."\n";
  
  return $out;

  
}




?>
