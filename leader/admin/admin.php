<?php
$allow_nonwebchat = true;
$admin_page = false; // When this is set to true, the server will reject any non-admin/moderator users

require_once("../opendb.php");
require_once("defaults.php");
require_once("../jsupport.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Main Page");

	//-------------------------------------------------------------------
	// Navbar start
	navbarCreate("MBP Admin");
	navbarAddSet("default");
	if ($access > 1) {
		navbarAddSet("admin");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	} else if ($access == 1) {
		navbarAddSet("mod");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	}
	navbarAddItem("Access: " . accessTitle($access));
	navbarEnd();
	// Navbar end
	//------------------------------------––––––––––––––––––––––––––––---
if (array_key_exists("banned", $_GET) && $_GET["banned"] == true) {
	?>
<div class="alert alert-error" style="margin: 0px 20px 20px 20px">
	<b>Error:</b> That account is banned!
	<button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
	<?php
}
if ($access < 0) {
	?>

	<div class="row">
		<div class="well span8 offset2">
			<h1 class="text-center">Log In</h1>
			<h4 class="text-center">You will need to log in to access this area</h4>
			<form action="../login.php" method="POST" class="form" style="margin: 0px auto; width: 270px;">
				<fieldset>
					<span class="help-block">Username:</span>
					<input type="text" class="input-xlarge" name="username" id="username" placeholder="Username">
					<span class="help-block">Password:</span>
					<input type="password" class="input-xlarge" name="password" id="password" placeholder="password">
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn">Log In</button>
						</div>
					</div>
					<input type="hidden" name="submitting" value="2">
					<input type="hidden" name="continue" value="<?php if (array_key_exists("continue", $_GET)) echo($_GET["continue"]);?>">
				</fieldset>
			</form>
		</div>
	</div>

	<?php
} else if ($access >= MINIMUM_ACCESS) {
	?>

<div class="container-fluid">
	<div class="row-fluid">
	<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">MBP Admin Panel</h1>
			<div class="row-fluid">
				<div class="span8">
					<br>
					<br>
					<p>
						Welcome to the MBP admin panel. From here you can monitor and modify most of the backend system that is used ingame.<br>
						Available modules can be accessed via the sidebar or the menus.
					</p>
					<p class="well">If a chat server happens to go down (or hang), press one of the scary-looking buttons below!<br>
						If you press one of these while the servers are up, it will restart the server, kicking everyone offline.<br>
						<b>Please notify someone if you are going to restart this in the event that we need to debug it. Thanks.</b><br>
						<a href="dochatrestart.php?port=28002" class="btn btn-danger">Restart Public Server</a>
						<a href="domasterrestart.php" class="btn btn-danger">Restart MP Master Server</a>
						<a <?php /* href="dochatrestart.php?port=39002" */ ?> class="btn btn-danger" disabled="disabled">Restart New Dev Server [WIP]</a>
					</p>
				</div>
				<div class="span4">
					<h2 class="text-center">Players Online</h2>
					<table class="table table-striped table-bordered">
						<tbody>
							<tr>
								<th>User</th>
							</tr>
							<?php

$query = pdo_prepare("SELECT `username`, `access`, `location` FROM `loggedin` UNION SELECT `username`, `access`, `location` FROM `jloggedin`");
$result = $query->execute();
if ($result && $result->rowCount() > 0) {
	while (($row = $result->fetchIdx()) !== false) {
		$username = $row[0];
		$display = getDisplayName($username);
		echo("<tr><td>$display</td></tr>");
	}
} else
	echo("<tr><td>No Players Online</td></tr>");

							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

	<?php
} else {
	//Below minimum access
?>
	<div class="row">
		<div class="well span8 offset2">
			<h1 class="text-center">Access Denied</h1>
			<hr>
			<p>Yeah nice try, but your access (<?php echo(accessTitle($access)); ?>) does not meet the minimum requirement (<?php echo(accessTitle(MINIMUM_ACCESS)); ?>). Have a nice day.</p>
			<form action="../logout.php" method="GET" class="form" style="">
				<fieldset>
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn">Log Out</button>
						</div>
					</div>
					<input type="hidden" name="admin" value="true">
				</fieldset>
			</form>
		</div>
	</div>
<?php
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
