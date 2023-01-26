<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Server Settings");

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

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Server Settings</h1>
			<br>
			<br>
			<form action="dosettings.php" method="POST" style="margin-bottom: 0px">
				<table class="table table-bordered table-striped" style="margin-bottom: 0px">
					<tr>
						<th>Setting</th>
						<th>Value</th>
					</tr>
				<?php

$query = pdo_prepare("SELECT * FROM `settings` ORDER BY `displayname` ASC");
$result = $query->execute();
while (($row = $result->fetch()) !== false) {
	echo("<tr><td>");
	echo($row["displayname"] . ":");
	echo("</td><td>");

	switch ($row["type"]) {
		case -1:
			echo("<span class=\"input-xlarge uneditable-input\">{$row['value']}</span><input type=\"hidden\" name=\"settings[{$row['key']}]\" value=\"{$row['value']}\">");
			break;
		case 0:
			echo("<input class=\"input-xlarge\" type=\"text\" name=\"settings[{$row['key']}]\" placeholder=\"{$row['default']}\" value=\"{$row['value']}\">");
			break;
		case 1:
			echo("<textarea rows=\"6\" class=\"input-xxlarge\" name=\"settings[{$row['key']}]\">" . str_replace(array("\r\n", "\\r\\n", "\n", "\\n"), "&#10;", $row['value']) . "</textarea>");
		default:
			break;
	}
	echo("</td></tr>");
}
				?>
					<tr>
						<td colspan="2">
							<div class="text-center">
								<input type="submit" class="btn btn-primary" value="Update Settings">
							</div>
						</td>
					</tr>
				</table>
			</form>
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
