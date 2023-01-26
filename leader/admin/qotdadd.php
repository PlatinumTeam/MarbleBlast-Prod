<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Set QOTD");

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

if ($access > (MINIMUM_ACCESS - 1)) {
	?>

<style>
.scrolling {
	overflow: scroll;
	max-height: 600px;
}
#qotd-table tr td {
	word-wrap: break-word;
	max-width: 500px;
}
</style>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Set QOTD</h1>
			<br>
			<br>
			<?php if (isset($_GET["error"])) {?>
			<div class="alert alert-danger">
				There was an error creating the QOTD. The error was: <b><?php
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
				The QOTD was updated successfully.
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<form action="doqotdadd.php" method="POST" class="form" style="margin: 0px auto; width: 550px" autocomplete="off">
				<fieldset>
					<div class="row-fluid">
						<div class="span6">
							<span class="help-block">New QOTD:</span>
							<input class="input-xxlarge" type="text" name="user" placeholder="Username">
							<textarea rows="6" class="input-xxlarge" name="qotd" placeholder="Quote"></textarea>
							<div class="control-group">
								<div class="controls">
									<button type="submit" class="btn">Update QOTD</button>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="submitting" value="2">
				</fieldset>
			</form>
			<h1 class="text-center">Old QOTDs</h1>
			<br>
			<br>
			<div class="scrolling">
			<table id="qotd-table" class="table table-bordered table-rounded table-striped">
			<tbody>
			<tr>
			<th>Text</th>
			<th>Username</th>
			<th>Submitter</th>
			<th>Date</th>
			<th>Toggle</th>
			</tr>
			<?php
			$result = pdo_prepare("SELECT * FROM `qotd` ORDER BY `id` DESC")->execute();
			if ($result->rowCount()) {
				while (($row = $result->fetch()) !== false) { ?>
<tr>
<td><?=nl2br(htmlspecialchars($row["text"]))?></td>
<td><?=getDisplayName($row["username"])?></td>
<td><?=getDisplayName($row["submitter"])?></td>
<td><?=$row["timestamp"]?></td>
<td><form action="doqotdadd.php" method="POST">
<input type="hidden" name="toggle" value="<?=$row["id"]?>">
<button type="submit" class="btn <?=$row["selected"]?"btn-danger":"btn-success"?>"><?=$row["selected"]?"Deactivate":"Activate"?></button>
</form></td>
</tr>
<?php
				}
			} else {
				echo("<tr><td colspan='3'>No QOTDs?</td></tr>");
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
