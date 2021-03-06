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
if (isset($req['msg'])) { $msg = substr($req['msg'], 0, 256); }

echo bootstrap_head("Advanced Study Room -- Games Archive");
?>


<body>
    <div class="row">
        <div class="col-md-1">
            <!-- left sidebar -->
    </div> <!-- col-md-1 -->
        <div class="col-md-10">
  <?php echo bootstrap_navbar('games'); ?>
        <!-- Jumbotron -->
        <div class="jumbotron well">
          <h1>The Archives</h1>
          <p>A database of the games played in the league. You can also upload games here if they are not found by the automatic match finder.</p>
        </div> <!-- /jumbotron -->
        <?php if (strlen($msg)>0) { echo '<div class="alert alert-success" role="alert">' . htmlentities($msg) . '</div>';} ?>
<?php if ( is_user_logged_in() && current_user_can('asr_edit_events')) { ?>
        
        <h2>Add Game Records</h2>
          <form action="action.php" method="post" enctype="multipart/form-data"><table><tr><td><input type="hidden" name="action" value="upload_sgf"><input type="file" id="upload_sgf" name="upload_sgf"></td><td><button type="submit" role="button" class="btn btn-sm btn-success">Send</button></td></tr></table></form>
          </form>
        <br />
<?php } ?>
        <h2>Records Archive</h2>
        <?php
          $games = DB::query("SELECT urlto,id,pstatus,data_key,player_white,player_black,date,place,type,result FROM sgf ORDER BY date LIMIT 10000;");
          $t = '<div class="table-responsive">'."\n";
          $t .= '<table class="table" id="table-players">'."\n";
          $t .= '<thead><tr><th>record no.</th><th>Date</th><th>Place</th><th>White</th><th>Black</th><th>Type</th><th>Result</th></tr></thead>'."\n";
          $t .= '<tbody>'."\n";
          foreach ($games as $game) {
            if ($game['pstatus'] > 0) continue; // don't display games which haven't been processed yet.
            $t .= '<tr>';
            $t .= '<td><a href="http://eidogo.com/#url:' . $game['urlto'] . '">' . $game['id'] . '</a></td>'."\n";
            $t .= '<td>' . date("Y-m-d H:i", strtotime($game['date'])) . '</td>'."\n";
            $t .= '<td>' . $game['place'] . '</td>'."\n";
            $t .= '<td>' . $game['player_white'] . '</td>'."\n";
            $t .= '<td>' . $game['player_black'] . '</td>'."\n";
            $t .= '<td>' . $game['type'] . '</td>'."\n";
            $t .= '<td>' . $game['result'] . '</td>'."\n";
            $t .= '</tr>';
          }
          $t .= '</tbody>'."\n";
          $t .= '</table>';
          $t .= '</div>';
        
          echo $t;
        ?>

  <!-- Site footer -->
  <footer class="footer">
    <hr>
    <p>&copy; 2016 advancedstudyroom.org</p>
  </footer>

        </div> <!-- col-md-10 -->
    <div class="col-md-1">
    </div> <!-- col-md-1 -->
    </div> <!-- row-->
  <?php echo bootstrap_core_js(); ?>
        
  <!-- inline script for local page -->
  <script>
    $(document).ready(function() {
      // Start
      $('#table-players').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     true,
        "order": [[ 6, "desc" ]] /* this puts the most recent ending date first so we can see what events are active.*/
      });
    });
  </script>
</body>
</html>
