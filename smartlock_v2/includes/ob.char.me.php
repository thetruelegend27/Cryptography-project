<?php

/*
 * Website visitor object.
 * This is inherited from default user object, which is the skeleton, but
 * added session controls to check if the user is logged in.
 */

require_once('./includes/ob.char.php');

class UserME extends CharOB
{
	// Default object constructing function.
	function __construct() {
		parent::__construct();

		// Default varaible values.
		$this->data['sid'] = '';
		$this->data['cookiefree'] = FALSE;

		// Check login information while instanciating.
		// - Once a user opens the website, there should be an unique id to track its behavior,
		// 	 which is the session id. Session id should be created for anyone, logged in or not.
		// - Login info is also stored in session data. Server will load the detailed info of
		//	 this user if he or she is logged in, or otherwise just use the basic user object
		// 	 to operate as GUEST.
		$this->check_login();
	}

	// Login Check
	// Basically, after login, an user is defined by its SESSION ID.
	// Session ID is supposed to be stored in browser cookie. However, if the
	// browser does not accept cookie, or for some other reasons such as debuging,
	// session id may be passed to server as a POST or GET variable named 'sid'.
	function check_login() {
		global $config, $DB, $_INPUT;

		// Create a new session if the server does not have tracking of this user.
		if ( !$sid = $this->get_cookie('sid') )
			if ( !$sid = trim($_INPUT['sid']) )
				return $this->create_new_session();

		// Check if the session id is valid.
		// If not, create a new one.
		if ( strlen($sid) != 50 )
			return $this->create_new_session();

		// Check if the session id exists in the database.
		// Possible reason for a user to pass in an invalid session id, may caused
		// by server cleaning up timed out sessions.
		// If the session id doesn't exist, create a new one.
		$sdata = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."session WHERE sid = '".$sid."'");
		if ( !$sdata )
			return $this->create_new_session();

		// Check if the session is already timeout.
		// If so, delete the session info from database and create a new one.
		// Normally in production environment, there should be a scheduled process
		// periodically cleaning the session table, but let's leave it simple
		// for now.
		if ( $sdata['lastactive'] < TIMENOW - $config['sessiontimeout'] ) {
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE sid = '".$sid."'");
			return $this->create_new_session();
		}

		// If the session is valid and exists in our database, store detailed
		// session data to the user object instance.
		$this->data = array_merge($this->data, $sdata);

		// If the session data shows that this user is already logged in, load his
		// or her detailed user data. 
		if ( $sdata['id'] )
			$this->load_user($sdata['id']);

		// Update the session info.
		$this->update_session();
	}

	// Session info updating.
	function update_session($FULLUPDATE=FALSE) {
		global $_INPUT, $DB;

		if ( !$this->data['sid'] )
			return;

		$this->data['host'] = SESSION_HOST;
		$this->data['useragent'] = USER_AGENT;
		$this->data['lastactive'] = TIMENOW;

		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."session SET host = '".SESSION_HOST."', useragent = '".USER_AGENT."', lastactive = ".TIMENOW." WHERE sid = '".$this->data['sid']."'");
	}

	// Creating an new session.
	private function create_new_session() {
		global $DB;

		// Creating an totally random 50 charaters length string as session id.
		// It is supposed to be unique for every user on every browser.
		$mt = explode(' ', microtime());
		$mt = $mt[1].substr($mt[0],2);
		$sid = generate_hash((50-strlen($mt))).$mt;
		
		// Store the session id and related user info into database.
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."session (sid, host, useragent, lastactive) VALUES ('".$sid."', '".SESSION_HOST."', '".USER_AGENT."', ".TIMENOW.")");
		
		// Apply session info to the user instance.
		$this->data['sid'] = $sid;
		$this->data['host'] = SESSION_HOST;
		$this->data['useragent'] = USER_AGENT;
		$this->data['lastactive'] = TIMENOW;

		// Try to send a cookie to user browser containing the session id only.
		// If it's failed for some reason, such like the browser does not accept
		// cookie for security reasons, then mark the user as 'cookiefree', and
		// we have to user POST or GET to pass session id every time instead.
		if ( !$this->set_cookie('sid', $sid) )
			$this->data['cookiefree'] = TRUE;
	}

	// Try to send a cookie.
	function set_cookie($name, $value='', $cookiedate=0) {
		global $config;
		
		$expires = $cookiedate > 0 ? TIMENOW + $cookiedate : null;
		$name = $config['cookieprefix'].$name;
		$value = rawurlencode($value);

		return @setcookie($name, $value, $expires, $config['cookiepath'], $config['cookiedomain']);
	}

	// Delete a cookie.
	function del_cookie($name) {
		$this->set_cookie($name, '', 0);
	}

	// Try to read a cookie value by its key.
	function get_cookie($name) {
		global $config, $_COOKIE;

		if ( isset($_COOKIE[$config['cookieprefix'].$name]) )
			return rawurldecode($_COOKIE[$config['cookieprefix'].$name]);

		return false;
	}
}

?>