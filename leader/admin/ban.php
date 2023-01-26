<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Ban Player");

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
var toban = "";
var totype = 0;
var banbtn;

function mute(player, btnname) {
	$("#muteModalLabel").text("Muting " + player);
	toban = player;
	banbtn = $(".ban-" + btnname);
	$("#muteModal").modal();
}

function domute() {
	var player = toban;
	toban = "";
	if (confirm("Mute " + player + "? (Note: use /mute if they're on webchat)")) {
		banbtn.attr("disabled", "disabled");
		banbtn.text("Muting...");
		banbtn.addClass("disabled");
		var length = parseInt($("#mutelength").val());
		$.post("domute.php", "user=" + player + "&length=" + length,
			function (data) {
				data = data.split("\n")[0];
				if (data == "GOOD")
					banbtn.text("Muted");
				else
					banbtn.text("Error");
			}
		);
	}
}
function unmute(player, btnname) {
	if (confirm("Unmute " + player + "? (Note: use /unmute if they're on webchat)")) {
		var unmutebtn = $("#unmute-" + btnname);
		unmutebtn.attr("disabled", "disabled");
		unmutebtn.text("Unmuting...");
		unmutebtn.addClass("disabled");
		$.post("dounmute.php", "user=" + player,
			function (data) {
				data = data.split("\n")[0];
				if (data == "GOOD")
					unmutebtn.text("Unmuted");
				else
					unmutebtn.text("Error");
			}
		);
	}
}

function ban1(player, btnname) {
	$("#banModalLabel").text("Chat Banning " + player);
	toban = player;
	totype = 1;
	banbtn = $(".ban-" + btnname);
	$("#banModal").modal();
}
function ban2(player, btnname) {
	$("#banModalLabel").text("Banning " + player);
	toban = player;
	totype = 2;
	banbtn = $(".ban-" + btnname);
	$("#banModal").modal();
}
function ban3(player, btnname) {
	$("#banModalLabel").text("IP-Banning " + player);
	toban = player;
	totype = 3;
	banbtn = $(".ban-" + btnname);
	$("#banModal").modal();
}

function doban() {
	var player = toban;
	var type = totype;
	toban = "";
	if (confirm("Ban " + player + "?")) {
		banbtn.attr("disabled", "disabled");
		banbtn.text("Banning...");
		banbtn.addClass("disabled");
		var message = $("#message").val();
		$.post("doban.php", "user=" + player + "&message=" + message + "&banType=" + type,
			function (data) {
				data = data.split("\n")[0];
				if (data == "GOOD")
					banbtn.text("Banned");
				else
					banbtn.text("Error");
			}
		);
	}
}
function unban(player, btnname) {
	if (confirm("Unban " + player + "?")) {
		var unbanbtn = $("#unban-" + btnname);
		unbanbtn.attr("disabled", "disabled");
		unbanbtn.text("Unbanning...");
		unbanbtn.addClass("disabled");
		$.post("dounban.php", "user=" + player,
			function (data) {
				data = data.split("\n")[0];
				if (data == "GOOD")
					unbanbtn.text("Unbanned");
				else
					unbanbtn.text("Error");
			}
		);
	}
}
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Ban Players</h1>
			<br>
			<br>
			<div class="row-fluid">
				<div class="span12">
					<h3 class="text-center">Player List</h3>
					<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
						<?php

$username = getPostValue("username");
$query = pdo_prepare("SELECT `username`, `access` FROM `users` WHERE `banned` = 0 ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	while (($row = $result->fetchIdx()) !== false) {
		echo("<tr><td>{$row[0]}</td><td>");
		$short = $row[0];
		$short = preg_replace('/[^a-z0-9]/s', '', $short);
		if (($row[1] < $access || $access == 2) && $row[0] != $username) {
			echo("<a href=\"#\" id=\"mute-{$short}\" onclick=\"mute('{$row[0]}', '$short');\" class=\"btn btn-danger text-right ban-{$short}\">Mute</a> ");
			echo("<a href=\"#\" id=\"ban1-{$short}\" onclick=\"ban1('{$row[0]}', '$short');\" class=\"btn btn-danger text-right ban-{$short}\">Chat Ban</a> ");
			echo("<a href=\"#\" id=\"ban2-{$short}\" onclick=\"ban2('{$row[0]}', '$short');\" class=\"btn btn-danger text-right ban-{$short}\">Ban</a> ");
			echo("<a href=\"#\" id=\"ban3-{$short}\" onclick=\"ban3('{$row[0]}', '$short');\" class=\"btn btn-danger text-right ban-{$short}\">IP Ban</a>");
		} else {
			echo("<a class=\"btn btn-danger\" disabled=\"disabled\" href=\"\">Ban</a>");
		}
		echo("</td></tr>");
	}
}

						?>
						</table>
					</div>
				</div>
			</div>
			<div class="row-fluid">
                <div class="span6">
                    <h3 class="text-center">Muted Players</h3>
                    <div style="max-height: 200px; overflow-y: scroll; overflow-x: auto;">
                        <table class="table table-striped">
							<?php
$username = getPostValue("username");
$query = pdo_prepare("SELECT `username`, `access`, `muteIndex` FROM `users` WHERE `muteIndex` > 0 ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
    while (($row = $result->fetchIdx()) !== false) {
        $short = $row[0];
        $duration = $row[2] * 30;
        $short = preg_replace('/[^a-z0-9]/s', '', $short);
        echo("<tr><td>{$row[0]}</td><td>$duration</td><td><a href=\"#\" id=\"unmute-{$short}\" onclick=\"unmute('{$row[0]}', '$short');\" class=\"btn btn-success text-right\">Unmute</a></td></tr>");
    }
}
							?>
                        </table>
                    </div>
                </div>
				<div class="span6">
					<h3 class="text-center">Chat Banned Players</h3>
					<div style="max-height: 200px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
						<?php

$query = pdo_prepare("SELECT `username` FROM `users` WHERE `banned` = 1 ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	if ($result->rowCount() == 0) {
		echo("<tr><td>No Banned Players</td></tr>");
	} else {
		while (($row = $result->fetchIdx())) {
			$short = $row[0];
			$short = preg_replace('/[^a-z0-9]/s', '', $short);
			echo("<tr><td>{$row[0]}</td><td><a href=\"#\" id=\"unban-$short\" onclick=\"unban('{$row[0]}', '$short');\" class=\"btn btn-success text-right\">Unban</a></td></tr>");
		}
	}
}

						?>
						</table>
					</div>
				</div>
            </div>
            <div class="row-fluid">
				<div class="span6">
					<h3 class="text-center">Banned Players</h3>
					<div style="max-height: 200px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
							<?php

$query = pdo_prepare("SELECT `username` FROM `users` WHERE `banned` = 2 ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	if ($result->rowCount() == 0) {
		echo("<tr><td>No Banned Players</td></tr>");
	} else {
		while (($row = $result->fetchIdx())) {
			$short = $row[0];
			$short = preg_replace('/[^a-z0-9]/s', '', $short);
			echo("<tr><td>{$row[0]}</td><td><a href=\"#\" id=\"unban-$short\" onclick=\"unban('{$row[0]}', '$short');\" class=\"btn btn-success text-right\">Unban</a></td></tr>");
		}
	}
}

							?>
						</table>
					</div>
				</div>
				<div class="span6">
					<h3 class="text-center">IP Banned Players</h3>
					<div style="max-height: 200px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
							<?php

$query = pdo_prepare("SELECT `username` FROM `users` WHERE `banned` = 3 ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	if ($result->rowCount() == 0) {
		echo("<tr><td>No Banned Players</td></tr>");
	} else {
		while (($row = $result->fetchIdx())) {
			$short = $row[0];
			$short = preg_replace('/[^a-z0-9]/s', '', $short);
			echo("<tr><td>{$row[0]}</td><td><a href=\"#\" id=\"unban-$short\" onclick=\"unban('{$row[0]}', '$short');\" class=\"btn btn-success text-right\">Unban</a></td></tr>");
		}
	}
}

							?>
						</table>
					</div>
				</div>
				<!-- Mute Modal -->
				<div id="muteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="muteModalLabel" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 id="muteModalLabel">Enter Mute Length</h3>
					</div>
					<div class="modal-body">
						<p>How long (seconds)? Will set mute length to this value, max 86400. Note this does nothing if the player is on webchat, use /mute instead in that case.</p>
                        <p>
                            For reference:
                            <ul>
                                <li>15 minutes: 900</li>
                                <li>30 minutes: 1800</li>
                                <li>1 hour: 3600</li>
                                <li>24 hours: 86400</li>
                            </ul>
                        </p>
						<input type="text" class="input input-xlarge" id="mutelength"><br>
					</div>
					<div class="modal-footer">
						<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
						<button class="btn btn-primary" data-dismiss="modal" onclick="domute();">Mute Player</button>
					</div>
				</div>
                <!-- Ban Modal -->
                <div id="banModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="banModalLabel" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 id="banModalLabel">Enter Message</h3>
                    </div>
                    <div class="modal-body">
                        <p>Enter a message to be shown to the banned player (optional):</p>
                        <input type="text" class="input input-xlarge" id="message"><br>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                        <button class="btn btn-primary" data-dismiss="modal" onclick="doban();">Ban Player</button>
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
