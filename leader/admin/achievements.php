<?php
$allow_nonwebchat = true;
$admin_page = true; // When this is set to true, the server will reject any non-admin/moderator users

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > (MINIMUM_ACCESS - 1) && array_key_exists("sub", $_POST)) {
	$user = getPostValue("user");
	print("Singleplayer achievements for $user:<br><br>");
?>
<table class="table table-striped table-bordered table-rounded">
	<tbody>
		<tr>
			<th>
				Name
			</th>
			<th>
				Progress
			</th>
		</tr>
<?php
$achiev = array(array("Title" => "Egg Seeker", "Progress" => 0),
      			 array("Title" => "Easter Bunny", "Progress" => 0),
      			 array("Title" => "Timely Marble", "Progress" => 0),
      			 array("Title" => "Stopped Time", "Progress" => 0),
      			 array("Title" => "The Citadel", "Progress" => 0),
      			 array("Title" => "King of the Speed", "Progress" => 0),
      			 array("Title" => "Pinball Maniac", "Progress" => 0),
      			 array("Title" => "Beating the Impossible", "Progress" => 0),
      			 array("Title" => "Diving to Eternity", "Progress" => 0),
      			 array("Title" => "Acrobat", "Progress" => 0),
      			 array("Title" => "Rebounded Headache", "Progress" => 0),
      			 array("Title" => "Gravity defyer", "Progress" => 0),
      			 array("Title" => "All Your Pathway are Belong to Us", "Progress" => 0),
      			 array("Title" => "The Looter", "Progress" => 0),
      			 array("Title" => "No looking down", "Progress" => 0),
      			 array("Title" => "Power of the Powerups", "Progress" => 0),
      			 array("Title" => "The Ultimate Heist", "Progress" => 0),
      			 array("Title" => "Winner of the Space Race", "Progress" => 0),
      			 array("Title" => "Endurance Battle", "Progress" => 0),
      			 array("Title" => "Schadenfreude", "Progress" => 0),
      			 array("Title" => "Carelessness", "Progress" => 0),
      			 array("Title" => "Sign Hit", "Progress" => 0),
      			 array("Title" => "Highway to Hell", "Progress" => 0),
      			 array("Title" => "Macarena", "Progress" => 0),
      			 array("Title" => "Glitch Abuser", "Progress" => 0),
      			 array("Title" => "The Dragon Awakes", "Progress" => 0),
      			 array("Title" => "7 Million", "Progress" => 0),
      			 array("Title" => "12 Million", "Progress" => 0),
      			 array("Title" => "30 Million", "Progress" => 0),
      			 array("Title" => "The Dragon\'s Destiny", "Progress" => 0));

require("../achievementcheck.php");
$achievements = achievementProgress($user);

for ($i = 0; $i < count($achievements); $i ++) {
	$achiev[$i]["Progress"] = $achievements[$i];
}

for ($i = 0; $i < count($achiev); $i ++) {
	print("<tr" . ($achiev[$i]["Progress"] == "100%" ? " class=\"completed\"" : "") . "><td>{$achiev[$i]["Title"]}</td><td>{$achiev[$i]["Progress"]}</td></tr>");
}
?>
	</tbody>
</table>
<?php
	print("Ultra achievements for $user:<br><br>");
?>
<table class="table table-striped table-bordered table-rounded">
	<tbody>
		<tr>
			<th>
				Name
			</th>
			<th>
				Progress
			</th>
		</tr>

<?php

$achiev = array(array("Title" => "The Only Easy Achievement", "Progress" => "0%"),
      			 array("Title" => "Egg Hunter", "Progress" => "0%"),
      			 array("Title" => "Golden Finale", "Progress" => "0%"),
      			 array("Title" => "Deja", "Progress" => "0%"),
      			 array("Title" => "Vu", "Progress" => "0%"),
      			 array("Title" => "On Par", "Progress" => "0%"),
      			 array("Title" => "Ultra Ultimate", "Progress" => "0%"),
      			 array("Title" => "Double Diamond", "Progress" => "0%"),
      			 array("Title" => "Scrambled Eggs", "Progress" => "0%"),
      			 array("Title" => "Ratings Monster", "Progress" => "0%"),
      			 array("Title" => "Speediest Marble on the Block!", "Progress" => "0%"),
      			 array("Title" => "It's a Jungle Out There", "Progress" => "0%"),
      			 array("Title" => "Trapped", "Progress" => "0%"),
      			 array("Title" => "Bumped", "Progress" => "0%"),
      			 array("Title" => "Pipe Mastery", "Progress" => "0%"));
require("../ultraachievementcheck.php");
$achievements = ultraAchievementProgress($user);

for ($i = 0; $i < count($achievements); $i ++) {
	if ($achievements[$i])
		$achiev[$i]["Progress"] = $achievements[$i];
}

for ($i = 0; $i < count($achiev); $i ++) {
	print("<tr" . ($achiev[$i]["Progress"] == "100%" ? " class=\"completed\"" : "") . "><td>{$achiev[$i]["Title"]}</td><td>{$achiev[$i]["Progress"]}</td></tr>");
}

?>
	</tbody>
</table>
<?php
	print("Challenge achievements for $user:<br><br>");
?>
<table class="table table-striped table-bordered table-rounded">
	<tbody>
		<tr>
			<th>
				Name
			</th>
			<th>
				Progress
			</th>
		</tr>

<?php

$achiev = array(array("Title" => "First Try", "Progress" => "0%"),
      			 array("Title" => "Naughty", "Progress" => "0%"),
      			 array("Title" => "Sweet Sixteen", "Progress" => "0%"),
      			 array("Title" => "Bragging Rights", "Progress" => "0%"),
      			 array("Title" => "Marblaxia\'s Challenge", "Progress" => "0%"),
      			 array("Title" => "Here for the Scenery", "Progress" => "0%"),
      			 array("Title" => "Archaic", "Progress" => "0%"),
      			 array("Title" => "Speedster", "Progress" => "0%"),
      			 array("Title" => "Formula Blast", "Progress" => "0%"),
      			 array("Title" => "Dust in the Wind", "Progress" => "0%"),
      			 array("Title" => "Just Barely", "Progress" => "0%"),
      			 array("Title" => "Leaving Someone High and Dry", "Progress" => "0%"),
      			 array("Title" => "Horrible Luck", "Progress" => "0%"),
      			 array("Title" => "Golden Marble", "Progress" => "0%"),
      			 array("Title" => "Shortest Victory", "Progress" => "0%"),
      			 array("Title" => "Racing Star", "Progress" => "0%"),
      			 array("Title" => "Racing Champion", "Progress" => "0%"),
      			 array("Title" => "The Taste of Victory", "Progress" => "0%"),
      			 array("Title" => "The Taste of Defeat", "Progress" => "0%"),
      			 array("Title" => "Master of All Challenges", "Progress" => "0%"));
if (0){
	require("../cachievementscheck.php");
	$achievements = cachievementProgress($user);

	for ($i = 0; $i < count($achievements); $i ++) {
		if ($achievements[$i + 1])
			$achiev[$i]["Progress"] = $achievements[$i + 1];
	}

	for ($i = 0; $i < count($achiev); $i ++) {
		print("<tr" . ($achiev[$i]["Progress"] == "100%" ? " class=\"completed\"" : "") . "><td>{$achiev[$i]["Title"]}</td><td>{$achiev[$i]["Progress"]}</td></tr>");
	}
}

?>
	</tbody>
</table>
<?php
	print("Multiplayer achievements for $user:<br><br>");
?>
<table class="table table-striped table-bordered table-rounded">
	<tbody>
		<tr>
			<th>
				Name
			</th>
			<th>
				Progress
			</th>
		</tr>

<?php

$achiev = array(array("Title" => "The First of Many", "Progress" => "0%"),
      			 array("Title" => "People's Power!", "Progress" => "0%"),
      			 array("Title" => "Experience", "Progress" => "0%"),
      			 array("Title" => "Close Call", "Progress" => "0%"),
      			 array("Title" => "Too Easy!", "Progress" => "0%"),
      			 array("Title" => "Well Rounded", "Progress" => "0%"),
      			 array("Title" => "I'm the Real Matan", "Progress" => "0%"),
      			 array("Title" => "It's a Party!", "Progress" => "0%"),
      			 array("Title" => "Advanced Player", "Progress" => "0%"),
      			 array("Title" => "Expert Player", "Progress" => "0%"),
      			 array("Title" => "Team Player", "Progress" => "0%"),
      			 array("Title" => "Multiplayer's Best", "Progress" => "0%"),
      			 array("Title" => "Handicap Master", "Progress" => "0%"),
      			 array("Title" => "Toppling the Best", "Progress" => "0%"),
      			 array("Title" => "Gloating Rights", "Progress" => "0%"),
      			 array("Title" => "Red is the Best", "Progress" => "0%"),
      			 array("Title" => "Yellow Mellow", "Progress" => "0%"),
      			 array("Title" => "Seeing Blue", "Progress" => "0%"),
      			 array("Title" => "All Your Gems are Belong to Me!", "Progress" => "0%"),
      			 array("Title" => "Points! I Need More Points!", "Progress" => "0%"),
      			 array("Title" => "Should have Put a Flag", "Progress" => "0%"),
      			 array("Title" => "Scrum at the Spires", "Progress" => "0%"),
      			 array("Title" => "Most Hated", "Progress" => "0%"),
      			 array("Title" => "Golden Days", "Progress" => "0%"),
      			 array("Title" => "Ultimate", "Progress" => "0%"),
      			 array("Title" => "Confusion", "Progress" => "0%"),
      			 array("Title" => "All Together", "Progress" => "0%"),
      			 array("Title" => "Sprawling on the Horizon", "Progress" => "0%"),
      			 array("Title" => "Bad Contribution", "Progress" => "0%"),
      			 array("Title" => "Running the Show", "Progress" => "0%"),
      			 array("Title" => "More Players than Gems!", "Progress" => "0%"),
      			 array("Title" => "Nobody Saw That!", "Progress" => "0%"),
      			 array("Title" => "Are you Matan?", "Progress" => "0%"),
      			 array("Title" => "Go Outside!", "Progress" => "0%"),
      			 array("Title" => "Tournament Winner", "Progress" => "0%"),
      			 array("Title" => "Dragon Down", "Progress" => "0%"),
      			 array("Title" => "Don't Look Directly at the Bugs!", "Progress" => "0%"));

require("../mpachievementcheck.php");
$achievements = mpachievementProgress($user);

for ($i = 0; $i < count($achievements); $i ++) {
	if ($achievements[$i])
		$achiev[$i]["Progress"] = $achievements[$i];
}

for ($i = 0; $i < count($achiev); $i ++) {
	print("<tr" . ($achiev[$i]["Progress"] == "100%" ? " class=\"completed\"" : "") . "><td>{$achiev[$i]["Title"]}</td><td>{$achiev[$i]["Progress"]}</td></tr>");
}

?>
	</tbody>
</table>

<?php
	die();
}

//----------------------------------------------------------------------
// Document start
documentHeader("Achievement Viewer");

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

<style type="text/css">
.table-striped tbody tr.highlight td {
	background-color: #90EDF5;
}
.table-striped tbody tr.completed td {
	background-color: #90F590;
}
.scrolling, #achievements {
	max-height: 600px;
	overflow-y: scroll;
}
</style>

<div class="container-fluid">
	<div class="row-fluid">
	<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Achievement Viewer</h1>
			<div class="row-fluid">
				<div class="span6 well">
					<h2 class="text-center">Player List</h2>
					<div class="scrolling">
						<table id="playerTable" class="table table-rounded table-bordered table-striped">
							<tbody>
								<tr nosel>
									<th>Username</th>
								</tr>
				<?php
$query = pdo_prepare("SELECT `username` FROM `users` WHERE `banned` = 0");
$result = $query->execute();
while (($row = $result->fetchIdx()) !== false) {
	echo("<tr><td>{$row[0]}</td></tr>");
}

				?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="span6 well">
					<h2 class="text-center">Achievements</h2>
					<div id="achievements">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$('#playerTable').on('click', 'tbody tr', function(event) {
	if ($(this).attr("nosel") != undefined)
		return;
   $(this).addClass('highlight').siblings().removeClass('highlight');
   $("#achievements").html("<h3 class=\"text-center\">Loading...</h3>");
   $.post("achievements.php", "sub=1&user=" + $(this).text(), function (data) {
   	$("#achievements").html(data);
   });
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
