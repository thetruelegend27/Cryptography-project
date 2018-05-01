<?php
define('ALLOW_GUEST', TRUE);
require_once('global.php');

if ( $ME->data['id'] )
	$SYS->redirect('home.php');

$captchatext = generate_hash(6,TRUE);
$randmask = generate_hash(8,FALSE);

$DB->query_unbuffered("REPLACE INTO ".TABLE_PREFIX."captcha (sid, randmask, captchastring, validuntil) VALUES ('".$ME->data['sid']."', '".$randmask."', '".$captchatext."', ".(TIMENOW+$config['captchatimeout']).")");

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
	<script type="text/javascript" src="js/smartlock.md5.js"></script>
	<script type="text/javascript">
	var MySID = '<?php echo $ME->data['sid']; ?>';
	// Using 'domready' event to prevent js executing before 
	// the entire document is rendered in browser.
	window.addEvent('domready', function() {
		smartLock.loginPage.init();
	});
	</script>
</head>
<body>
<div class="titlerow">Welcome! Please Login...</div>
<div class="wrapper ac">
	<form id="loginform" action="login.php" method="post" onsubmit="return smartLock.loginPage.checkLoginForm();">
		<input class="inputtext" type="text" name="mobilenum" placeholder="Mobile Number" style="width:300px;" /><br />
		<input class="inputtext" type="password" name="password" placeholder="Password" style="width:300px;" /><br />
		<input class="inputtext" type="text" name="captcha" placeholder="Captcha" style="width:300px;" /><br />
		<img class="logincaptcha" src="_captcha.php" /><br /> 
		<input class="inputbutton" type="submit" name="dosubmit" value="Login" style="width:300px;" />
		<input type="hidden" name="randmask" value="<?php echo $randmask; ?>" />
		<input type="hidden" name="encpassword" value="" />
	</form>
</div>
<div class="wrapper ac" style="margin-top:30px;">
	<a href="register.php">User Management Panel (for testing purpose)</a>
</div>
</body>
</html>
