<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Ban/Mute Player");

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
	<script type="text/javascript" src="assets/datetimepicker/jquery.datetimepicker.full.min.js"></script>
	<link rel="stylesheet" href="assets/datetimepicker/jquery.datetimepicker.min.css">

	<script type="text/javascript">

		var toban = "";
		var banbutton = "";
		function ban(player, buttonName) {
			toban = player;
			banbutton = $("#" + buttonName);
			$("#ban-name").text(toban);
			$("#myModal").modal();
		}

		function doban() {
			var player = toban;
			toban = "";
			if (confirm("Ban " + player + "?")) {
				var banbtn = banbutton;
				banbtn.attr("disabled", "disabled");
				banbtn.text("Banning...");
				banbtn.addClass("disabled");

				var params = "user=" + player;
				if ($("#option-mute").is(":checked")) params += "&mute=1";
				if ($("#option-deafen").is(":checked")) params += "&deafen=1";
				if ($("#option-block").is(":checked")) params += "&block=1";

				var message = $("#option-message").val();
				if (message != "") params += "&message=" + message;

				var end = $("#option-end").datetimepicker("getValue");
				end = end.getTime() / 1000;

				params += "&end=" + end;

				$.post("dobanmute.php", params,
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
		function cancelBan(player, id) {
			if (confirm("Unban " + player + "?")) {
				var unbanbtn = $("#cancel-" + id);
				unbanbtn.attr("disabled", "disabled");
				unbanbtn.text("Cancelling...");
				unbanbtn.addClass("disabled");
				$.post("dounbanmute.php", "id=" + id,
					function (data) {
						data = data.split("\n")[0];
						if (data == "GOOD")
							unbanbtn.text("Cancelled");
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
					<div class="span6">
						<h3 class="text-center">Player List</h3>
						<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
							<table class="table table-striped">
								<?php

								$username = getPostValue("username");
								$query = pdo_prepare("SELECT `username`, `access` FROM `users` WHERE `banned` = 0 ORDER BY `username` ASC");
								$result = $query->execute();
								if ($result) {
									while (($row = $result->fetchIdx()) !== false) {
										$button = $row[0];
										$button = strtolower($button);
										$button = preg_replace('/[^a-z0-9]/s', '', $button);
										echo("<tr><td>{$row[0]}</td><td>");
										if (($row[1] < $access || $access == 2) && $row[0] != $username)
											echo("<a href=\"#\" id=\"ban-$button\" onclick=\"ban('{$row[0]}', 'ban-$button');\" class=\"btn btn-danger text-right\">Ban</a>");
										else
											echo("<a class=\"btn btn-danger\" disabled=\"disabled\" href=\"\">Ban</a>");
										echo("</td></tr>");
									}
								}
								?>
							</table>
						</div>
					</div>
					<div class="span6">
						<h3 class="text-center">Current Bans</h3>
						<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
							<table class="table table-striped">
								<tr>
									<th>Username</th>
									<th>Bans</th>
									<th>End (<?=strftime("%Z")?>)</th>
									<th>Cancel</th>
								</tr>
								<?php

								$query = pdo_prepare("SELECT * FROM `bans` WHERE `end` > CURRENT_TIMESTAMP");
								$result = $query->execute();
								if ($result) {
									if ($result->rowCount() == 0) {
										echo("<tr><td colspan='4'>No Current Bans</td></tr>");
									} else {
										while (($row = $result->fetch())) {
											$id = $row["id"];
											$user = $row["username"];
											$message = $row["message"];
											$bans = "";
											if ($row["mute"])   $bans .= "Muted ";
											if ($row["deafen"]) $bans .= "Deafened ";
											if ($row["block"])  $bans .= "Blocked";
											$end = strftime("%F %T", strtotime($row["end"]));
											?>
											<tr>
												<td><?=$user?></td>
												<td><?=$bans?></td>
												<td><?=$end?></td>
												<td><a href="#" id="cancel-<?=$id?>" onclick="cancelBan('<?=$user?>', '<?=$id?>');" class="btn btn-success text-right">Cancel</a></td>
											</tr>
											<tr>
												<td colspan="4">
													Message: "<?=($message === "" ? "No Message" : $message)?>"
												</td>
											</tr>
											<?php
										}
									}
								}
								?>
							</table>
						</div>
						<hr>
						<h3 class="text-center">Expired Bans</h3>
						<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
							<table class="table table-striped">
								<tr>
									<th>Username</th>
									<th>Bans</th>
									<th>End (<?=strftime("%Z")?>)</th>
								</tr>
								<?php

								$query = pdo_prepare("SELECT * FROM `bans` WHERE `end` < CURRENT_TIMESTAMP");
								$result = $query->execute();
								if ($result) {
									if ($result->rowCount() == 0) {
										echo("<tr><td colspan='3'>No Expired Bans</td></tr>");
									} else {
										while (($row = $result->fetch())) {
											$id = $row["id"];
											$user = $row["username"];
											$message = $row["message"];
											$bans = "";
											if ($row["mute"])   $bans .= "Muted ";
											if ($row["deafen"]) $bans .= "Deafened ";
											if ($row["block"])  $bans .= "Blocked";
											$end = strftime("%F %T", strtotime($row["end"]));
											?>
											<tr>
												<td><?=$user?></td>
												<td><?=$bans?></td>
												<td><?=$end?></td>
											</tr>
											<tr>
												<td colspan="3">
													Message: "<?=($message === "" ? "No Message" : $message)?>"
												</td>
											</tr>
											<?php
										}
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
							<h3 id="myModalLabel">Banning <span id="ban-name"></span></h3>
						</div>
						<div class="modal-body">
							<p>Ban Options:</p>
							<p>
								<label for="option-mute"><input type="checkbox" class="input" id="option-mute"> Mute</label>
								<label for="option-deafen"><input type="checkbox" class="input" id="option-deafen"> Deafen</label>
								<label for="option-block"><input type="checkbox" class="input" id="option-block"> Block</label>
								<label for="option-end">End Date/Time (In local time):<br>
									<input type="text" id="option-end"></label>
								<label for="option-message">Enter a message to be shown to the banned player (optional):<br>
									<input type="text" class="input input-xlarge" id="option-message"></label>
							</p>
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

	<script type="text/javascript">
		$("#option-end").datetimepicker({
			format: 'Y-m-d h:i:s'
		});
	</script>

	<?php
} else {
	accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
