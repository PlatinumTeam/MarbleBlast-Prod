<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Recalculate Scores");

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

<script type="text/javascript">
function recal(user) {
	$("#output").removeClass("hidden");
	$(".recalBtn").each(function() {
		this.setAttribute("disabled", "disabled");
		this.setAttribute("onclick2", this.getAttribute("onclick"));
		this.setAttribute("onclick", "");
	});
	$("#recalAll")[0].setAttribute("disabled", "disabled");
	$("#recalAll")[0].setAttribute("onclick2", $("#recalAll")[0].getAttribute("onclick"));
	$("#recalAll")[0].setAttribute("onclick", "");
	$("#recalMp")[0].setAttribute("disabled", "disabled");
	$("#recalMp")[0].setAttribute("onclick2", $("#recalMp")[0].getAttribute("onclick"));
	$("#recalMp")[0].setAttribute("onclick", "");

	user = encodeURIComponent(user);
	var request;
	if (window.XMLHttpRequest)
		request = new XMLHttpRequest;
	else
		request = new ActiveXObject("Microsoft.XMLHTTP");
	//alert("Made the object!");

	request.onreadystatechange = function() {
		if (request.readyState == 4 && request.status == 200) {
			var text = nl2br(request.responseText);
			text = text.replace(/>!/g, "><span style=\"color: #f00; font-weight: bold;\">");
			text = text.replace(/!</g, "</span><");
			$("#output").html(text);
			$(".recalBtn").each(function() {
				this.removeAttribute("disabled");
				this.setAttribute("onclick", this.getAttribute("onclick2"));
				this.removeAttribute("onclick2");
			});
			$("#recalAll")[0].removeAttribute("disabled");
			$("#recalAll")[0].setAttribute("onclick", $("#recalAll")[0].getAttribute("onclick2"));
			$("#recalAll")[0].removeAttribute("onclick2");
			$("#recalMp")[0].removeAttribute("disabled");
			$("#recalMp")[0].setAttribute("onclick", $("#recalMp")[0].getAttribute("onclick2"));
			$("#recalMp")[0].removeAttribute("onclick2");
		}
	}

	request.open("POST", "dorecal.php", true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	var tosend = "user=" + user;
	request.send(tosend);
}

function nl2br(str) {
	return str.replace(/\n/g, "<br>")
				 .replace(/\r/g, "<br>");
}

function recalall() {
	var allUsers = <?php
$last = getServerPref("lastrecalc");
$query = pdo_prepare("SELECT `username` FROM `users` WHERE UNIX_TIMESTAMP(`lastaction`) > :last ORDER BY `rating` DESC");
$query->bind(":last", $last);
$result = $query->execute();
if ($result) {
	$users = array();
	while (($row = $result->fetchIdx())) {
		array_push($users, $row[0]);
	}
	echo(json_encode($users));
} else {
	echo(json_encode(array()));
}
	?>;

	$("#output").html("<h1>This is going to take longer than your mother takes climbing a flight of stairs...</h1>");
	$("#output").removeClass("hidden");
	$(".recalBtn").each(function() {
		this.setAttribute("disabled", "disabled");
		this.setAttribute("onclick2", this.getAttribute("onclick"));
		this.setAttribute("onclick", "");
	});
	$("#recalAll")[0].setAttribute("disabled", "disabled");
	$("#recalAll")[0].setAttribute("onclick2", $("#recalAll")[0].getAttribute("onclick"));
	$("#recalAll")[0].setAttribute("onclick", "");
	$("#recalMp")[0].setAttribute("disabled", "disabled");
	$("#recalMp")[0].setAttribute("onclick2", $("#recalMp")[0].getAttribute("onclick"));
	$("#recalMp")[0].setAttribute("onclick", "");

	var prog = $("#recalallprogress");

	doreq = function(userNum) {
		prog.width((userNum / allUsers.length) * 100 + "%");

		user = encodeURIComponent(allUsers[userNum]);
		var request;
		if (window.XMLHttpRequest)
			request = new XMLHttpRequest;
		else
			request = new ActiveXObject("Microsoft.XMLHTTP");
		//alert("Made the object!");

		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				var text = nl2br(request.responseText);
				text = text.replace(/>!/g, "><span style=\"color: #f00; font-weight: bold;\">");
				text = text.replace(/!</g, "</span><");
				$("#output").html(text + "<br>" + $("#output").html());
				$(".recalBtn").each(function() {
					this.removeAttribute("disabled");
					this.setAttribute("onclick", this.getAttribute("onclick2"));
					this.removeAttribute("onclick2");
				});
				$("#recalAll")[0].removeAttribute("disabled");
				$("#recalAll")[0].setAttribute("onclick", $("#recalAll")[0].getAttribute("onclick2"));
				$("#recalAll")[0].removeAttribute("onclick2");
				$("#recalMp")[0].removeAttribute("disabled");
				$("#recalMp")[0].setAttribute("onclick", $("#recalMp")[0].getAttribute("onclick2"));
				$("#recalMp")[0].removeAttribute("onclick2");

				if (userNum + 1 < allUsers.length)
					doreq(userNum + 1);
			}
		}

		request.open("POST", "dorecal.php", true);
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		var tosend = "user=" + user + "&reall=true";
		request.send(tosend);
	};

	doreq(0);
}

function recalmp() {
	$("#output").removeClass("hidden");
	$(".recalBtn").each(function() {
		this.setAttribute("disabled", "disabled");
		this.setAttribute("onclick2", this.getAttribute("onclick"));
		this.setAttribute("onclick", "");
	});
	$("#recalAll")[0].setAttribute("disabled", "disabled");
	$("#recalAll")[0].setAttribute("onclick2", $("#recalAll")[0].getAttribute("onclick"));
	$("#recalAll")[0].setAttribute("onclick", "");
	$("#recalMp")[0].setAttribute("disabled", "disabled");
	$("#recalMp")[0].setAttribute("onclick2", $("#recalMp")[0].getAttribute("onclick"));
	$("#recalMp")[0].setAttribute("onclick", "");

	var request;
	if (window.XMLHttpRequest)
		request = new XMLHttpRequest;
	else
		request = new ActiveXObject("Microsoft.XMLHTTP");
	//alert("Made the object!");

	request.onreadystatechange = function() {
		if (request.readyState == 4 && request.status == 200) {
			var text = nl2br(request.responseText);
			text = text.replace(/>!/g, "><span style=\"color: #f00; font-weight: bold;\">");
			text = text.replace(/!</g, "</span><");
			$("#output").html(text);
			$(".recalBtn").each(function() {
				this.removeAttribute("disabled");
				this.setAttribute("onclick", this.getAttribute("onclick2"));
				this.removeAttribute("onclick2");
			});
			$("#recalAll")[0].removeAttribute("disabled");
			$("#recalAll")[0].setAttribute("onclick", $("#recalAll")[0].getAttribute("onclick2"));
			$("#recalAll")[0].removeAttribute("onclick2");
			$("#recalMp")[0].removeAttribute("disabled");
			$("#recalMp")[0].setAttribute("onclick", $("#recalMp")[0].getAttribute("onclick2"));
			$("#recalMp")[0].removeAttribute("onclick2");
		}
	}

	request.open("POST", "dorecalmp.php", true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send("");
}
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Recalculate Scores</h1>
			<br>
			<div id="output" class="well hidden" style="max-height: 600px; overflow-y: scroll;">
			</div>
			<br>
			<div class="row-fluid">
				<div class="span6 well">
					<h3 class="text-center">Recalculate a Player's Scores</h3>
					<div class="alert alert-info">
						Recalculating a player's scores will not remove any incorrectly gained achievements.
					</div>
					<div style="max-height: 600px; overflow-y: scroll;">
						<table class="table table-striped">
				<?php

list($username) = getPostValues("username");
$query = pdo_prepare("SELECT `username` FROM `users` ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	while (($row = $result->fetchIdx())) {
		echo("<tr><td>{$row[0]}</td><td style=\"text-align: right;\">");
		echo("<a href=\"#\" id=\"recal-{$row[0]}\" onclick=\"recal('{$row[0]}');\" class=\"btn btn-info text-right recalBtn\">Recalculate</a>");
		echo("</td></tr>");
	}
}
				?>
						</table>
					</div>
				</div>
				<div class="span6">
					<div class="well">
						<h3 class="text-center">Recalculate all Scores</h3>
						<div class="alert alert-danger">
							Recalculating all scores can be very taxing on the server, and is not recommended when there are players online.
						</div>
						<br>
						<br>
						<div style="text-align: center;">
							<button id="recalAll" class="btn btn-warning" onclick="recalall();">Recalculate All</button>
						</div>
						<div class="progress progress-striped active">
							<div class="bar" style="width:0%;" id="recalallprogress"></div>
						</div>
					</div>
					<div class="well">
						<h3 class="text-center">Recalculate Multiplayer Games</h3>
						<div class="alert alert-warning">
							Recalculating Multiplayer games will take some time to finish, and any games played during this time may cause issues in the results.
						</div>
						<br>
						<br>
						<div style="text-align: center;">
							<button id="recalMp" class="btn btn-warning" onclick="recalmp();">Recalculate Multiplayer Games</button>
						</div>
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
