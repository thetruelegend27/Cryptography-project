<?php
/*
 * This file is the startup logic of every single page of this website.
 */

// Initialize all environmental variables and definitions.
require_once('./includes/init.php');

// MimeType output, in case if it's not text/html.
if ( !defined('NOHEADER') ) {
	if ( defined('CONTENT_TYPE') )
		header('Content-type: '.CONTENT_TYPE);
	else
		header('Content-type: text/html');
}

// Using HTML headers to tell the browsers NOT caching this page.
if ( !defined('NOHEADER') && defined('KEEPFRESH') ) {
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Cache-Control: no-cache, must-revalidate"); 
header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
header("Pragma: no-cache"); 
}

// Filter all $_POST and $_GET variables and convert special charaters.
// All POST and GET key - value pairs will be stored in $_INPUT.
// --------------------------------------------------------------------
// NOTE: This is very IMPORTANT to prevent SQL injection.
// --------------------------------------------------------------------
require_once('./includes/functions.php');
$_INPUT = init_input();


// Use Gzip to compress page output for bandwidth saving.
if ( !defined('NOGZIP') )
	ob_start('ob_gzhandler');

// Initialize an USER OBJECT named $ME as an instance of the browsing user.
if ( !defined('NOUSER') ) {
	require_once('includes/ob.char.me.php');
	$ME = new UserME();
}

// Checking if the user is logged in (having valid User ID loaded).
// Only pages defined ALLOW_GUEST can be accessed by those who did not,
// which typically the index and login page.
if ( !defined('ALLOW_GUEST') && !defined('NOUSER') && !$ME->data['id'] ) {
	if ( defined('IS_AJAX') )
		die();
	else
		$SYS->redirect('./');
}

?>