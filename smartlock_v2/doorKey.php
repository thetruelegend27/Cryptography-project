<?php

require_once('global.php');

$roomnum = intval($_INPUT['roomNum']);
if ( !$roomnum || $roomnum < 1 )
	notify_fail('Invalid room number provided.');
$roominfo = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."room WHERE roomNum = ".$roomnum);
if ( !$roominfo )
	notify_fail('Room #'.$roomnum.' does not exist in the database.');

$perminfo = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."roomperms WHERE userid = ".$ME->data['id']." AND roomnum = ".$roomnum);
if ( !$perminfo )
	notify_fail('You do not have permissions to operate on this door.');
if ( $perminfo['expiretime'] != NULL ) {
	@$expiretimestamp = strtotime($perminfo['expiretime']);
	if ( !$expiretimestamp )
		notify_fail('Expire time record on this door for you seems to be corrupted. Please contact administrator.');
	if ( TIMENOW > $expiretimestamp )
		notify_fail('Your permission to access this door is expired.');
}

?>

<!-- Door lock control page by JiangCat -->
<!-- Last modified by JiangCat @ 2015.12.24 23:57 -->
<!-- Moved all UI logic to JS using AJAX -->
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Door Lock Control</title>
	<link rel="stylesheet" href="css/smartlock.css" />
	<link rel="stylesheet" href="css/iconfont.css" />
	<script type="text/javascript" src="js/MooTools-Core-1.5.2.js"></script>
	<script type="text/javascript" src="js/smartlock.js"></script>
	<script type="text/javascript">
	var MySID = '<?php echo $ME->data['sid']; ?>';
	var roomNum = <?php echo $_REQUEST['roomNum']; ?>;
	// Using 'domready' event to prevent js executing before 
	// the entire document is rendered in browser.
	window.addEvent('domready', function() {
		smartLock.pageDoorKey.init();
	});
	</script>
</head>
<body>
<div class="titlerow">Room <?php echo $_REQUEST['roomNum']; ?> Lock Control</div>

<div id="lockIcon" class="pagesign" style="margin:0 auto; float:none; opacity:0.2;"><span class="iconfont lock" style="font-size:110px;"></span></div>
	
<div class="wrapper ac" style="margin-top:10px;">Lock State: <span id="lockStatusText">Connecting...</span></div>

<div id="areaUnlock" class="ac" style="margin-top:50px; display:none;">
	<button id="btnUnlock" class="btninput" style="width:240px;">Click To UnLock</button>
</div>
<div id="areaLock" class="ac" style="margin-top:50px;">
	<button id="btnLock" class="btninput" style="width:240px;">Click To Lock</button>
</div>

<div class="wrapper ac" style="margin-top:10px;">
	<button id="btnCheck" class="btninput" style="font-size:14px; margin-top:20px; padding:6px 15px; border-width:2px;">Update State</button>
</div>
</body>
</html>