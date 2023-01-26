<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 1) {

$dedhost = MBDB::getDatabaseHost("dedicated");
$deduser = MBDB::getDatabaseUser("dedicated");
$dedpass = MBDB::getDatabasePass("dedicated");
$deddata = MBDB::getDatabaseName("dedicated");

$dsn = "mysql:dbname=" . $deddata . ";host=" . $dedhost;
// Connect + select
try {
	global $ded_connection;
   $ded_connection = new SpDatabaseConnection($dsn, $deduser, $dedpass);
} catch (SpDatabaseLoginException $e) {
	die("Could not open database connection.");
}
if ($ded_connection == null) {
	die("Could not connect to database.");
}

function dprepare($query) {
	global $ded_connection;

	return $ded_connection->prepare($query);
}

if (array_key_exists("server", $_POST)) {
	$send = array("Error" => false);

	$server = $_POST["server"];
	$row = dprepare("SELECT * FROM `servers` WHERE `id` = :id");
	$row->bind(":id", $server);

	$result = $row->execute();
	if ($result->rowCount()) {
		//Server info time
		$array = $result->fetch();
		$send += $array;

		//Get the log
		$send["log"] = file_get_contents($array["gamelocation"] . $array["consolefile"]);

		//Check if it's running
		$pid = $array["pid"];
		if ($pid > 0) {
			$running = posix_kill($pid, 0);

			//If it's not running, tell the database
			if (!$running) {
				$query = dprepare("UPDATE `servers` SET `pid` = -1 WHERE `id` = :id");
				$query->bind(":id", $server);
				$query->execute();

				$send["pid"] = -1;
			}

			$send["running"] = $running;

			//Let's not be too dangerous
			$pid = (int)$pid;

			//Get how much cpu+mem it's using

			//This took longer to write than I'd like to admit
			$cpu = trim(exec("ps -p $pid --no-heading -o %cpu"));
			$mem = trim(exec("ps -p $pid --no-heading -o %mem"));

			$send["cpu"] = $cpu;
			$send["memory"] = $mem;
		} else {
			$send["running"] = false;

			$send["cpu"] = 0;
			$send["memory"] = 0;
		}

		$send["address"] = $_SERVER["SERVER_ADDR"];

		echo(json_encode($send));
	} else
		echo(json_encode(array("Error" => "The server does not exist!")));

	die();
} else {

	//----------------------------------------------------------------------
	// Document start
	documentHeader("Dedicated Servers");

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

?>
<style type="text/css">
.table-striped tbody tr.highlight td {
	background-color: #90EDF5;
}
.table-striped tbody tr.completed td {
	background-color: #90F590;
}
.infotable td:nth-child(1) {
	width: 150px;
	min-width: 150px;
}
.scrolling {
	min-height: 620px;
	height: 620px;
	overflow-y: scroll;
	border-collapse: collapse;
}
.nopadding {
	padding: 0px;
}
.serverinfo {
	text-align: center;
}
.console {
	min-height: 300px;
	height: 300px;
	overflow-y: scroll;
}
td.row-danger {
	background-color: #ffcccc !important;
}
</style>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Chat Servers</h1>
			<br>
			<br>
			<div class="row-fluid">
				<div class="span3 well scrolling nopadding">
					<!-- Server list -->
					<table class="table table-rounded table-bordered table-striped" id="serverTable">
						<tbody>
<?php
// Get server list

$result = dprepare("SELECT * FROM `servers`")->execute();

if ($result->rowCount()) {
	while (($row = $result->fetch()) !== false) {
		$pid = (int)$row["pid"];
		$cpu = (float)trim(exec("ps -p $pid --no-heading -o %cpu"));

		echo("<tr server-id=\"{$row['id']}\">");
		if ($cpu > 20)
			echo("<td class=\"row-danger\">{$row['name']}</td>");
		else
			echo("<td>{$row['name']}</td>");
		echo("</tr>");
	}
} else { ?>
<tr>
<td>No servers!</td>
</tr>
<?php }

?>
						</tbody>
					</table>
				</div>
				<div class="span9 well scrolling" id="serverinfo">
					<!-- Server information -->
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var gServerName = "";

$('#serverTable').on('click', 'tbody tr', function(event) {
	if ($(this).attr("nosel") != undefined)
		return;
   $(this).addClass('highlight').siblings().removeClass('highlight');
   //Do servery things here

   var id = $(this).attr("server-id");
   gServerName = $(this).text();

   updateServer(id);
});

function updateServer(id) {
   var infodiv = $("#serverinfo");
   var head = "<h2>" + gServerName + "</h2><br>";
   infodiv.html(head + "<h4>Loading...</h4>");

   $.post("servers.php", "server=" + id, function(data) {
   	//Parse data
   	var obj = JSON.parse(data);

   	if (obj.error) {
   		infodiv.html(head + "<br><h3>ERROR</h3>");
   	} else {
   		//So we have lots of information on the server

   		var button = "<a id='startstop' class='pull-right btn" + (obj.running ? " btn-danger" : " btn-success") + "' onclick='" + (obj.running ? "stop" : "start") + "Server(" + id + ");'>" + (obj.running ? "Stop" : "Start") + " Server</a>";

   		var body = "<br><table class='table table-rounded table-bordered infotable'><tbody>";
   		body += "<tr><th colspan='2'>Process Information</th></tr>"
   		body += "<tr><td>Process ID:</td><td>" + (obj.pid == -1 ? "Offline" : obj.pid) + "</td>";
   		body += "<tr><td>Process is Running:</td><td><span id='running'>" + (obj.running ? "Yes" : "No") + "</span>" + button + "</td>";
   		body += "<tr><td>CPU Usage:</td><td>" + obj.cpu + "%</td>";
   		body += "<tr><td>Memory Usage:</td><td>" + obj.memory + "%</td>";
   		body += "<tr><td>Executable:</td><td>" + obj.gamelocation + obj.exename + "</td>";
   		body += "<tr><td>Server Address:</td><td>" + obj.address + "</td>";
   		body += "</tbody></table>";
   		body += "<br><table class='table table-rounded table-bordered infotable'><tbody>";
   		body += "<tr><th colspan='2'>Server Settings</th></tr>"
<?php
$result = dprepare("SELECT * FROM `variables`")->execute();
while (($row = $result->fetch()) !== false)
	echo("body += \"<tr><td>{$row["title"]}:</td><td><input type='text' class='setvar' variable='{$row["name"]}' server-id='\" + id + \"' value='\" + obj.{$row["name"]} + \"'></td></tr>\";");
?>
   		body += "</tbody></table>";
   		body += "<br><table class='table table-rounded table-bordered infotable'><tbody>";
   		body += "<tr><th colspan='2'>Console Access</th></tr>"
   		body += "<tr><td>Console Input:</td><td><input type='text' id='entry' server-id='" + id + "'></td></tr>";
   		body += "<tr><td>Console:</td><td><div class='console'>" + obj.log.replace(/\n/g, "<br>") + "</div></td></tr>";
   		body += "</tbody></table>";

   		infodiv.html(head + body);

   		$(".console").scrollTop($(".console")[0].scrollHeight);

			$(".setvar").keyup(function (event) {
				if (event.keyCode == 13) { //Newline
					//Send the entry to the console
					var id = $(this).attr("server-id");
					var value = $(this).val();
					var variable = $(this).attr("variable");

					$.post("doserveraction.php", "server=" + id + "&action=set&variable=" + variable + "&value=" + value, function(data) {
						var obj = JSON.parse(data);

						if (obj.error) {

						} else
							updateServer(id);
					});
				}
			});

			$("#entry").keyup(function (event) {
				if (event.keyCode == 13) { //Newline
					//Send the entry to the console
					var value = $(this).val();
					var id = $(this).attr("server-id");

					$.post("doserveraction.php", "server=" + id + "&action=send&value=" + value, function(data) {
						var obj = JSON.parse(data);

						if (obj.error) {

						} else
							updateServer(id);
					});
				}
			});
   	}
   });
}

function startServer(id) {
	$("#startstop").attr("disabled", "disabled");
	$("#running").text("Starting...");

	$.post("doserveraction.php", "server=" + id + "&action=start", function(data) {
		var obj = JSON.parse(data);

		if (obj.error) {

		} else {
			$("#startstop").val("Started");
			$("#startstop").attr("disabled", "");
			$("#running").text("Refreshing...");

			updateServer(id);
		}
	});
}

function stopServer(id) {
	$("#startstop").attr("disabled", "disabled");
	$("#running").text("Stopping...");

	$.post("doserveraction.php", "server=" + id + "&action=stop", function(data) {
		var obj = JSON.parse(data);

		if (obj.error) {

		} else {
			$("#startstop").val("Started");
			$("#startstop").attr("disabled", "");
			$("#running").text("Refreshing...");

			updateServer(id);
		}
	});
}


</script>

	<?php
	} else {
		accessDenied();
	}
	documentFooter();
	// Document end
	//----------------------------------------------------------------------

}

?>
