<?php
define("MBGLEVELCOUNT",    10);
define("MBPLEVELCOUNT",    10);
define("MBULEVELCOUNT",    10);
define("CUSTOMLEVELCOUNT", 30);
define("ALLLEVELCOUNT",    50);
define("CHALLENGECOUNT",   20);
define("SCHALLENGECOUNT",  20);

define("SHOWOTHERS", $_GET["others"] == "on");

$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Tracking Data");

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

<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawCharts);

// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
function drawCharts() {
	drawLevelPractices();
	drawLevelPlays();
	drawMBGPlays();
	drawMBPPlays();
	drawMBUPlays();
	drawCustomPlays();
	// drawAllPlays();
	drawScreenResolutions();
	drawWindowResolutions();
	drawMarblesSP();
	drawMarblesMP();
	//drawChallenges();
	drawSuperChallenges();
	drawAchievements();
	//drawCAchievements();
	drawMPAchievements();
	drawSuperChallengePlayers();
	drawRatings();
}
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Tracking Data</h1>
			<br>
			<br>
<table class="table-bordered table-rounded" style="width: 100%"><tr><td style="min-width: 30; padding: 0px;">
<style type="text/css">
.table-striped tbody tr.highlight td {
	background-color: #90EDF5;
	border-collapse: collapse;
}
.table-striped tbody tr td {
	border-left: none;
	border-collapse: collapse;
}
.nologin {
	color: #f00;
}
</style>
<table style="border: none; padding: 0px; margin: 0px; border-collapse: collapse;">
<tr><td><input type="search" style="border-radius: 20px" id="searchbar" onkeyup="updateSearch();"></td></tr>
<tr><td>
<div style="max-height: 600px; min-height: 600px; height: 600px; overflow-y: scroll; overflow-x: auto;">
<table id="playerTable" class="table table-striped">
<tr all="all" <?php if (!array_key_exists("user", $_GET)) echo("class=\"highlight\""); ?>><td>[All Players]</td></tr>
</table>
</div>
</td></tr>
</table>
<script type="text/javascript">
var names = [<?php

$username = getPostValue("username");
$query = pdo_prepare("SELECT `username`, `access`, `lastaction` FROM `users` WHERE `banned` = 0 ORDER BY `username` ASC");
$result = $query->execute();
if ($result) {
	while (($row = $result->fetchIdx()) !== false) {
		echo("{" . ($_GET["user"] == $row[0] ? "\"selected\": true," :"") . "\"name\": \"{$row[0]}\", \"loggedin\": " . ($row[2] != 0 ? 1 : 0) . "},");
	}
}
?>];

function updateSearch() {
	$("tr#player").each(function() {
		$(this).remove();
	});
	var search = $("#searchbar").val();
	for (var i = 0; i < names.length; i++) {
		var player = names[i].name;

		if (player == "")
			continue;

		if (player.search(search) != -1) {
			$("<tr id=\"player\"" + (names[i].loggedin == 1 ? "" : " class=\"nologin\"") + "><td>" + player + "</td></tr>").appendTo($('#playerTable'));
		}
	};
}

setTimeout(updateSearch, 100);

$('#playerTable').on('click', 'tbody tr', function(event) {
	if ($(this).attr("nosel") != undefined)
		return;
   $(this).addClass('highlight').siblings().removeClass('highlight');
   if ($(this).attr("all") != undefined)
   	window.location.search = "";
   else
	   window.location.search = "?user=" + $(this).text();
});
</script>
</td><td style="padding-left:20px">
<div style="max-height: 600px; overflow-y: scroll; overflow-x: auto;">
<?php
$hasuser = array_key_exists("user", $_GET);

$query = pdo_prepare("SELECT COUNT(*) FROM `users`");
$result = $query->execute();
$totalusers = $result->fetchIdx(0);
$query = pdo_prepare("SELECT COUNT(*) FROM `users` WHERE `lastaction` != 0");
$result = $query->execute();
$visitedusers = $result->fetchIdx(0);

echo("Some random stats:<br>");
if (!$hasuser) {
	echo("Total Users: $totalusers<br>");
	echo("Users who have actually opened the damn site: $visitedusers<br>");
	echo("That's a whopping " . round(($visitedusers / $totalusers) * 100) . "%!<br>");
}
echo("Longest login: ");
$query = pdo_prepare("SELECT * FROM `tracking` WHERE `type` = 'logintime' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " ORDER BY `data`+0 DESC LIMIT 1");
$result = $query->execute();
if ($result->rowCount()) {
	$row = $result->fetch();
	$sec = $row['data'] % 60;
	$min = ($row['data'] - ($row['data'] % 60)) / 60 % 60;
	$hour = ($row['data'] - ($row['data'] % 3600)) / 3600 % 24;
	$day = ($row['data'] - ($row['data'] % 86400)) / 86400 % 24;
	if ($hasuser)
		echo($day . " day(s), " . $hour . " hour(s), " . $min . " minute(s), " . $sec . " second(s).");
	else
		echo("{$row['username']} with a login time of " . $day . " day(s), " . $hour . " hour(s), " . $min . " minute(s), " . $sec . " second(s).");
} else {
	echo("None");
}
echo("<br>");
echo("Average login time: ");
$query = pdo_prepare("SELECT AVG(`data`) FROM `tracking` WHERE `type` = 'logintime' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " ORDER BY `data`+0 DESC LIMIT 1");
$result = $query->execute();
if ($result->rowCount()) {
	$row = $result->fetchIdx();
	$sec = $row[0] % 60;
	$min = ($row[0] - ($row[0] % 60)) / 60 % 60;
	$hour = ($row[0] - ($row[0] % 3600)) / 3600 % 24;
	$day = ($row[0] - ($row[0] % 86400)) / 86400 % 24;
	echo($day . " day(s), " . $hour . " hour(s), " . $min . " minute(s), " . $sec . " second(s).");
} else {
	echo("None");
}
echo("<br>");
if ($hasuser)
	echo("Total Time Online: ");
else
	echo("Longest Time Online: ");
$query = pdo_prepare("SELECT `username`, SUM(`data`) FROM `tracking` WHERE `type` = 'logintime' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `username` ORDER BY SUM(`data`)+0 DESC LIMIT 1");
$result = $query->execute();
if ($result->rowCount()) {
	$row = $result->fetchIdx();
	$sec = $row[1] % 60;
	$min = ($row[1] - ($row[1] % 60)) / 60 % 60;
	$hour = ($row[1] - ($row[1] % 3600)) / 3600 % 24;
	$day = ($row[1] - ($row[1] % 86400)) / 86400 % 24;
	if ($hasuser)
		echo($day . " day(s), " . $hour . " hour(s), " . $min . " minute(s), " . $sec . " second(s).");
	else
		echo("{$row[0]} with a total login time of " . $day . " day(s), " . $hour . " hour(s), " . $min . " minute(s), " . $sec . " second(s).");
} else {
	echo("None");
}
echo("<br>");
echo("[SP] Level most played: ");
$query = pdo_prepare("SELECT `data`, SUM(`count`) FROM `tracking` WHERE `type` = 'levelplay' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `data` ORDER BY SUM(`count`)+0 DESC LIMIT 1");
$result = $query->execute();
if ($result->rowCount()) {
	$row = $result->fetchIdx();
	echo("{$row[0]} with a total play count of {$row[1]} play(s).");
} else {
	echo("None");
}
echo("<br>");
echo("[MP] Level most played: ");
$query = pdo_prepare("SELECT `level`, COUNT(*) FROM `serverscores` WHERE `players` > 1 " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT 1");
$result = $query->execute();
if ($result->rowCount()) {
	$row = $result->fetchIdx();
	echo("{$row[0]} with a total play count of {$row[1]} play(s).");
} else {
	echo("None");
}
echo("<br>");
echo("[MP] Level most practiced: ");
$query = pdo_prepare("SELECT `level`, COUNT(*) FROM `serverscores` WHERE `players` = 1 " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT 1");
$result = $query->execute();
if ($result->rowCount()) {
	$row = $result->fetchIdx();
	echo("{$row[0]} with a total play count of {$row[1]} play(s).");
} else {
	echo("None");
}
?>
<br>
<div class="text-center">
<h3>MBG Levels</h3>
<div id="mbgplays"></div>
<script type="text/javascript">
function drawMBGPlays() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT l.`display`, COUNT(*) FROM `scores` s JOIN `officiallevels` l ON s.`level` = l.`file` WHERE `gametype` = 'Gold' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . MBGLEVELCOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `scores` WHERE `gametype` = 'Gold' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . MBGLEVELCOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'<?php echo(MBGLEVELCOUNT); ?> Most Played MBG Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('mbgplays'));
	chart.draw(data, options);
}
</script>
<h3>MBP Levels</h3>
<div id="mbpplays"></div>
<script type="text/javascript">
function drawMBPPlays() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT l.`display`, COUNT(*) FROM `scores` s JOIN `officiallevels` l ON s.`level` = l.`file` WHERE `gametype` = 'Platinum' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . MBPLEVELCOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `scores` WHERE `gametype` = 'Platinum' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . MBPLEVELCOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'<?php echo(MBPLEVELCOUNT); ?> Most Played MBP Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('mbpplays'));
	chart.draw(data, options);
}
</script>

<h3>MBU Levels</h3>
<div id="mbuplays"></div>
<script type="text/javascript">
function drawMBUPlays() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT l.`display`, COUNT(*) FROM `scores` s JOIN `officiallevels` l ON s.`level` = l.`file` WHERE `gametype` = 'Ultra' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . MBULEVELCOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `scores` WHERE `gametype` = 'Ultra' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . MBULEVELCOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'<?php echo(MBULEVELCOUNT); ?> Most Played MBU Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('mbuplays'));
	chart.draw(data, options);
}
</script>
<h3>Custom Levels</h3>
<div id="customplays"></div>
<script type="text/javascript">
function drawCustomPlays() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT l.`display`, COUNT(*) FROM `scores` s JOIN `levels` l ON s.`level` = l.`file` WHERE `gametype` = 'LBCustom' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . CUSTOMLEVELCOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `scores` WHERE `gametype` = 'LBCustom' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . CUSTOMLEVELCOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'<?php echo(CUSTOMLEVELCOUNT); ?> Most Played Custom Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('customplays'));
	chart.draw(data, options);
}
</script>
<?php /*
<h3>All Levels</h3>
<div id="allplays"></div>
<script type="text/javascript">
function drawAllPlays() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT g.`display`, COUNT(*) FROM `scores` s JOIN ((SELECT `display`,`file` FROM `officiallevels`) UNION (SELECT `display`,`file` FROM `levels`)) g ON s.`level` = g.`file` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . ALLLEVELCOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `scores` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . ALLLEVELCOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'<?php echo(ALLLEVELCOUNT); ?> Most Played Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('allplays'));
	chart.draw(data, options);
}
</script>
*/ ?>
<h3>Level Practices</h3>
<div id="levelpractices"></div>
<script type="text/javascript">
function drawLevelPractices() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT l.`display`, COUNT(*) FROM `serverscores` s JOIN `mplevels` l ON s.`level` = l.`file` WHERE `players` = 1 " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Most Practiced Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('levelpractices'));
	chart.draw(data, options);
}
</script>
<h3>Level Plays</h3>
<div id="levelplays"></div>
<script type="text/javascript">
function drawLevelPlays() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Level Name');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT l.`display`, COUNT(*) FROM `serverscores` s JOIN `mplevels` l ON s.`level` = l.`file` WHERE `players` > 1 " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Most Played Levels',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('levelplays'));
	chart.draw(data, options);
}
</script>
<h3>Desktop Resolutions</h3>
<div id="screenresolutions"></div>
<script type="text/javascript">
function drawScreenResolutions() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Screen Resolution');
	data.addColumn('number', 'Use Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT `data`, COUNT(*) FROM `tracking` WHERE `type` = 'screenres' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `data` ORDER BY COUNT(*) DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Top 10 Desktop Resolutions',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('screenresolutions'));
	chart.draw(data, options);
}
</script>
<h3>Window Resolutions</h3>
<div id="windowresolutions"></div>
<script type="text/javascript">
function drawWindowResolutions() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Window Resolution');
	data.addColumn('number', 'Use Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT `data`, COUNT(*) FROM `tracking` WHERE `type` = 'windowres' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `data` ORDER BY COUNT(*) DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Top 10 Window Resolutions',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('windowresolutions'));
	chart.draw(data, options);
}
</script>
<h3>Marble Choice (SP)</h3>
<div id="marblessp"></div>
<script type="text/javascript">
function drawMarblesSP() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Marble Choice');
	data.addColumn('number', 'Use Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT `data`, `count` FROM `tracking` WHERE `type` = 'marbleNum' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `data` ORDER BY `count` DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Top 10 Marble Choices (SP)',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('marblessp'));
	chart.draw(data, options);
}
</script>
<h3>Marble Choice (MP)</h3>
<div id="marblesmp"></div>
<script type="text/javascript">
function drawMarblesMP() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Marble Choice');
	data.addColumn('number', 'Use Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT `marble`, COUNT(*) FROM `serverscores` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `marble` ORDER BY COUNT(*) DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Top 10 Marble Choices (MP)',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('marblesmp'));
	chart.draw(data, options);
}
</script>
<?php if (0) { ?>
<h3>Challenges</h3>
<div id="challenges"></div>
<script type="text/javascript">
function drawChallenges() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Challenge');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT g.`display`, COUNT(*) FROM `challengedata` s JOIN ((SELECT `display`,`file` FROM `officiallevels`) UNION (SELECT `display`,`file` FROM `levels`)) g ON s.`level` = g.`file` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . CHALLENGECOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `challengedata` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `level` ORDER BY COUNT(*) DESC LIMIT " . CHALLENGECOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'Top <?php echo(CHALLENGECOUNT); ?> Challenges',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('challenges'));
	chart.draw(data, options);
}
</script>
<?php } ?>
<h3>Super Challenges</h3>
<div id="superchallenges"></div>
<script type="text/javascript">
function drawSuperChallenges() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Super Challenge');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT d.`display`, COUNT(*) FROM `scscores` s JOIN `scdata` d ON s.`challenge` = d.`name` WHERE `challenge` != '' AND `challenge` != '0' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `challenge` ORDER BY COUNT(*) DESC LIMIT " . SCHALLENGECOUNT);
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
if (SHOWOTHERS) {
	$query = pdo_prepare("SELECT SUM(`counts`) FROM (SELECT COUNT(*) AS `counts` FROM `scscores` WHERE `challenge` != '' AND `challenge` != '0' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `challenge` ORDER BY COUNT(*) DESC LIMIT " . SCHALLENGECOUNT . ",1000) AS `bananas`");
	$result = $query->execute();
	if ($result->rowCount()) {
		$row = $result->fetchIdx();
		if ($row[0] == "") $row[0] = 0;
		echo("['Others', {$row[0]}]");
	}
}
?>
	]);
	var options = {'title':'Top <?php echo(CHALLENGECOUNT); ?> Super Challenges',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('superchallenges'));
	chart.draw(data, options);
}
</script>
<div id="superchallenges2"></div>
<script type="text/javascript">
function drawSuperChallengePlayers() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Opponent Count');
	data.addColumn('number', 'Play Count');
	data.addRows([
<?php
$query = pdo_prepare("SELECT `players`, COUNT(*) FROM `scscores` WHERE `challenge` != '' AND `challenge` != '0' " . ($hasuser ? "AND `username` = '{$_GET['user']}' " : "") . " GROUP BY `players` ORDER BY COUNT(*) DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . addSlashes($row[0]) . "', {$row[1]}],");
	}
}
?>
	]);
	var options = {'title':'Super Challenge Player Counts',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
	};
	var chart = new google.visualization.PieChart(document.getElementById('superchallenges2'));
	chart.draw(data, options);
}
</script>
<h3>Achievements</h3>
<div id="achievements"></div>
<script type="text/javascript">
function drawAchievements() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Achievement');
	data.addColumn('number', '% Players Earned');
	data.addRows([
<?php
$achiev = array("Egg Seeker", "Easter Bunny", "Timely Marble", "Stopped Time", "The Citadel", "King of the Speed", "Pinball Maniac", "Beating the Impossible", "Diving to Eternity", "Acrobat", "Rebounded Headache", "Gravity defyer", "All Your Pathway are Belong to Us", "The Looter", "No looking down", "Power of the Powerups", "The Ultimate Heist", "Winner of the Space Race", "Endurance Battle", "Schadenfreude", "Carelessness", "Sign Hit", "Highway to Hell", "Macarena", "Glitch Abuser", "The Dragon Awakes", "7 Million", "12 Million", "30 Million", "The Dragon\'s Destiny");
$query = pdo_prepare("SELECT `achievement`, COUNT(*) FROM `achievements` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `achievement` ORDER BY `achievement` ASC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		$percent = $row[1] / $visitedusers;
		echo("['{$achiev[$row[0]]}', {$percent}],");
	}
}
?>
	]);
	var options = {'title':'Achievements',
		'width':700,
		'height':600,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
		'orientation':'vertical',
		'hAxis':{title:"Times Earned", format: "###%", gridlines: {count: -1}},
		'vAxis':{title:"Achievement Name", slantedTextAngle: 90},
	};
	var chart = new google.visualization.ColumnChart(document.getElementById('achievements'));
	chart.draw(data, options);
}
</script>
<br>
<?php if (0) { ?>
<div id="cachievements"></div>
<script type="text/javascript">
function drawCAchievements() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Achievement');
	data.addColumn('number', '% Players Earned');
	data.addRows([
<?php

$achiev = array("First Try", "Naughty", "Sweet Sixteen", "Bragging Rights", "Marblaxia\'s Challenge", "Here for the Scenery", "Archaic", "Speedster", "Formula Blast", "Dust in the Wind", "Just Barely", "Leaving Someone High and Dry", "Horrible Luck", "Golden Marble", "Shortest Victory", "Racing Star", "Racing Champion", "The Taste of Victory", "The Taste of Defeat", "Master of All Challenges");

$query = pdo_prepare("SELECT `achievement`, COUNT(*) FROM `cachievements` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `achievement` ORDER BY `achievement` ASC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		$percent = $row[1] / $visitedusers;
		echo("['{$achiev[$row[0]]}', {$percent}],");
	}
}
?>
	]);
	var options = {'title':'Challenge Achievements',
		'width':700,
		'height':600,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
		'orientation':'vertical',
		'hAxis':{title:"Times Earned", format: "###%", gridlines: {count: -1}},
		'vAxis':{title:"Achievement Name", slantedTextAngle: 90},
	};
	var chart = new google.visualization.ColumnChart(document.getElementById('cachievements'));
	chart.draw(data, options);
}
</script>
<br>
<?php } ?>
<div id="mpachievements"></div>
<script type="text/javascript">
function drawMPAchievements() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Achievement');
	data.addColumn('number', '% Players Earned');
	data.addRows([
<?php

$achiev = array("The First of Many", "People's Power!", "Experience", "Close Call", "Too Easy!", "Well Rounded", "I'm the Real Matan", "It's a Party!", "Advanced Player", "Expert Player", "Team Player", "Multiplayer's Best", "Handicap Master", "Toppling the Best", "Gloating Rights", "Red is the Best", "Yellow Mellow", "Seeing Blue", "All Your Gems are Belong to Me!", "Points! I Need More Points!", "Should have Put a Flag", "Scrum at the Spires", "Most Hated", "Golden Days", "Ultimate", "Confusion", "All Together", "Sprawling on the Horizon", "Bad Contribution", "Running the Show", "More Players than Gems!", "Nobody Saw That!", "Are you Matan?", "Go Outside!", "Tournament Winner", "Dragon Down", "Don't Look Directly at the Bugs!");
$query = pdo_prepare("SELECT `achievement`, COUNT(*) FROM `mpachievements` " . ($hasuser ? "WHERE `username` = '{$_GET['user']}' " : "") . " GROUP BY `achievement` ORDER BY `achievement` ASC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		$percent = $row[1] / $visitedusers;
		echo("[\"{$achiev[$row[0]]}\", {$percent}],");
	}
}
?>
	]);
	var options = {'title':'Multiplayer Achievements',
		'width':700,
		'height':600,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
		'orientation':'vertical',
		'hAxis':{title:"Times Earned", format: "###%", gridlines: {count: -1}},
		'vAxis':{title:"Achievement Name",  slantedTextAngle: 90},
	};
	var chart = new google.visualization.ColumnChart(document.getElementById('mpachievements'));
	chart.draw(data, options);
}
</script>
</div>
<?php
if (!$hasuser) {
?>
<br>
<div id="ratings"></div>
<script type="text/javascript">
function drawRatings() {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Username');
	data.addColumn('number', 'Rating Points');
	data.addRows([
<?php

$query = pdo_prepare("SELECT `username`, `rating_mp` FROM `users` WHERE `rating_mpgames` >= 20 GROUP BY `rating_mp` ORDER BY `rating_mp` DESC");
$result = $query->execute();
if ($result->rowCount()) {
	while (($row = $result->fetchIdx()) !== FALSE) {
		echo("['" . getDisplayName($row[0]) . "', $row[1]],");
	}
}

?>
	]);
	var options = {'title':'Multiplayer Ratings',
		'width':700,
		'height':350,
		'is3D':true,
		'backgroundColor': '#f5f5f5',
		'histogram': { 'bucketSize': 100 }
	};
	var chart = new google.visualization.Histogram(document.getElementById('ratings'));
	chart.draw(data, options);
}
</script>
</div>
<?php
}
?>
			<form action="tracking.php" method="GET">
				<input type="hidden" name="user" value="<?php echo($_GET["user"]); ?>">
				<label for="others">Show "Others" Slice: <input type="checkbox" name="others" onclick="this.form.submit();" <?php if ($_GET["others"] == "on") echo("checked"); ?>></label>
			</form>
			</div>
			</td></tr></table>
			Red names have never opened the site.
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
