<?php
// just download a game and add a sgf to the db
    error_reporting(E_ALL);
    ini_set('display_errors','On');
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/db/mdb.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/wplib.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/asrleague/scraperlib.php';

if ( is_user_logged_in() && current_user_can('asr_edit_events') ) {

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["upload_sgf"]["name"]);
$uploadOk = 1;
$sgfFileType =pathinfo($target_file,PATHINFO_EXTENSION);
// Check if file already         exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
        // Check file size
if ($_FILES["upload_sgf"]["size"] > 50000) {
            echo "Sorry, your file is too large.";
    $uploadOk = 0;
        }
// Allow certain file formats
if($sgfFileType != "sgf") {
            echo "Sorry, only SGF files are allowed.";
            $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if         ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
            if (move_uploaded_file($_FILES["upload_sgf"]["tmp_name"], $target_file)) {
                echo "The file ". basename( $_FILES["upload_sgf"]["name"]). " has been uploaded.";
    } else {
                echo "Sorry, there was an error uploading your file.";
		$uploadOk=0;
            }
}
// if file has been upload, add pull request.
if ($uploadOk==1){
 scraper_add_pull_request($target_file);
}

}// user can edit event


?>
