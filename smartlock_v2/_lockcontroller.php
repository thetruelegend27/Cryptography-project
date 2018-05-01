<?php

/*
 * Smartlock AJAX controller by JiangCat @ 2015.12.24 17:30
 * 	Note:	It is a good practice to name indirect-user-access PHP file with a
 * 			filename prefix '_'.
 */

define('IS_AJAX', TRUE);
define('ALLOW_GUEST', TRUE);
define('CONTENT_TYPE', 'text/plain');

require_once('global.php');

define('STATICSERVERIDENTIFIER', 'S');

 
// Check if the sockets extension already loaded
if ( !function_exists('socket_create') ) {
	// Check if dl() function is available for dynamic module loading
	// If the function does not exist and can not be loaded dynamically,
	// the script should notify AJAX the error.
	if ( !function_exists('dl') ) {
		echo 'e=900|';
		die();
	}
	// Check what operation system the script is running on and load the module
	// accordingly. Windows should load .DLL while *nix goes for .SO.
	if ( !dl((DIRECTORY_SEPARATOR == '\\' ? 'php_sockets.dll' : 'sockets.so')) )  {
		echo 'e=999|';
		die();
	}
}


class lockController
{
	private $debugMode = true;
	private $roomData = 0;
	private $socketCon = false;
	
	private $debugInfo = '';

	// Default entry function of the class.
	function main() {
		global $DB, $ME, $_INPUT;
		
		// Check login
		if ( !$ME->data['id'] )
			$this->show_error(700, 'You are not logged in.');
		
		// Check if room number is provided
		if ( !$roomNum = intval($_INPUT['roomNum']) )
			$this->show_error(101);
		
		// Get data of the specific room		
		$this->roomData = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."room WHERE roomNum = ".$roomNum." LIMIT 0,1");
		if ( !$this->roomData['ipAddress'] )
			$this->show_error(103);

		// Check permissions
		$perminfo = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."roomperms WHERE userid = ".$ME->data['id']." AND roomnum = ".$roomNum);
		if ( !$perminfo )
			$this->show_error(710, 'You do not have permissions to operate on this door.');
		if ( $perminfo['expiretime'] != NULL ) {
			@$expiretimestamp = strtotime($perminfo['expiretime']);
			if ( !$expiretimestamp )
				$this->show_error(720, 'Expire time record on this door for you seems to be corrupted. Please contact administrator.');
			if ( TIMENOW > $expiretimestamp )
				$this->show_error(730, 'Your permission to access this door is expired.');
		}

		// Action handler 
		switch ( trim($_INPUT['action']) ) {
			case 'checkstatus'	:
			case 'lock'			:
			case 'unlock'		:	$this->send_lock_cmd(strtoupper(trim($_INPUT['action'])));
									break;
			default				:	$this->show_error(100);
									break;
		}
	}

	// Send command to lock
	private function send_lock_cmd($cmd) {
		global $DB, $_INPUT;

		if ( $cmd == 'LOCK' || $cmd == 'UNLOCK' ) {
			$cmdcombo = $cmd.','.STATICSERVERIDENTIFIER.','.trim($_INPUT['role']);
		} else {
			$cmdcombo = $cmd;
		}
//		$n = $this->socket_request($cmd, '123.59.81.183', 9999);
		$nresp = $this->socket_request($cmdcombo);
//		$this->debugInfo .= "Raw response: ".$nresp;
/*
		if ( strpos($nresp,',') === FALSE ) {
			$n = $nresp;
			$R = '';
		} else {
			$nresp = explode(',', $nresp);
			$n = $nresp[0];
			$R = $nresp[1];
		}
*/
		// Random N is not 27 digits long
		if ( strlen($nresp) != 27 )
			$this->show_error(300, 'N='.$nresp);
		
		// Lock role undefined
		/*
		if ( ($cmd == 'LOCK' || $cmd == 'UNLOCK') && !$R )
			$this->show_error(305);
		*/
		
		if ( $cmd == 'LOCK' || $cmd == 'UNLOCK' )
			$hashtext = $nresp.trim($_INPUT['role']).STATICSERVERIDENTIFIER.$cmd;
		else
			$hashtext = $nresp;
		$hashtext = strtoupper($hashtext);
		$hash = $this->generate_key_hash($hashtext);

		$res = $this->socket_request($hash);
		// Wrong hash
		if ( $res == 'X' )
			$this->show_error(301);
		// 
		if ( $res == 'Z' )
			$this->show_error(400);

		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."room SET lockState = ".($res=='Y'?1:0)." WHERE roomNum = ".$this->roomData['roomNum']);

		$respond = trim($_INPUT['action']).'|'.$res;
		
		if ( $this->debugMode && $this->debugInfo )
			$respond .= "\n".$this->debugInfo;

		echo $respond;
	}

	private function socket_request($msg, $lockIp='', $lockPort=0) {
		global $config;

		if ( $this->socketCon === false ) {
			if ( $this->debugMode )
				$this->debugInfo .= "Creating new connection...\n";
			$this->socketCon = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ( $this->socketCon === false ) {
				if ( $this->debugMode )
					$this->debugInfo .= "socket_create() fail\n";
				$this->show_socket_error(200);
			}
			if ( $this->debugMode )
				$this->debugInfo .= "Setting socket options...\n";
			socket_set_option($this->socketCon, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>$config['socketsendtimeout'], 'usec'=>0));
			socket_set_option($this->socketCon, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>$config['socketrecievetimeout'], 'usec'=>0));
			if ( !$lockIp )		$lockIp = $this->roomData['ipAddress'];
			if ( !$lockPort )	$lockPort = $config['roomlockport']; 
			if ( $this->debugMode )
				$this->debugInfo .= "Connecting lock... ";
			if ( socket_connect($this->socketCon, $lockIp, $lockPort) === false ) {
				if ( $this->debugMode )
					$this->debugInfo .= "[Fail]\n";
				$this->show_socket_error(201);
			} else {
				if ( $this->debugMode )
					$this->debugInfo .= "[OK]\n";
			}
		}
		
		if ( $this->debugMode )
			$this->debugInfo .= "Sending: ".$msg."\n";
		
		$msg .= "\n";
//		if ( socket_send($this->socketCon, $msg, strlen($msg), MSG_OOB) === false )
		if ( socket_write($this->socketCon, $msg, strlen($msg)) === false ) {
			if ( $this->debugMode )
				$this->debugInfo .= "socket_write() failed!\n";
			$this->show_socket_error(202);
		}
		
		$readbuf = '';
		$respond = '';
		$readseq = 1;
		while ( true ) {
			$readbuf = socket_read($this->socketCon, $config['socketreadbufferlength'], PHP_BINARY_READ);
			if ( $readbuf === false )
				$this->show_socket_error(203);
			if ( $this->debugMode )
				$this->debugInfo .= "socket_read() seq=".$readseq." got: ".$readbuf."\n";
			
			$respond .= $readbuf;
			$readseq ++;
			if ( substr($respond,-1) == "\n" )
				break;
		}
			
		return trim($respond);
	}
	
	private function show_socket_error($eNum=200, $eMsg='') {
		$this->show_error($eNum, '['.socket_last_error($this->socketCon).'] '.socket_strerror(socket_last_error($this->socketCon)));
	}

	private function show_error($eNum=0, $eMsg='') {
		$respond = 'e='.$eNum.'|'.$eMsg;
		if ( $this->debugMode && $this->debugInfo )
			$respond .= "\n".$this->debugInfo;
		echo $respond;
		die();
	}

	private function generate_key_hash($n) {
		global $config;
		
		$sha256key = array();
		foreach ( $config['roomencryptkey'] AS $k )
			$sha256key[] = chr($k);
		$sha256key = implode('',$sha256key);
		
		return hash_hmac('sha256', $n, $sha256key, false);
	}
}

$controller = new lockController();
$controller->main();
 
?>