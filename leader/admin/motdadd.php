<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Set MOTD");

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

<style>
.scrolling {
	overflow: scroll;
	max-height: 600px;
}
#motd-table tr td {
	word-wrap: break-word;
	max-width: 500px;
}
</style>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Set MOTD</h1>
			<br>
			<br>
			<?php if (isset($_GET["error"])) {?>
			<div class="alert alert-danger">
				There was an error creating the version. The error was: <b><?php
				switch ($_GET["error"]) {
					case 0: echo("There was an internal server error"); break;
					default: echo("There was an internal server error"); break;
				}
				?></b>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<?php if (isset($_GET["success"])) {?>
			<div class="alert alert-success">
				The MOTD was updated successfully.
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<form action="domotdadd.php" method="POST" class="form" style="margin: 0px auto; width: 550px" autocomplete="off">
				<fieldset>
					<div class="row-fluid">
						<div class="span6">
							<span class="help-block">New MOTD:</span>
							<textarea rows="6" class="input-xxlarge" name="motd"><?php
								$query = pdo_prepare("SELECT `message` FROM `motd` ORDER BY `id` DESC LIMIT 1");
								$result = $query->execute();

								if ($result->rowCount()) {
									$row = $result->fetch();

									$message = substr(str_replace(array("\r\n", "\r", "\n"), "\n", addslashes($row["message"])), 0, 999);

									echo("$message");
								} else {
									echo(getServerPref("defaultmotd"));
								}
								?></textarea>

							<div class="control-group">
								<div class="controls">
									<button type="submit" class="btn">Update MOTD</button>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="submitting" value="2">
				</fieldset>
			</form>
			<h1 class="text-center">Old MOTDs</h1>
			<br>
			<br>
			<div class="scrolling">
			<table id="motd-table" class="table table-bordered table-rounded table-striped">
			<tbody>
			<tr>
			<th>Message</th>
			<th>Submitter</th>
			<th>Date</th>
			</tr>
			<?php
			$result = pdo_prepare("SELECT * FROM `motd`")->execute();
			if ($result->rowCount()) {
				while (($row = $result->fetch()) !== false) {
					echo("<tr>");
					echo("<td>" . nl2br(htmlspecialchars($row["message"])) . "</td>");
					echo("<td>{$row["submitter"]}</td>");
					echo("<td>{$row["timestamp"]}</td>");
					echo("</tr>");
				}
			} else {
				echo("<tr><td colspan='3'>No MOTDs?</td></tr>");
			}
			?>
			</tbody>
			</table>
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
