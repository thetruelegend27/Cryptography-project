<?php
define('ALLOW_GUEST', TRUE);
require_once('global.php');

class page
{
	function mainfunc() {
		global $ME, $_INPUT;

		switch ( trim($_INPUT['act']) ) {
			case 'pwd'		:	$this->user_pwd();		break;
			case 'del'		:	$this->user_del();		break;
			case 'mod'		:	$this->user_mod();		break;
			case 'reg'		:	$this->user_register();	break;
			default			:	notify_fail('What do you want? Huh? =__=||');	break;
		}
	}
	
	private function user_pwd() {
		global $DB, $_INPUT;

		$uid = intval($_INPUT['uid']);
		$password = trim($_INPUT['password']);

		if ( !$uid || $uid < 1 )
			notify_fail('No valid user ID provided.');
		
		$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE id = ".$uid);
		if ( !$DB->num_rows() )
			notify_fail('User with ID '.$uid.' does not exist in the database.');
		
		$salt = generate_hash(6);
		$encpassword = md5(md5($password).$salt);
		
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET password = '".$encpassword."', salt = '".$salt."' WHERE id = ".$uid);

		// Force the logged in user to quit by cleaning his / her session.
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE id = ".$uid);
		
		page_redirect('register.php');
	}
	
	private function user_del() {
		global $DB, $_INPUT;

		$uid = intval($_INPUT['uid']);
		
		if ( !$uid || $uid < 1 )
			notify_fail('No valid user ID provided.');
		
		$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE id = ".$uid);
		if ( !$DB->num_rows() )
			notify_fail('User with ID '.$uid.' does not exist in the database.');
			
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."user WHERE id = ".$uid);
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."roomperms WHERE userid = ".$uid);
		
		// Force the logged in user to quit by cleaning his / her session.
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."session WHERE id = ".$uid);
		
		page_redirect('register.php');
	}
	
	private function user_mod() {
		global $DB, $_INPUT;

		$uid = intval($_INPUT['uid']);
		$roomnum = intval($_INPUT['roomnum']);
		$newexpire = trim($_INPUT['newexpire']);
		
		if ( !$uid || $uid < 1 )
			notify_fail('No valid user ID provided.');
		
		if ( !$roomnum || $roomnum < 1 )
			notify_fail('No valid room number provided.');
		
		$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE id = ".$uid);
		if ( !$DB->num_rows() )
			notify_fail('User with ID '.$uid.' does not exist in the database.');
		
		$DB->query("SELECT * FROM ".TABLE_PREFIX."room WHERE roomNum = ".$roomnum);
		if ( !$DB->num_rows() )
			notify_fail('Room #'.$roomnum.' does not exist in the database.');
		
		$DB->query("SELECT * FROM ".TABLE_PREFIX."roomperms WHERE userid = ".$uid." AND roomnum = ".$roomnum);
		if ( !$DB->num_rows() )
			notify_fail('Hmmm... What the heck did you do? There is no such user and room permission combination in the database to modify. Did you hack our website or something? =__=||');
		
		if ( $newexpire && !$expiretimestamp = @strtotime($newexpire) )
			notify_fail("New expire time given is not in right format. Use 'YYYY-mm-dd HH:mm:ss' strictly.");

		$expiretime = !$newexpire ? 'NULL' : "'".date('Y-m-d H:i:s',$expiretimestamp)."'";
		
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."roomperms SET expiretime = ".$expiretime." WHERE userid = ".$uid." AND roomnum = ".$roomnum);
		
		page_redirect('register.php');
	}

	private function user_register() {
		global $DB, $_INPUT;

		$fullname = trim($_INPUT['fullname']);
		$mobilenum = trim($_INPUT['mobilenum']);
		$password = trim($_INPUT['password']);
		$roomnum = intval($_INPUT['roomnum']);
		$validuntil = trim($_INPUT['validuntil']);
		
		if ( !$fullname || !$mobilenum || !$password || !$roomnum )
			notify_fail('Everything must be filled ONLY except Expire Time.');
		
		$DB->query("SELECT * FROM ".TABLE_PREFIX."user WHERE mobilenum = '".$mobilenum."'");
		if ( $DB->num_rows() )
			notify_fail('Mobile number '.$mobilenum.' is already used for registration.');
			
		$DB->query("SELECT * FROM ".TABLE_PREFIX."room WHERE roomNum = ".$roomnum);
		if ( !$DB->num_rows() )
			notify_fail('Database does NOT contain data of room #'.$roomnum.'.');

		if ( $validuntil && !$expiretimestamp = @strtotime($validuntil) )
			notify_fail("Expire time given is not in right format. Use 'YYYY-mm-dd HH:mm:ss' strictly.");

		$salt = generate_hash(6);
		$encpassword = md5(md5($password).$salt);
		
		$DB->query("INSERT INTO ".TABLE_PREFIX."user (fullname, mobilenum, password, salt, regtime) VALUES ('".$fullname."', '".$mobilenum."', '".$encpassword."', '".$salt."', '".date('Y-m-d H:i:s',TIMENOW)."')");
		$uid = $DB->insert_id();
		
		$expiretime = !$validuntil ? 'NULL' : "'".date('Y-m-d H:i:s',$expiretimestamp)."'";
		
		$DB->query("INSERT INTO ".TABLE_PREFIX."roomperms (addtime, expiretime, userid, roomnum) VALUES ('".date('Y-m-d H:i:s',TIMENOW)."', ".$expiretime.", ".$uid.", ".$roomnum.")");
		
		page_redirect('register.php');
	}
}

$module = new page();
$module->mainfunc();

?>