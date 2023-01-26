<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");
require_once("../jsupport.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Kick Player");

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

if ($access > (MINIMUM_ACCESS - 1)) {
	?>

<script type="text/javascript">
var tokick = "";
function kick(user) {
	tokick = user;
	$('#myModal').modal();
}
function dokick() {
	var user = tokick;
	var kickbtn = $("#kick-" + user);
	kickbtn.attr("disabled", "disabled");
	kickbtn.text("Kicking...");
	kickbtn.addClass("disabled");
	var message = encodeURIComponent($("#message").val());
	var ban = $("#doban").is(":checked");
	$.post("dokick.php", "user=" + user + "&message=" + message + "&ban=" + ban,
		function (data) {
			data = data.split("\n")[0];
			if (data == "GOOD")
				kickbtn.text("Kicked");
			else
				kickbtn.text("Error");
		}
	);
}
<?php 
if ($access > 1) {
?>
function kickall() {
	if (!confirm("Are you sure?"))
		return;
	if (!confirm("I mean like really. Don't do this if you don't have to."))
		return;
	if (!confirm("Sure?"))
		return;
	if (!confirm("OK will do it after this message box."))
		return;
	var kickbtn = $("#kickall");
	kickbtn.attr("disabled", "disabled");
	kickbtn.text("Kicking...");
	kickbtn.addClass("disabled");
	$.post("dokickall.php", "",
		function (data) {
			data = data.split("\n")[0];
			if (data == "GOOD")
				kickbtn.text("Kicked");
			else
				kickbtn.text("Error");
		}
	);
}
<?php 
}
?>
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Kick Players</h1>
			<br>
			<br>
			<h3 class="text-center">Player List</h3>
			<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
				<table class="table table-striped">
				<?php

list($username) = getPostValues("username");
$query = pdo_prepare("(SELECT `username`, `access` FROM `loggedin`) UNION (SELECT `username`, `access` FROM `jloggedin`) ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	if ($result->rowCount() == 0) { ?>
		<tr><td>No Users Online!</td></tr>
	<?php }
	while (($row = $result->fetchIdx())) {
		$user = $row[0];
		$display = getDisplayName($user);
		echo("<tr><td>$display</td><td style=\"text-align: right;\">");
		if (($row[1] < $access || $access == 2 || $row[1] == 3) && $row[0] != $username)
			echo("<a href=\"#\" id=\"kick-{$row[0]}\" onclick=\"kick('{$row[0]}');\" class=\"btn btn-danger text-right\">Kick</a>");
		else
			echo("<a class=\"btn btn-danger\" disabled=\"disabled\" href=\"\">Kick</a>");
		echo("</td></tr>");
	}
} else { ?>
		<tr><td>No Users Online!</td></tr>
	<?php }

				?>
				</table>
<?php 
if ($access > 1) {
?>
				<a type="button" class="btn btn-danger" id="kickall" href="#" onclick="kickall();">Kick <em>ALL</em> the people!</a>
<?php
}
?>
				<!-- Modal -->
				<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 id="myModalLabel">Enter Message</h3>
					</div>
					<div class="modal-body">
						<p>Enter a message to be shown to the kicked player (optional):</p>
						<input type="text" class="input input-xlarge" id="message"><br>
						<label class="checkbox"><input type="checkbox" id="doban" name="doban">Ban Player</label>
					</div>
					<div class="modal-footer">
						<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
						<button class="btn btn-primary" data-dismiss="modal" onclick="dokick();">Kick Player</button>
					</div>
				</div>
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
