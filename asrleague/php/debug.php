<?php
//
// debug.php for tsumego.ca v0.5
// (C) 2015 Akisora Corporation
//
// A library of methods used to help debug stuff.
//

function dumpsession($tag = "session: ")
{
	echo $tag . session_id() . "<br>";
	echo "[scd: '" . ini_get('session.cookie_domain') . "']<br>";
	foreach ($_SESSION as $key=>$val)
	{
		echo $tag . "[ " . $key . " : " . $val . " ]<br>";
	}
	return;
}

// "version 2.0" shows newline on recursive calls and adds open_list and close_list.
function display_tree($array, $newline = "<br>", $open_list = "<ul>", $close_list = "</ul>") {
	$output = "";

    foreach($array as $key => $value) {    //cycle through each item in the array as key => value pairs
        if (is_array($value) || is_object($value)) {        
            //if the VALUE is an array, then
            //call it out as such, surround with brackets, and recursively call displayTree.
            $value = "Array()" . $newline . $open_list . display_tree($value, $newline, $open_list, $close_list) . $close_list . $newline;
        }

        //if value isn't an array, it must be a string. output its' key and value.
        $output .= "[$key] => " .$value . $newline;
    }
    return $output;
}

// "version 2.0" shows newline on recursive calls and adds open_list and close_list.
function display_tree2($array, $newline = "<br>", $open_list = "<ul>", $close_list = "</ul>") {
	$output = "";

    foreach($array as $key => $value) {    //cycle through each item in the array as key => value pairs
        if (is_array($value) || is_object($value)) {        
            //if the VALUE is an array, then
            //call it out as such, surround with brackets, and recursively call displayTree.
            $value = "Array()" . $newline . $open_list . display_tree($value, $newline, $open_list, $close_list) . $close_list . $newline;
        }

        //if value isn't an array, it must be a string. output its' key and value.
        $output .= "[$key] => " . cleanstr($value) . $newline;
    }
    return $output;
}

?>
