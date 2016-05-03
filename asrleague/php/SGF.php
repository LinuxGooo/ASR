<?php
// SGF.php
// (C) 2015 Akisora Corporation
//
// SGF class for holding SGF files and extracting info.
//
//

require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/makealpha.php'; // for endswith for finding byo-yomi.
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/php/ncrypt.php'; // for calculation of data keys.
//require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/schema.php';
//drop_table_sgf();
//create_table_sgf();

class SGF {
  // property declaration
  public $id; // our ID if we know it.
  public $sgf; // the SGF we represent.
	public $info_str; // the string used to calculate $info_key.
	public $info_key;  // A hash of the game info (players, date, location, rules, etc.)
	public $data_key;  // A hash of the entire string.
	public $game_key;  // A hash of the main branch list of moves.
  public $tags; // tags like asr, pro, etc.

	// Scanned Properties
  public $prop;

	// constructor
	function __construct() {
    $this->clear(); // clear data
	}

	// clears the scanned properties.
	public function clear() {
		$this->sgf = '';
    $this->id = 0; // this means insert new.
		$this->prop = array();
	  $this->prop['GM'] = ''; // game type (1=go)
	  $this->prop['FF'] = ''; // file format (4 is most recent i think)
    $this->prop['CA'] = ''; // charset
	  $this->prop['AP'] = ''; // Application ex. [SmartGo:1.0]
    $this->prop['ST'] = ''; // Defines how variations should be shown (this is needed to synchronize the comments with the variations).
    $this->prop['RU'] = ''; // rules ex. Japanese
	  $this->prop['SZ'] = ''; // board size ex. 19
    $this->prop['KM'] = ''; // komi
	  $this->prop['TM'] = ''; // main time in seconds
    $this->prop['OT'] = ''; // overtime ex. 5x30 byo yomi
    $this->prop['OTtype'] = '';
    $this->prop['OTmeta1'] = '';
    $this->prop['OTmeta2'] = '';
	  $this->prop['PW'] = ''; // player white name
	  $this->prop['PB'] = ''; // player black name
    $this->prop['WR'] = ''; // white rank ex. 5k
	  $this->prop['BR'] = ''; // black rank ex 6k
	  $this->prop['DT'] = ''; // date played YYYY-MM-DD
    $this->prop['PC'] = ''; // place
	  $this->prop['RE'] = ''; // result ex. B+
		$this->prop['type'] = ''; // game type ex. ranked
    $this->tags = ''; //clear the tags field.

		// other properties we want to exist by default.
		$this->prop['urlto'] = '';
		$this->prop['filename'] = '';
   return;
	}
	
	// scan the sgf for properties.
	public function scan($sgf)
  {
    $this->sgf = $sgf;
    $len = strlen($sgf);
		$sgf = str_split($sgf);
    $i = 0;
    $tag = '';
    $value = '';
    while ($i < $len) {
      if ($sgf[$i] != '[') {
        if (($sgf[$i] != ';') && ($sgf[$i] != ' ')) {
          // no spaces or ; needed for scan.
          $tag .= $sgf[$i];
        } // if not space or ;
      } else {
        // process brackets
        $i++;
        while ($sgf[$i] != ']') {
          if ($sgf[$i] == '\\') {
            $i++; // skip to next char.
          } // if
          $value .= $sgf[$i];
          $i++;
        } // while
        // value processed.
        // ignore B and W tags (stone placement), C (comment) and some others.
        $tag = trim($tag);

        if ((strlen($tag)>0)
          && (strcmp($tag, "B") != 0)
        	&& (strcmp($tag, "BL") != 0)
          && (strcmp($tag, "W") != 0)
          && (strcmp($tag, "WL") != 0)
          && (strcmp($tag, "C") != 0)
          && (strcmp($tag, "C") != 0)) {
          $this->prop[$tag] = $value;
        }
        $tag = '';
        $value = '';
	} // else // if [
	$i++;
    } // while

		$this->derive_info();
		$this->derive_keys();
		return true;
  } // function

	function encode2($str) {
    $str = mb_convert_encoding($str , 'UTF-32', 'UTF-8');
    $t = unpack("N*", $str);
    $t = array_map(function($n) { return "&#$n;"; }, $t);
    return implode("", $t);
  }

	function getprop($p) {
    if (isset($this->prop[$p])) {
      return $this->prop[$p];
    }
    return null;
	}


	function derive_info() {
    global $debug;

    // clear all values.
    $this->prop['blackwins'] = false;
    $this->prop['whitewins'] = false;
    $this->prop['overtime'] = '';
    $this->prop['periods'] = '';
    $this->prop['seconds'] = '';

    // Derive winner.
    $this->prop['winner'] = substr($this->prop['RE'], 0, 1);
    if ($this->prop['winner'] == 'B') { 
      $this->prop['blackwins'] = true;
      $this->prop['whitewins'] = false;
    }
    if ($this->prop['winner'] == 'W') { 
      $this->prop['blackwins'] = false;
      $this->prop['whitewins'] = true;
    }

    if ($this->prop['blackwins']) { $this->prop['winner'] = $this->prop['PB']; }
    if ($this->prop['whitewins']) { $this->prop['winner'] = $this->prop['PW']; }

    // Derive info about game time.
    // Basically we want to know byo-yomi settings or if it's not byo-yomi.

    if (endsWith($this->prop['OT'], "byo-yomi")) {
      // ex. "5x30 byo-yomi"
      $a = explode("x", $this->prop['OT'], 2);
      $p = $a[0];
      $a = explode(" ", $a[1], 2);
      $s = $a[0];
      $t = $a[1];
      $this->prop['OTmeta1'] = $p;
      $this->prop['OTmeta2'] = $s;
      $this->prop['OTtype'] = $t;
    } else if (endsWith($this->prop['OT'], "Canadian")) {
      // ex. "25/600 Canadian"
      $a = explode("/", $this->prop['OT'], 2);
      $p = $a[0];
      $a = explode(" ", $a[1], 2);
      $s = $a[0];
      $t = $a[1];
      $this->prop['OTmeta1'] = $p;
      $this->prop['OTmeta2'] = $s;
      $this->prop['OTtype'] = $t;  
    } else {
      $this->prop['OTtype'] = 'unrecognized';
      $this->prop['OTmeta1'] = '';
      $this->prop['OTmeta2'] = '';
    }

    //done!
    return;
	}


	public function derive_keys() {
		// Calculate game info string.
		$info = 'Game Info String v0.6'; // some salt.
    $info .= ':' . $this->prop['RU']; // rules ex. Japanese
	  $info .= ':' . $this->prop['SZ']; // board size ex. 19
    $info .= ':' . $this->prop['KM']; // komi
	  $info .= ':' . $this->prop['TM']; // main time in seconds
    $info .= ':' . $this->prop['OT']; // overtime ex. 5x30 byo yomi
	  $info .= ':' . $this->prop['PW']; // player white name
	  $info .= ':' . $this->prop['PB']; // player black name
    $info .= ':' . $this->prop['WR']; // white rank ex. 5k
	  $info .= ':' . $this->prop['BR']; // black rank ex 6k
	  $info .= ':' . $this->prop['DT']; // date played YYYY-MM-DD
    $info .= ':' . $this->prop['PC']; // place
		$info .= ':' . $this->prop['type'];
		if (strcmp($this->prop['PC'], "The KGS Go Server at http://www.gokgs.com/") == 0)
		{
			// if the urls from kgs are different, they are different games.
			$info .= ":" . $this->prop['urlto'];
		}
	  $info .= ':' . $this->prop['RE']; // result ex. B+
    $this->info_str = $info;
    $this->info_key = hash("ripemd160", $info, false);
    $this->data_key = hash("ripemd160", $this->sgf, false);
    $this->game_key = null; // We don't have a parser capable of traversing the main tree yet, so we cannot calculate this value.
	}

	// returns positive id of inserted row
	// or 0 if no insert id.
	function save_to_db($new_id = 0) {

    // Add to DB.
		$d = array(
      'info_key' => $this->info_key,
      'data_key' => $this->data_key,
      'game_key' => $this->game_key,
      'date' => $this->prop['DT'],
      'place' => $this->prop['PC'],
      'type' => $this->prop['type'],
			'result' => $this->prop['RE'],
      'TM' => $this->prop['TM'],
      'OT' => $this->prop['OT'],
      'OTtype' => $this->prop['OTtype'],
      'OTmeta1' => $this->prop['OTmeta1'],
      'OTmeta2' => $this->prop['OTmeta2'],
      'player_white' => $this->prop['PW'],
      'player_black' => $this->prop['PB'],
      'white_rank' => $this->prop['WR'],
      'black_rank' => $this->prop['BR'],
      'sgf' => $this->sgf,
			'urlto' => $this->prop['urlto'],
			'filename' => $this->prop['filename'],
      'tags' => $this->tags
	  );
		
    if ($new_id > 0) {
			$this->id = $new_id;
		}
    if ($this->id > 0) {
      $d['id'] = $this->id;
    }

    DB::insertUpdate("sgf", $d);

		$iid = DB::insertId();
		if ($iid > 0) { $this->id = $iid; }

    return $this->id;
  }
	
}

?>
