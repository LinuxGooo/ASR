<?php
//
// makealpha.php v2.2 (now with preg not ereg)
// (C) 1999, 2011 Akisora Corporation
//
// A library of input cleaners.

// from http://www.ibm.com/developerworks/opensource/library/os-php-secure-apps/index.html
function isValidFileName($file) {
    /* don't allow .. and allow any "word" character \ / */
    return preg_match('/^(((?:\.)(?!\.))|\w)+$/', $file);
}

// make alphanumeric
// Strips everything which is not an alphanumeric character.
// i.e. returns the original string minus all non [a-z][A-Z][0-9]
function makealpha($s){
  $r = substr($s, 0, 1024);
  $r = preg_replace('/[^a-zA-Z0-9]/i', '', $r);
  return ($r); }

// make safe alphanumeric
// but keeps (some) safe string chars esp. for numbers (plus and minus,
// period and underscore) intact.
function makesalpha($s)
{
  $r = substr($s, 0, 1024);
  $r = preg_replace('/[^\.\+\!\:\-\_\@\#a-zA-Z0-9 ]/i', '', $r);
  return ($r);
}

// make e-mail alphanumeric
// Strips everything which does not belong in an email address.
function makeealpha($s)
{
  $rval = preg_replace('([^_\-\+.@a-zA-Z0-9])', '', $s);
  return ($rval);
}

function isemail($email)
{
 if(!eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$', $email))
   return false;
 else
   return true;
}

// meekrodb escapes everything so we just want to clean the html codes now.
function scape($s)
{
	@ $x = html_entity_decode($s);
	while (@ strcmp ($x, html_entity_decode($x)) != 0)
	{
		@ $x = html_entity_decode($x);
	}

	@ $y = htmlentities($x);
	
	return ($y);
}

function clean_echo($s)
{
	echo cleanstr(s);
}

function cleanstr($s)
{
	 @ $a = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
	 @ $b = htmlentities($a, ENT_QUOTES, 'UTF-8');
	 return $b;
}

function cleanstr_br($s)
{
	return str_replace("&lt;br&gt;", "<br>", cleanstr($s));
}

// from: http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function strposq($haystack, $needle, $offset = 0) {
	$len = strlen($haystack);
	$charlen = strlen($needle);
	$flag1 = false;
	$flag2 = false;
	for($i = $offset; $i < $len; $i++){
		if(substr($haystack, $i, 1) == "'") {
			$flag1 = !$flag1 && !$flag2 ? true : false;
		}
		if(substr($haystack, $i, 1) == '"') {
			$flag2 = !$flag1 && !$flag2 ? true : false;
		}
		if(substr($haystack, $i, $charlen) == $needle && !$flag1 && !$flag2) {
			return $i;       
		}
	}
	return false;
} 

// returns true if $needle is a substring of $haystack
function contains($needle, $haystack)
{
    return strpos($haystack, $needle) !== false;
}

?>
