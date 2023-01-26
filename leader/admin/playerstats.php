<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Player Stats");

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
				<h1 class="text-center">Player Stats</h1>
				<br>
				<br>
				<table class="table table-bordered table-rounded table-striped">
<?php

$player = $_GET["player"];
$query = pdo_prepare("SELECT `level`, `players`, `place`, COUNT(*) FROM `serverscores` WHERE `username` = :player AND `players` > 1 AND `custom` = 0 GROUP BY `level`, `players`, `place`");
$query->bind(":player", $player);
$result = $query->execute();

$results = [];

while (($row = $result->fetch()) !== false) {
	$results[$row["level"]][$row["players"]][$row["place"]] = $row["COUNT(*)"];
}

foreach ($results as $level => $playerslist) {
	echo("<tr><th colspan='3'>{$level}</th></tr>");

	foreach ($playerslist as $players => $placelist) {
		echo("<tr><td>{$players} Players</td>");

		$winTotal = 0;
		foreach ($placelist as $place => $wins) {
			$winTotal += $wins;
		}

		echo("<td>{$winTotal} games</td>");

		$avg = 0;
		echo("<td>");
		foreach ($placelist as $place => $wins) {
			$avg += $place * ($wins / $winTotal);
			echo("[$place: $wins]");
		}
		echo("</td>");

		echo("<td>");
		foreach (range($players, 1, -1) as $place) {
			$hue = 120 - (120 * (($place - 1) / ($players - 1)));
			$width = 100 / $players;
			echo("<span style='background-color: hsl({$hue}, 100%, 50%); width: {$width}%; display:inline-block;'>&nbsp;</span>");
		}

		// 2   , 2 -> 0
		// 1.5 , 2 -> 50
		// 1   , 2 -> 100

		// 4, 4 => 100
		// 3, 4 -> 66.6
		//2.5, 4 -> 50
		// 2, 4 -> 33.3
		// 1, 4 -> 0

		$off = 100 - (100 * ($avg - 1) / ($players - 1));
		$avgHue = 120 - (120.0 * (($avg - 1.0) / ($players - 1.0)));
		echo("<span style='width: 100%; background-color: hsl({$avgHue}, 100%, 50%); display:inline-block;'><span style='margin-left: {$off}%;'>|</span></span>");
		echo("</td>");

		echo("</tr>");
	}
}

?>
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
