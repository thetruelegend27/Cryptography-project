<!-- Room selection page by JiangCat -->
<!-- Last modified by JiangCat @ 2015.12.24 16:33 -->
<?php

define('NOGZIP', TRUE);
require_once('global.php');

$floorNum = intval($_INPUT['floor']);

// Query all room numbers from database
$DB->query("SELECT roomNum, reserveState FROM ".TABLE_PREFIX."room WHERE floorNum = ".$floorNum);

// Take the result into array type variable
// Note by Jiangcat:
//		This is such an ugly way to structure results, but let's'
//		just leave it that way, for now.
$roomNum = array();
$reserveState = array();
while ( $row = $DB->fetch_array() ) {
	$roomNum[] = $row['roomNum'];		//All room number which floor number is $floorNum
//	$reserveState[] = $row[1];	//All reserve state which floor number is $fllorNum
	// Modified by JiangCat
	//		Change the status code from STR(1) to INT(1), simulating a boolean
	//		flag. Because of the difference of grammar structure, a true boolean
	//		value needs to be translated to string 'true' or 'false' while porting
	//		to JS, and transfering a STR(5) 'false' string is more costy than just
	//		a STR(1) '1' in HTML/1.1 protocol, '1/0' is apparently more effective
	//		than true/false as switch.
	$reserveState[] = $row['reserveState'];
}

?>	
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Room Selection</title>
	<link rel="stylesheet" href="css/smartlock.css" />
	<link rel="stylesheet" href="css/iconfont.css" />
	<script type="text/javascript" src="js/MooTools-Core-1.5.2.js"></script>
	<script type="text/javascript" src="js/smartlock.js"></script>
	<script type="text/javascript">
	// Put the lock state result to JS for UI rendering
	var floorNum = <?php echo $floorNum; ?>;
	var lockStatus = [
		// Room numbers in an array
		[<?php echo implode(',',$roomNum); ?>],
		// Lock status in an array
		[<?php echo implode(',',$reserveState); ?>]
	];
	/*
	Change to OO style after implementing MooTools framework.
	Also moving the program logic to JS files
	*/
	// Using 'domready' event to prevent js executing before 
	// the entire document is rendered in browser.
	window.addEvent('domready', function() {
		smartLock.pageSelectRoom.init();
	});
	</script>
</head>
<body>
<div class="titlerow">Floor <?php echo $floorNum; ?> Room Selection</div>
<div class="wrapper">
	<div class="colright">
		<!--
			All 'LI' elements are generated dynamically in JS logic,
			making the result HTML code cleaner, and reduce server pressure
			by moving UI generating process to the client side.
		-->
		<ul id="roomul" class="btnroom"></ul>
		<!--
		Entire logic moved to JS file.
		<script type="text/javascript">
			var lis = document.getElementById('roomul').getElementsByTagName('li');
			for ( var i=0; i < lis.length; i++ ) {
				lis[i].addEventListener('mouseover', function(){
					this.setAttribute('class', 'over');
				});
				lis[i].addEventListener('mouseout', function(){
					this.setAttribute('class', '');
				});
			}
		</script>
		-->
	</div>
	<div class="colleft">
		<div class="pagesign"><span class="iconfont floor"></span></div>
	</div>
	<div class="cl"></div>
</div>
</body>
</html>