<?php

$config = array(
	'dbserver'		=> 'localhost',
	'dbport'		=> 3306,
	'dbname'		=> 'max_smartlockv2',
	'dbusername'	=> 'max',
	'dbpassword'	=> 'ToraNoChris8301',
	'dbtableprefix'	=> 'SLv2_',
	
	'cookiedomain'	=> '',
	'cookiepath'	=> '/smartlock_v2/',
	'cookieprefix'	=> 'SLv2_',
	
	'sessiontimeout'	=> 86400,
	'captchatimeout'	=> 60,
	
	'socketsendtimeout'			=> 5,
	'socketrecievetimeout'		=> 5,
	'socketreadbufferlength'	=> 100,
	
	'publickeylength'	=> 27,
	'roomlockport'		=> 11983,
	'roomencryptkey'	=> array(0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b,0x0b),
);
	
?>