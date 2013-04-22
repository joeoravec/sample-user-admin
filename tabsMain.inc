<?php
$chkUserType = $_SESSION['userType'];

$url = $_SERVER['PHP_SELF'];
$testurl = parse_url($url, PHP_URL_PATH);
?>
<div id="tabs">
	<ul id="menu">
    	<li <?php if ($testurl == '/cmapp/main.php') echo "class=\"active\""; ?>><a href="main.php">Case Listing</a></li>
    	<li <?php if ($testurl == '/cmapp/progressMain.php') echo "class=\"active\""; ?>><a href="progressMain.php">Reports</a></li>
	<?php
	if ($chkUserType == '2') {
	?>
    	<li <?php if ($testurl == '/cmapp/adminTools.php') echo "class=\"active\""; ?>><a href="adminTools.php">Admin Tools</a></li>
    	<li <?php if ($testurl == '/cmapp/adminUser.php') echo "class=\"active\""; ?>><a href="adminUser.php">Manage Users</a></li>
    	<li <?php if ($testurl == '/cmapp/adminClient.php') echo "class=\"active\""; ?>><a href="adminClient.php">Manage Clients</a></li>
	<?php
	}
	?>
    	<li <?php if ($testurl == '/cmapp/adminPatient.php') echo "class=\"active\""; ?>><a href="adminPatient.php">Manage Patients</a></li>
    </ul>
</div>
<div id="menubar"></div>