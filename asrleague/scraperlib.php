<?php
//
// scraperlib.php
// (C) 2016 Akisora Corporation
//
// Since this is the second pass I will be refactoring "support functins" into this file, to keep the scraper logic CLEAN and ez to see.
//

// Gives you the next kgsname to scrape.
function scraper_find_next_player($event_id) {
    $row = DB::queryFirstRow("SELECT * FROM participants WHERE eid=%i AND scrape_priority > %i", $event_id, 0);
    if (count($row) == 0) {
        DB::query("UPDATE participants SET scrape_priority=1 WHERE eid=%i", $event_id);
        $row = DB::queryFirstRow("SELECT * FROM participants WHERE eid=%i AND scrape_priority > %i", $event_id, 0);
    }
    return $row;
}

// marks a player scraped so the "next" function (above) wont pull it until everyone else has had a go.
function scraper_mark_player_scraped($event_id, $kgsname) {
    DB::query("UPDATE participants SET scrape_priority=0 WHERE eid=%i AND kgsname=%s", $event_id, $kgsname);
}

// Will return an URL to a sgf file if one exists in the pull requests list.
function scraper_find_pull_request(){
    $row = DB::queryFirstRow("SELECT * from sgf WHERE pstatus=%i", 2); // 2 = PSTATUS_PULL_REQUEST. See schema.php
    return $row;
}

// adds a link to pull later.
function scraper_add_pull_request($url) {
    $row = array(
        'urlto' => $url,
        'pstatus' => 2,
        'data_key' => 'pull_request'
    );
    DB::insert("sgf", $row);
    return;
}



?>
