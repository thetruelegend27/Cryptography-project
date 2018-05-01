<?php
	
require_once('global.php');

?>
<!-- Smart lock control demo by Jiangcat -->
<!-- Totally 6 floors with 10 rooms each -->
<!-- Last modified by JiangCat @ 2015.12.24 16:11 -->
<html>
<head>
	<title>Intelligent Building Access Control System</title>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="css/smartlock.css" />
	<link rel="stylesheet" href="css/iconfont.css" />
	<script type="text/javascript" src="js/MooTools-Core-1.5.2.js"></script>
	<script type="text/javascript" src="js/smartlock.js"></script>
	<script type="text/javascript">
	// Using 'domready' event to prevent js executing before 
	// the entire document is rendered in browser.
	window.addEvent('domready', function() {
		smartLock.floorIndex.init();
	});
	</script>
</head>
<body>
<div class="titlerow">Intelligent Building Access Control System</div>
<div class="wrapper">
	<div class="colright">
		<ul id="floorul" class="btnfloor">
			<li>Office Floor</li>
			<li>Residence Floor</li>
			<li>Hotel Floor</li>
			<li>Other Floor</li>
		</ul>
	</div>
	<div class="colleft">
		<div class="pagesign"><span class="iconfont building"></span></div>
	</div>
	<div class="cl"></div>
</div>
</body>
</html>
