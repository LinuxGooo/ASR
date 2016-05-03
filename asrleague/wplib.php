<?php
//
// wplib.php v0.6
// (C) 2016 Akisora Corporation
//
// Starts session, checks wordpress, puts relevant data into session.
//


////////////////////////////////////////////////////////////////////////////
// Step 1: Load WordPress.
require_once $_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php';


////////////////////////////////////////////////////////////////////////////
// Step 1b: Start the session.
// If this causes an E_NOTICE or whatever, remove it.
// The issue is whether or not wp-load starts a session, I don't know.
// In any case it will be ignored if wp-load already started a session.
session_start();


////////////////////////////////////////////////////////////////////////////
// Step 1c: Nice environment
ini_set('memory_limit', '512M');
ini_set('session.gc_maxlifetime', 14400000);
ini_set('session.cookie_lifetime', 14400000);
ini_set('session.gc_divisor', 1000);
//ini_set('session.use_cookies', 0);
ini_set('max_execution_time', 30);


////////////////////////////////////////////////////////////////////////////
// Step 2: Are we logged in to WordPress? get WP user data.
$user = wp_get_current_user();
if ($user->exists()) {
  $_SESSION['user'] = $user;
  $_SESSION['uid'] = $user->ID;
} else {
  $_SESSION['user'] = 0;
  $_SESSION['uid'] = 0;
}



////////////////////////////////////////////////////////////////////////////
/// function library
////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////
// wp_get_role_names()
//
// get a list of role names.
//
function wp_get_role_names() {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    return $wp_roles->get_names();
}

?>
