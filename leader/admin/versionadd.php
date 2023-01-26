<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Add Version");

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

    $oldListing = "";
    $newListing = "";

    $configFile = JPATH_ROOT . "/pq/config/config.json";
    $conts = file_get_contents($configFile);
    if ($conts !== false) {
	    $json = json_decode($conts, true);
	    if ($json !== null) {
		    $packagesURL = $json["packages"]["mac"];
		    //Find if it's latest-1 or latest-2
		    if (preg_match('/\/(yeahboii-.)\//', $packagesURL, $matches)) {
		        $oldListing = $matches[1];

			    if ($matches[1] == "yeahboii-1") {
				    $newListing = "yeahboii-2";
			    } else {
				    $newListing = "yeahboii-1";
			    }
		    } else {
			    $newListing = "";
		    }
	    } else {
		    $newListing = "";
	    }
    } else {
        $newListing = "";
    }

	?>

<style>
.scrolling {
	overflow: scroll;
	max-height: 600px;
}
</style>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Add Version</h1>
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
				The version was created successfully.
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<form action="doversionadd.php" method="POST" class="form" style="margin: 0px auto; width: 550px" autocomplete="off">
				<fieldset>
					<div class="row-fluid">
						<div class="span6">
							<span class="help-block">Version Title:</span>
							<input type="text" class="input-xlarge" name="title" id="title" placeholder="Tile" autocomplete="off">
							<span class="help-block">Version Number:</span>
							<input type="text" class="input-xlarge" name="version" id="version" placeholder="Version (e.g. 3)" autocomplete="off">
							<span class="help-block">Version Download URL:</span>
							<input type="text" class="input-xxlarge" name="url" id="url" placeholder="http://example.com" autocomplete="off">
							<span class="help-block">Switch config-final.json:</span>
							<select class="input-xxlarge" name="config" id=config">
                                <option value="">Don't Switch</option>
                                <option value="yeahboii-1" <?= $newListing === "yeahboii-1" ? "selected" : "" ?>>Latest-1 <?= $oldListing === "yeahboii-1" ? "[Currently Active]" : "" ?></option>
                                <option value="yeahboii-2" <?= $newListing === "yeahboii-2" ? "selected" : "" ?>>Latest-2 <?= $oldListing === "yeahboii-2" ? "[Currently Active]" : "" ?></option>
                            </select>
							<span class="help-block">Version Description:</span>
							<textarea rows="6" class="input-xxlarge" name="desc"></textarea>

							<div class="control-group">
								<div class="controls">
									<button type="submit" class="btn">Add Version</button>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="submitting" value="2">
				</fieldset>
			</form>
			<h1 class="text-center">Old Versions</h1>
			<br>
			<br>
			<div class="scrolling">
			<table class="table table-bordered table-rounded table-striped">
			<tbody>
			<tr>
			<th>Version</th>
			<th>Title</th>
			<th>Description</th>
			<th>URL</th>
			<th>Submitter</th>
			<th>Date</th>
			</tr>
			<?php
			$result = pdo_prepare("SELECT * FROM `versions` ORDER BY `id` DESC")->execute();
			if ($result->rowCount()) {
				while (($row = $result->fetch()) !== false) {
					echo("<tr>");
					echo("<td>{$row["version"]}</td>");
					echo("<td>{$row["title"]}</td>");
					echo("<td>" . nl2br($row["desc"]) . "</td>");
					echo("<td style=\"word-break: break-all\"><a href=\"{$row["url"]}\">{$row["url"]}</a></td>");
					echo("<td>{$row["submitter"]}</td>");
					echo("<td>{$row["timestamp"]}</td>");
					echo("</tr>");
				}
			} else {
				echo("<tr><td colspan='6'>No Versions?</td></tr>");
			}
			?>
			</tbody>
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
