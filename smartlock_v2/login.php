<?php
	
define('KEEPFRESH', TRUE);
define('ALLOW_GUEST', TRUE);

require_once('global.php');

class page
{
	function mainfunc() {
		global $ME, $_INPUT;

		switch ( trim($_INPUT['act']) ) {
			case 'logout'	:	$this->user_logout();	break;
			case 'getsalt'	:	$this->get_salt();		break;
			default			:	$this->user_login();	break;
		}
	}

	private function get_salt() {
		global $DB, $ME, $_INPUT;
		
		$mobilenum = trim($_INPUT['mobilenum']);
		
		if ( !$mobilenum )
			notify_fail('No mobile number provided');
		
		$udata = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."user WHERE mobilenum = '".$mobilenum."'");
		if ( !$udata )
			notify_fail('Mobile number is not registered.');

		echo 'salt='.$udata['salt'];
		die(); 
	}
	
	private function user_logout() {
		global $DB, $ME;
		
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."session SET id = 0 WHERE sid = '".$ME->data['sid']."'");
		$ME->data['id'] = 0;
		
		page_redirect('./');
	}
	
	private function user_login() {
		global $DB, $_INPUT, $ME;

		$mobilenum = trim($_INPUT['mobilenum']);
		$encpassword = trim($_INPUT['encpassword']);
		$captcha = trim($_INPUT['captcha']);
		
		if ( !$mobilenum || !$captcha )
			notify_fail('Mobile number and captcha must be filled.');
		if ( !$captcha || strlen($captcha) != 6 )
			notify_fail('Captcha missing or not in valid format.');

		if ( !$encpassword || strlen($encpassword) != 32 )
			notify_fail("Hmm... I wonders how you get to this result. Contact and tell me what you've done to my precious website!");
		
		$cdata = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."captcha WHERE sid = '".$ME->data['sid']."'");
		if ( !$cdata || TIMENOW > $cdata['validuntil'] )
			notify_fail('The captcha is out of life. Please go back to login page and refresh before trying to login again.');
		
		if ( strtolower($captcha) != strtolower($cdata['captchastring']) ) {
			// Delete used captcha to force making a new one for the next login attempt.
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."captcha WHERE sid = '".$ME->data['sid']."'");
			notify_fail('Opps! Captcha is wrong! Are you a robot? Break this: '.md5('F U!'));
		}

		$udata = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."user WHERE mobilenum = '".$mobilenum."'");
		if ( !$udata )
			notify_fail('The mobile number '.$mobilenum.' is not registered yet.');
		
		if ( md5($udata['password'].$cdata['randmask']) != $encpassword ) {
			// Delete used captcha to force making a new one for the next login attempt.
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."captcha WHERE sid = '".$ME->data['sid']."'");
			notify_fail('Password incorrect.');
		}

		// Delete used captcha to force making a new one for the next login attempt.
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."captcha WHERE sid = '".$ME->data['sid']."'");
		
		$ME->data['id'] = $udata['id'];
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."session set id = ".$udata['id']." WHERE sid = '".$ME->data['sid']."'");

		page_redirect('home.php');
	}
}

$module = new page();
$module->mainfunc();

?>