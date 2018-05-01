<?php

@error_reporting(E_ALL ^ E_NOTICE);

if ( !function_exists('socket_create') )
	@dl((DIRECTORY_SEPARATOR == '\\' ? 'php_sockets.dll' : 'sockets.so'));

define('SERVERIP', '127.0.0.1');
define('SERVERPORT', 11983);
define('ROOMNUM', 201);

set_time_limit(0);
ob_implicit_flush();

require_once('includes/config.php');
define('TABLE_PREFIX', $config['dbtableprefix']);

echo "--------------------------------------------\n";
echo "Starting lock simulating server...\n";

echo "Connecting database...\n";
$DB = new mysqli($config['dbserver'], $config['dbusername'], $config['dbpassword'], $config['dbname'], $config['dbport']);

function check_lock_status() {
	global $DB;
	echo "Checking lock status of room ".ROOMNUM."...\n";
	$dbres = $DB->query("SELECT lockState FROM ".TABLE_PREFIX."room WHERE roomNum = ".ROOMNUM." LIMIT 0,1");
	if ( !$dbres->num_rows )
		return 'Z';
	while ( $r = $dbres->fetch_row() )
		$stat = $r[0];
	$stat = $stat == 1 ? 'Y' : 'N';
	return $stat;
}

function generate_key_N() {
	$s = '';
	for ( $i=0; $i<27; $i++ )
		$s .= rand(0,9);
	return $s;
}
function generate_key_hash($n) {
	global $config;
	$sha256key = array();
	foreach ( $config['roomencryptkey'] AS $k )
		$sha256key[] = chr($k);
	$sha256key = implode('',$sha256key);
	return hash_hmac('sha256', $n, $sha256key, false);
}

$socketCon = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socketCon, SERVERIP, SERVERPORT);
socket_listen($socketCon, 10);

echo "Socket listening...\n";
echo "--------------------------------------------\n";

$clientID = 0;
$clientData = array();
do {
	if ( ($msgCon = socket_accept($socketCon)) === false) {
		echo "socket_accept() failed: reason: ".socket_strerror(socket_last_error($socketCon))."\n";
		echo "--------------------------------------------\n";
		break;
	} else {
		$clientID ++;
		$clientData[$clientID] = array();
		echo "Client #" .$clientID .": Connected\n";
		echo "--------------------------------------------\n";
	}
	$cur_buf = '';
	do {
		if ( false === ($buf = socket_read($msgCon, 1024)) ) {
			echo "socket_read() failed: reason: ".socket_strerror(socket_last_error($msgCon))."\n";
			echo "--------------------------------------------\n";
            break 2;
        }
		$cur_buf .= $buf;
		if ( substr($cur_buf,-1) == "\n" ) {
			$cmd = trim($cur_buf);
			echo 'Client #'.$clientID.': '.$cmd."\n";
			$cmdarr = explode(',', $cmd);
			if ( $cmdarr[0] == 'CHECKSTATUS' || $cmdarr[0] == 'LOCK' || $cmdarr[0] == 'UNLOCK' ) {
				$clientData[$clientID]['lastcmd'] = $cmdarr[0];
				$clientData[$clientID]['lastN'] = generate_key_N();
				if ( $cmdarr[0] != 'CHECKSTATUS' ) {
					$clientData[$clientID]['lastS'] = $cmdarr[1];
					$clientData[$clientID]['lastR'] = $cmdarr[2];
				}
				$respond = $clientData[$clientID]['lastN']."\n";
				socket_write($msgCon, $respond, strlen($respond));
				echo 'Respond: '.$clientData[$clientID]['lastN']."\n";
//				echo 'Hash: '.generate_key_hash($clientData[$clientID]['lastN'])."\n";
				echo "--------------------------------------------\n";
			} else {
				if ( !$clientData[$clientID] || !$clientData[$clientID]['lastcmd'] || !$clientData[$clientID]['lastN'] ) {
					echo "Error: client data missing while checking hash.\n";
					socket_write($msgCon, "X\n", strlen("X\n"));
					break;
				}
				if ( $clientData[$clientID]['lastcmd'] == 'CHECKSTATUS' ) {
					if ( generate_key_hash(strtoupper($clientData[$clientID]['lastN'])) != $cmdarr[0] ) {
						echo "Error: client hash failed while CHECKSTATUS.\n";
						socket_write($msgCon, "X\n", strlen("X\n"));
						break;
					}
				} else {
					if ( !$clientData[$clientID]['lastS'] || !$clientData[$clientID]['lastR'] ) {
						echo "Error: client S & R data is missing while checking hash.\n";
						socket_write($msgCon, "X\n", strlen("X\n"));
						break;
					}
					$hashtext = $clientData[$clientID]['lastN'].
								$clientData[$clientID]['lastR'].
								$clientData[$clientID]['lastS'].
								$clientData[$clientID]['lastcmd'];
					$hashenc = generate_key_hash(strtoupper($hashtext));
					if ( $hashenc != $cmdarr[0] ) {
						echo "Error: client hash failed while ".$clientData[$clientID]['lastcmd'].".\nShould be:\n\tHash = ".$hashenc."\n\tlastN = ".$clientData[$clientID]['lastN']."\n\tLastR = ".$clientData[$clientID]['lastR']."\n\tlastS = ".$clientData[$clientID]['lastS']."\n\tlastcmd = ".$clientData[$clientID]['lastcmd']."\n";
						socket_write($msgCon, "X\n", strlen("X\n"));
						break;
					}
				}
				switch ( $clientData[$clientID]['lastcmd'] ) {
					case 'CHECKSTATUS'	:	$respond = check_lock_status();
											break;
					case 'LOCK'			:	$respond = 'Y';
											$DB->query("UPDATE ".TABLE_PREFIX."room SET lockState = 1 WHERE roomNum = ".ROOMNUM);
											break;
					case 'UNLOCK'		:	$respond = 'N';
											$DB->query("UPDATE ".TABLE_PREFIX."room SET lockState = 0 WHERE roomNum = ".ROOMNUM);
											break;
					default				:	echo "Unknown command.\n";
											break 2;
				}
				echo 'Respond: '.$respond."\n";
				echo "--------------------------------------------\n";
				$respond .= "\n";
				socket_write($msgCon, $respond, strlen($respond));
				break;
			}
			$cur_buf = '';
		}
	} while ( true );
	echo 'Connection to client #' .$clientID ." closed by server.\n";
	echo "--------------------------------------------\n";
	socket_close($msgCon);
} while ( true );

socket_close($socketCon);
echo "Script ended!\n";

?>