<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Trasfer User Data");

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
<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Trasfer User Data</h1>
			<br>
			<br>
			<div class="row-fluid">
				<div class="span6">
					<h3 class="text-center" id="usertitle1">From Player</h3>
					<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
						<?php
$username = getPostValue("username");
$query = pdo_prepare("SELECT `username`, `access` FROM `users` ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	while (($row = $result->fetchIdx()) !== false) {
		echo("<tr class=\"selector1-tr\"><td>{$row[0]}</td><td>");
		if (($row[1] < $access || $access == 2) && $row[0] != $username)
			echo("<a href=\"#\" data-user=\"{$row[0]}\" class=\"btn btn-primary text-right selector1\">Select</a>");
		else
			echo("<a class=\"btn btn-primary\" disabled=\"disabled\" href=\"\">Select</a>");
		echo("</td></tr>");
	}
}

						?>
						</table>
					</div>
				</div>
				<div class="span6">
					<h3 class="text-center" id="usertitle2">To Player</h3>
					<div style="max-height: 400px; overflow-y: scroll; overflow-x: auto;">
						<table class="table table-striped">
						<?php
$query = pdo_prepare("SELECT `username`, `access` FROM `users` ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	while (($row = $result->fetchIdx()) !== false) {
		echo("<tr class=\"selector2-tr\"><td>{$row[0]}</td><td>");
		if (($row[1] < $access || $access == 2) && $row[0] != $username)
			echo("<a href=\"#\" data-user=\"{$row[0]}\" class=\"btn btn-primary text-right selector2\">Select</a>");
		else
			echo("<a class=\"btn btn-primary\" disabled=\"disabled\" href=\"\">Select</a>");
		echo("</td></tr>");
	}
}
						?>
						</table>
					</div>
				</div>
			</div>
			<br>
			<div class="text-center">
				<button id="submit" disabled="disabled" class="btn btn-success">Transfer</button>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	.selected {
		background-color: #9f9;
	}
	.selected td {
		background-color: #9f9 !important;
	}
</style>
<script type="text/javascript">
	var user1 = undefined;
	var user2 = undefined;
	$(".selector1").click(function(e) {
		var $this = $(this);
		var user = $this.attr("data-user");
		$("#usertitle1").text("From Player " + user);
		$(".selector1-tr.selected").removeClass("selected");
		$this.parent().parent().addClass("selected");
		user1 = user;
		if (typeof(user1) !== "undefined" && typeof(user2) !== "undefined") {
			finish();
		}
	});
	$(".selector2").click(function(e) {
		var $this = $(this);
		var user = $this.attr("data-user");
		$("#usertitle2").text("To Player " + user);
		$(".selector2-tr.selected").removeClass("selected");
		$this.parent().parent().addClass("selected");
		user2 = user;
		if (typeof(user1) !== "undefined" && typeof(user2) !== "undefined") {
			finish();
		}
	});
	function finish() {
//		$.post("dotransferuser.php", "from=" + user1 + "&to=" + user2)
	}
</script>
	<?php
} else {
	accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
