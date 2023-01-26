<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Mute Player");

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
var tomute = "";
function mute(player) {
	tomute = player;
	$("#myModal").modal();
}

function domute() {
	var player = tomute;
	tomute = "";
	if (confirm("Mute " + player + "?")) {
		var mutebtn = $("#mute-" + player);
		mutebtn.attr("disabled", "disabled");
		mutebtn.text("mutening...");
		mutebtn.addClass("disabled");
		var message = $("#message").val();
		$.post("domute.php", "user=" + player + "&message=" + message,
			function (data) {
				data = data.split("\n")[0];
				if (data == "GOOD")
					mutebtn.text("mutened");
				else
					mutebtn.text("Error");
			}
		);
	}
}
function unmute(player) {
	if (confirm("Unmute " + player + "?")) {
		var unmutebtn = $("#unmute-" + player);
		unmutebtn.attr("disabled", "disabled");
		unmutebtn.text("Unmutening...");
		unmutebtn.addClass("disabled");
		$.post("dounmute.php", "user=" + player,
			function (data) {
				data = data.split("\n")[0];
				if (data == "GOOD")
					unmutebtn.text("Unmutened");
				else
					unmutebtn.text("Error");
			}
		);
	}
}
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">mute Players</h1>
			<br>
			<br>
			<div class="row-fluid">
				<div class="span6">
					<h3 class="text-center">Player List</h3>
					<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
						<?php

$username = getPostValue("username");
$query = pdo_prepare("SELECT `username`, `access` FROM `loggedin` ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	while (($row = $result->fetchIdx()) !== false) {
		echo("<tr><td>{$row[0]}</td><td>");
		if (($row[1] < $access || $access == 2) && $row[0] != $username)
			echo("<a href=\"#\" id=\"mute-{$row[0]}\" onclick=\"mute('{$row[0]}');\" class=\"btn btn-danger text-right\">Man</a>");
		else
			echo("<a class=\"btn btn-danger\" disabled=\"disabled\" href=\"\">Man</a>");
		echo("</td></tr>");
	}
}

						?>
						</table>
					</div>
				</div>
				<!-- Modal -->
				<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 id="myModalLabel">Enter Time</h3>
					</div>
					<div class="modal-body">
						<p>For how long should the player be muted?</p>
						<input type="text" class="input input-xlarge" id="time"><br>
					</div>
					<div class="modal-footer">
						<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
						<button class="btn btn-primary" data-dismiss="modal" onclick="domute();">Mute Player</button>
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
