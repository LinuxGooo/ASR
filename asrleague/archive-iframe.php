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
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/bootstrap.php';

// messages
$req = array_merge($_GET, $_POST);
$msg = '';

echo bootstrap_head("Advanced Study Room -- Games Archive");
if (isset($req['msg'])) { $msg = substr($req['msg'], 0, 256); }
?>


<body>
        <div class="">
<?php if ( is_user_logged_in() && current_user_can('asr_edit_events') ) { ?>
        <h2>Add Game Records</h2>
          <form action="action.php" method="post" enctype="multipart/form-data"><table><tr><td><input type="hidden" name="action" value="upload_sgf"><input type="file" id="upload_sgf" name="upload_sgf"></td><td><button type="submit" role="button" class="btn btn-sm btn-success">Send</button></td></tr></table></form>
          </form>
        <br />
<?php } ?>
        <h2>Records Archive</h2>
        <?php
          $games = DB::query("SELECT id,pstatus,data_key,player_white,player_black,date,place,type,result FROM sgf ORDER BY date LIMIT 10000;");
          $t = '<div class="table-responsive">'."\n";
          $t .= '<table class="table" id="table-players">'."\n";
          $t .= '<thead><tr><th>Date</th><th>White</th><th>Black</th><th>Result</th></tr></thead>'."\n";
          $t .= '<tbody>'."\n";
          foreach ($games as $game) {
            if ($game['pstatus'] > 0) continue; // don't display games which haven't been processed yet.
$link="javascript:window.top.change_iframe(" . $game['id'] . ");";
            $t .= '<tr>'."\n";
            $t .= '<td>'. date("Y-m-d H:i", strtotime($game['date'])) . '</td>'."\n";
            $t .= '<td>' . $game['player_white'] . '</td>'."\n";
            $t .= '<td>' . $game['player_black'] . '</td>'."\n";
            $t .= '<td>' .' <a href="'.$link .'">'. $game['result'] . '</a></td>'."\n";
            $t .= '</tr>';
          }
          $t .= '</tbody>'."\n";
          $t .= '</table>';
          $t .= '</div>';
        
          echo $t;
        ?>


        </div> <!-- col-md-10 -->
  <?php echo bootstrap_core_js(); ?>


  <!-- inline script for local page -->
  <script>
    $(document).ready(function() {
      // Start
      $('#table-players').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     false,
        "order": [[ 0, "desc" ]] /* this puts the most recent ending date first so we can see what events are active.*/
      });
    });
  </script>
</body>
</html>
