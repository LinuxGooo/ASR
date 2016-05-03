<?php
// ncrypt.php v1.2
// (C) 2012 Akisora Corporation
//
// A library of strong encryption and hash algorithms which is easy to use.
//

function ncrypt($decrypted="", $password="pointbagtrunkwrong", $salt="simplysticktwicebark") {
	// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
	$key = hash('SHA256', $salt . $password, true);

	// Build $iv and $iv_base64.
	srand();

	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_SERPENT, MCRYPT_MODE_CBC), MCRYPT_RAND);

	if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22)
	{
		return false;
	}

	// Encrypt $decrypted and an MD5 of $decrypted using $key.
	// MD5 is fine to use here because it's just to verify successful decryption.
	$encrypted = base64_encode(
		mcrypt_encrypt(
			MCRYPT_SERPENT,
			$key,
			$decrypted . md5($decrypted),
			MCRYPT_MODE_CBC,
			$iv
		)
	);

	// We're done!
	return $iv_base64 . $encrypted;
}

function dcrypt($encrypted="", $password="pointbagtrunkwrong", $salt="simplysticktwicebark") {
	// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
	$key = hash('SHA256', $salt . $password, true);
	
	// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
	$iv = base64_decode(substr($encrypted, 0, 22) . '==');
	
	// Remove $iv from $encrypted.
	$encrypted = substr($encrypted, 22);
	
	// Decrypt the data.
	// rtrim won't corrupt the data because the last 32 characters are the
	// md5 hash; thus any \0 character has to be padding.
	$decrypted = rtrim(
		mcrypt_decrypt(
			MCRYPT_SERPENT,
			$key,
			base64_decode($encrypted),
			MCRYPT_MODE_CBC,
			$iv
		),
		"\0\4"
	);

	// Retrieve $hash which is the last 32 characters of $decrypted.
	$hash = substr($decrypted, -32);

	// Remove the last 32 characters from $decrypted.
	$decrypted = substr($decrypted, 0, -32);

	// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
	if (md5($decrypted) != $hash)
		return false;

	// Yay!
	return $decrypted;
}

function isHashAvailable($h)
{
	foreach (hash_algos() as $v)
	{
		if (strcasemp($v, $h) == 0)
		{
			return true;
		}
	}
	
	return false;
}

function hashpw($pw, $algorithm = "ripemd320", $salt = "")
{
	$r = hash($algorithm, $pw . $salt, false);
	return $r;
}

function checkpw($pw, $hash, $n = 0, $algorithm = "ripemd320", $salt = "")
{
	if ($n == 0)
	{
		$n = 512;
	}

	$hash = substr($hash, 0, $n);
	$hash2 = substr(hashpw($pw, $algorithm, $salt), 0, $n);
	if (strcasecmp($hash, $hash2) == 0)
	{
		return true;
	}

	// fallthrough
	return false;
}

// returns a formtag which is the hash of the session ID.
function formtag()
{
	$hash = hashpw(session_id(), "ripemd320", "formtag");
	$hash = substr($hash, 16, 32);
	return $hash;
}

// returns true if $s is a hash of the session ID.
function checkformtag($s)
{
	$hash = hashpw(session_id(), "ripemd320", "formtag");
	$hash = substr($hash, 16, 32);
	if (strcmp($s, $hash) == 0)
	{
		return true;
	}
	
	return false;
}


?>
