<?php

/*
 * Initialization of every single page of this website.
 */

// Definitions for production and debug usages.
define('STARTTIME', microtime());
define('STARTMEM', memory_get_usage());
define('TIMENOW', 
	(isset($_SERVER['REQUEST_TIME']) && is_int($_SERVER['REQUEST_TIME'])) 
		? $_SERVER['REQUEST_TIME'] 
		: time()
);
define('MSTIMENOW', substr(microtime(TRUE), 0, 15));
define('IS_WIN', DIRECTORY_SEPARATOR == '\\');

// Loading the basic config file.
if ( !@include('./includes/config.php') ) {
	exit('The file "includes/config.php" does not exist.');
}

// Setting up PHP runtime environment.
@error_reporting(E_ALL ^ E_NOTICE);
@set_magic_quotes_runtime(0);
@mb_internal_encoding('UTF-8');

// Filter and make some definitions to prevent hostile code running from browser user_agent or cookies.
function xss_clean($var) {
	return preg_replace('/(java|vb)script/i', '\\1 script', utf8_htmlspecialchars($var));
}

function format_path($path) {
	if ( strpos($path, '\\') !== false ) {
		$path = str_replace('\\', '/', $path);
	}
	if ( strpos($path, '//') !== false ) {
		$path = str_replace('//', '/', $path);
	}
	return $path;
}

function utf8_chr($cp) {
	if ( $cp > 0xFFFF )
		return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	else if ( $cp > 0x7FF )
		return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	else if ( $cp > 0x7F )
		return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
	else
		return chr($cp);
}

function utf8_htmlspecialchars($string) {
	return str_replace(
		array('<', '>', '"', "'"),
		array('&lt;', '&gt;', '&quot;', '&#039;'),
		preg_replace('/&(?!#[0-9]+;)/si', '&amp;', $string)
	);
}

$ip = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_CLIENT_IP'])) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
}
else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
	foreach ($matches[0] as $v) {
		if (!preg_match("#^(10|172\.16|192\.168)\.#", $v)) {
			$ip = $v;
			break;
		}
	}
}
else if (isset($_SERVER['HTTP_FROM'])) {
	$ip = $_SERVER['HTTP_FROM'];
}
define('SESSION_HOST', $ip);

if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']) {
	$scriptpath = $_SERVER['REQUEST_URI'];
}
else if ((isset($_ENV['REQUEST_URI']) && $_ENV['REQUEST_URI'])) {
	$scriptpath = $_ENV['REQUEST_URI'];
}
else {
	if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'])
		$scriptpath = $_SERVER['PATH_INFO'];
	else if (isset($_ENV['PATH_INFO']) && $_ENV['PATH_INFO'])
		$scriptpath = $_ENV['PATH_INFO'];
	else if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'])
		$scriptpath = $_SERVER['REDIRECT_URL'];
	else if (isset($_ENV['REDIRECT_URL']) && $_ENV['REDIRECT_URL'])
		$scriptpath = $_ENV['REDIRECT_URL'];
	else if (isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'])
		$scriptpath = $_SERVER['PHP_SELF'];
	else
		$scriptpath = $_ENV['PHP_SELF'];

	$scriptpath .= '?';
	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'])
		$scriptpath .= $_SERVER['QUERY_STRING'];
	else if (isset($_ENV['QUERY_STRING']) && $_ENV['QUERY_STRING'])
		$scriptpath .= $_ENV['QUERY_STRING'];
}
$scriptpath = preg_replace('/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $scriptpath);
$scriptpath = xss_clean($scriptpath);
if ( false !== ($quest_pos = strpos($scriptpath, '?')) ) {
	$script = urldecode(substr($scriptpath, 0, $quest_pos));
	$scriptpath = $script . substr($scriptpath, $quest_pos);
}
else {
	$script = $scriptpath = urldecode($scriptpath);
}
define('SCRIPTPATH', $scriptpath);
define('SCRIPT', $script);
define('USER_AGENT', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
define('REFERRER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

// Load the MySQLi database operating object by default, unless 'NODB' is defined.
$DB = null;
define('TABLE_PREFIX', $config['dbtableprefix']);
require_once('./includes/ob.mysqli.php');
if ( !defined('NODB') ) {
	$DB = new db;
	$DB->connect($config);
}

?>