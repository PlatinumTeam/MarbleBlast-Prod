<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("View User Data");

	//-------------------------------------------------------------------
	// Navbar start

	navbarCreate("MBP Admin");
	navbarAddSet("default");
	if ($access > 1) {
		navbarAddSet("admin");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	}
	navbarAddItem("Access: " . accessTitle($access));
	navbarEnd();

	// Navbar end
	//------------------------------------––––––––––––––––––––––––––––---

if ($access > 1) {
	?>

<style type="text/css">
form.inlineForm {
	display: inline-block;
	margin: 0px;
	padding: 0px;
	padding-right: 10px;
}
</style>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">View User Data</h1>
			<br>
			<br>
			<h3 class="text-center">Player List</h3>
			<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
				<table class="table table-striped">
				<?php

list($username) = getPostValues("username");
$query = pdo_prepare("SELECT `username`, `access` FROM `users` ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	if ($result->rowCount() == 0) { ?>
		<tr><td>No Users!</td></tr>
	<?php }
	while (($row = $result->fetchIdx()) !== false) {
		echo("<tr><td>{$row[0]} (" . getDisplayName($row[0]) . ")</td><td style=\"text-align: right;\">");
		echo("<form action=\"useredit.php\" method=\"POST\" class=\"inlineForm\"><input type=\"submit\" value=\"Edit\" class=\"btn btn-success text-right\"><input type=\"hidden\" name=\"edituser\" value=\"{$row[0]}\"></form>");
		if (($row[1] < $access || $access == 2) && $row[0] != $username)
			echo("<a href=\"#\" id=\"kick-{$row[0]}\" onclick=\"kick('{$row[0]}');\" class=\"btn btn-danger text-right\">Delete</a>");
		else
			echo("<a class=\"btn btn-danger\" disabled=\"disabled\" href=\"\">Delete</a>");
		echo("</td></tr>");
	}
} else { ?>
		<tr><td>No Users!</td></tr>
	<?php }

				?>
				</table>
			</div>
		</div>
	</div>
</div>

	<?php
} else {
	accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
