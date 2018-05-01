<?php
define('ALLOW_GUEST', TRUE);
require_once('global.php');
?>
<html>
<head>
	<title>User Management Panel</title>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="css/smartlock.css" />
	<link rel="stylesheet" href="css/iconfont.css" />
	<script type="text/javascript" src="js/MooTools-Core-1.5.2.js"></script>
	<script type="text/javascript" src="js/smartlock.js"></script>
	<script type="text/javascript">
	window.addEvent('domready', function() {
		smartLock.userManager.init();
	});
	</script>
</head>
<body>
<div class="titlerow">User Management Panel</div>
<div class="wrapper ac">
	New User Registration
</div>
<div class="wrapper ac" style="margin-top:10px;">
	<form action="manageuser.php" method="post">
		<input class="inputtext s" type="text" name="fullname" placeholder="Full Name" style="width:200px;" />
		<input class="inputtext s" type="text" name="mobilenum" placeholder="Mobile Number" style="width:130px;" />
		<input class="inputtext s" type="password" name="password" placeholder="Password" style="width:120px;" />
		<input class="inputtext s" type="text" name="roomnum" placeholder="Room #" style="width:100px;" />
		<input class="inputtext s" type="text" name="validuntil" placeholder="Expire: YYYY-mm-dd HH:mm:ss" style="width:240px;" />
		<input class="inputbutton s" type="submit" name="submit" value="Add" />
		<input type="hidden" name="act" value="reg" />
	</form>
</div>
<div class="wrapper ac" style="color:red;">Note: There is totally NO input validation nor encryption on browser side as it's just a demo.</div>
<div class="wrapper ac" style="margin-top:30px;">
	Current Registered Users &amp; Room Permissions
</div>
<div class="wrapper" style="margin-top:10px;">
	<table class="userlist" width="100%" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td>Full Name</td>
			<td>Mobile Number</td>
			<td>Register Time</td>
			<td>Room #</td>
			<td>Valid Until</td>
			<td>Options</td>
		</tr>
	</thead>
	<tbody>
	<?php
		$DB->query("SELECT u.id, u.fullname, u.mobilenum, u.regtime, rp.* FROM ".TABLE_PREFIX."user u LEFT JOIN ".TABLE_PREFIX."roomperms rp ON (rp.userid = u.id) ORDER BY u.regtime DESC");
		if ( $DB->num_rows() ) {
			while ( $r = $DB->fetch_array() ) {
	?>
		<tr>
			<td><?php echo $r['fullname']; ?></td>
			<td><?php echo $r['mobilenum']; ?></td>
			<td><?php echo $r['regtime']; ?></td>
			<td><?php echo $r['roomnum']; ?></td>
			<td><?php echo (!$r['expiretime']?'Permanent':$r['expiretime']); ?></td>
			<td><a href="javascript:smartLock.userManager.modUser(<?php echo $r['id']; ?>,<?php echo $r['roomnum']; ?>,'<?php echo (!$r['expiretime']?'':$r['expiretime']); ?>');">[MOD]</a> <a href="javascript:smartLock.userManager.pwdUser(<?php echo $r['id']; ?>);">[PWD]</a> <a href="javascript:smartLock.userManager.delUser(<?php echo $r['id']; ?>);">[DEL]</a></td>
		</tr>
	<?php
			}
		}
	?>		
	</tbody>
	</table>
</div>
</body>
</html>
