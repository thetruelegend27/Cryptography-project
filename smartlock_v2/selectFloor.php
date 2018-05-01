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
	/*
	Change to OO style after implementing MooTools framework.
	Also moving the program logic to JS files
	*/
	// Using 'domready' event to prevent js executing before 
	// the entire document is rendered in browser.
	window.addEvent('domready', function() {
		smartLock.pageIndex.init();
	});
	</script>
</head>
<body>
<div class="titlerow"><span id="ftprefix"></span> Floor Selection</div>
<div class="wrapper">
	<div class="colright">
		<ul id="floorul" class="btnfloor">
			<!--
			<li>Floor 1</li>
			<li>Floor 2</li>
			<li>Floor 3</li>
			<li>Floor 4</li>
			<li>Floor 5</li>
			<li>Floor 6</li>
			<li>Floor 7</li>
			<li>Floor 8</li>
			<li>Floor 9</li>
			<li>Floor 10</li>
			<li>Floor 11</li>
			<li>Floor 12</li>
			<li>Floor 13</li>
			<li>Floor 14</li>
			<li>Floor 15</li>
			<li>Floor 16</li>
			<li>Floor 17</li>
			<li>Floor 18</li>
			<li>Floor 19</li>
			-->
		</ul>
	</div>
	<!--
	Entire logic moved to JS file.
	<script type="text/javascript">
		var lis = document.getElementById('floorul').getElementsByTagName('li');
		for ( var i=0; i < lis.length; i++ ) {
			lis[i].setAttribute('id', 'btn_'+(i+1));
			lis[i].addEventListener('mouseover', function(){
				this.setAttribute('class', 'over');
			});
			lis[i].addEventListener('mouseout', function(){
				this.setAttribute('class', '');
			});
			lis[i].addEventListener('click', function(){
				smartLock.openFloorPage(this.id.substr(4));
			});
		}
	</script>
	-->
	<div class="colleft">
		<div class="pagesign"><span id="floortypeicon" class="iconfont building"></span></div>
	</div>
	<div class="cl"></div>
</div>
</body>
</html>
