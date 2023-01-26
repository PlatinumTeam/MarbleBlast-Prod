<?php
$allow_nonwebchat = true;
$admin_page = true;

define("EASTER_EGG",     1 << 0);
define("NO_JUMP",        1 << 1);
define("DOUBLE_DIAMOND", 1 << 2);
define("NO_TT",          1 << 3);

define("SHOW_MILLIONIFY", false);

require("../opendb.php");
require("../lbratings.php");
require("defaults.php");

// Connection to old database
$olddbhost = MBDB::getDatabaseHost("platinum_old");
$olddbuser = MBDB::getDatabaseUser("platinum_old");
$olddbpass = MBDB::getDatabasePass("platinum_old");
$olddbdata = MBDB::getDatabaseName("platinum_old");

/*
 * Super duper conversions list
 * TABLE: times -> scores
 *
 * ROWS:
 * username -> username
 * time     -> score
 * mission  -> level [stripped and lowercase]
 * rating   -> throw it out completely, recalculate
 *          -> type [interpret from the level]
 *          -> gametype [interpret as well]
 * id       -> id [just autogenerate]
 *          -> time [use now]
 *          -> timestamp [also just use now]
 */

$dsn = "mysql:dbname=" . $olddbdata . ";host=" . $olddbhost;
// Connect + select
try {
	global $oldConnection;
   $oldConnection = new SpDatabaseConnection($dsn, $olddbuser, $olddbpass);
} catch (SpDatabaseLoginException $e) {
	die("Could not open database connection.");
}
if ($oldConnection == null) {
	die("Could not connect to database.");
}

function formatTime($time) {
	if ($time == 0 || $time == 5998999)
		return "99:59.99";
	$neg = $time < 0;
	$time = abs($time);
	$ms = fmod($time, 1000);
	$time = ($time - $ms) / 1000;
	$s  = fmod($time, 60);
	$m  = ($time - $s) / 60;
	return ($neg ? "-" : "") . str_pad($m, 2, "0", STR_PAD_LEFT) . ":" . str_pad($s, 2, "0", STR_PAD_LEFT) . ":" . str_pad($ms, 3, "0", STR_PAD_LEFT);
}

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("View Scores");

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
<style>
select, input, form {
	margin-bottom: 0px !important;
}
table, tr, td {
	vertical-align: top;
}
</style>
<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">View Scores</h1>
			<br>
			<br>

			<table class="noborder">
			<tbody>
			<tr>
			<td>
			Select Level:
			<table class="table-bordered">
			<tbody>
			<tr>
			<td>Game:</td>
			<td><form action="scores.php" method="POST">
			<select name='game' onchange="this.form.submit();">
<?php
$list = array("", "Gold", "Platinum", "Ultra", "Custom");
foreach ($list as $game) {
	echo("<option value='$game'" . ($_POST["game"] == $game ? " selected" : "") . ">$game</option>");
}
?>
			</select>
			</form>
			</td>
			</tr>
<?php if (array_key_exists("game", $_POST)) { ?>
			<tr>
			<td><?php if ($_POST["game"] == "Custom") echo("Pack(s):"); else echo("Difficulty:");?></td>
			<td><form action="scores.php" method="POST">
			<input type="hidden" name="game" value="<?php echo($_POST["game"]); ?>">
			<select name='diff' onchange="this.form.submit();">
<?php
if ($_POST["game"] == "Gold") $list = array("", "Beginner", "Intermediate", "Advanced");
if ($_POST["game"] == "Platinum") $list = array("", "Beginner", "Intermediate", "Advanced", "Expert");
if ($_POST["game"] == "Ultra") $list = array("", "Beginner", "Intermediate", "Advanced");
if ($_POST["game"] == "Custom") {
	$list = array("");
	$query = pdo_prepare("SELECT `name` FROM `categories`");
	$result = $query->execute();
	while (($diff = $result->fetchIdx(0)) !== false)
		array_push($list, $diff);
}
foreach ($list as $diff) {
	echo("<option value='$diff'" . ($_POST["diff"] == $diff ? " selected" : "") . ">$diff</option>");
}
?>
			</select>
			</form>
			</td>
			</tr>
<?php if (array_key_exists("diff", $_POST)) { ?>
			<tr>
			<td>Level:</td>
			<td><form action="scores.php" method="POST">
			<input type="hidden" name="game" value="<?php echo($_POST["game"]); ?>">
			<input type="hidden" name="diff" value="<?php echo($_POST["diff"]); ?>">
			<select name='level' onchange="this.form.submit();">
			<option value=""></option>
<?php
$diff = strtolower($_POST["diff"]);

if ($_POST["game"] == "Custom") {
	$diff = str_replace(" to ", "-", $diff);
	$diff = preg_replace('/[^a-z0-9\\-]/s', '', $diff);
	$query = pdo_prepare("SELECT `file`, `display` FROM `levels` WHERE `category` = :type ORDER BY `display` ASC");
} else {
	$query = pdo_prepare("SELECT `file`, `display` FROM `officiallevels` WHERE `game` = :game AND `type` = :type ORDER BY `display` ASC");
	$query->bind(":game", $_POST["game"]);
}
$query->bind(":type", $diff);
$result = $query->execute();
while (($row = $result->fetchIdx()) !== false) {
	echo("<option value=\"" . addslashes($row[0]) . "\"" . (stripLevel($_POST["level"]) == stripLevel($row[0]) ? " selected" : "") . ">{$row[1]}</option>");
}
?>
			</select>
			</form>
			</td>
			</tr>
<?php
if (array_key_exists("level", $_POST)) {
?>
			<tr>
			<td>
			<form action="scores.php" method="POST">
			<input type="hidden" name="game" value="<?php echo($_POST["game"]); ?>">
			<input type="hidden" name="diff" value="<?php echo($_POST["diff"]); ?>">
			<input type='hidden' name="level" value="<?php echo($_POST["level"]); ?>">
			<input type="submit" value="Refresh" class="btn btn-primary">
			</form>
			</td>
			</tr>
<?php
}
?>
			</tbody>
			</table>
<?php

if (array_key_exists("level", $_POST)) {

	//Level picture
	echo("<img src=\"/leader/levelpics/" . stripslashes($_POST["level"]) . ".jpg\">");

} } } ?>
			</td>
<?php
if (array_key_exists("level", $_POST)) {
echo("<td>");
$stripped = stripLevel($_POST["level"]);
if (array_key_exists("info", $_POST) && $access > 1) {
	$qualify            = (float)$_POST["QualifyTime"];
	$gold               = (float)$_POST["GoldTime"];
	$ultimate           = (float)$_POST["UltimateTime"];
	$standardiser       = (float)$_POST["Standardiser"];
	$basescore          = (float)$_POST["BaseScore"];
	$basemultiplier     = (float)$_POST["BaseMultiplier"];
	$goldbonus          = (float)$_POST["GoldBonus"];
	$platinumbonus      = (float)$_POST["PlatinumBonus"];
	$ultimatebonus      = (float)$_POST["UltimateBonus"];
	$difficulty         = (int)$_POST["Difficulty"];
	$golddifficulty     = (int)$_POST["GoldDifficulty"];
	$ultimatedifficulty = (int)$_POST["UltimateDifficulty"];
	$query = pdo_prepare("UPDATE `levels` SET " .
								"`qualify` = :qualify," .
								"`gold` = :gold," .
								"`ultimate` = :ultimate," .
								"`standardiser` = :standardiser," .
								"`basescore` = :basescore," .
								"`basemultiplier` = :basemultiplier," .
								"`goldbonus` = :goldbonus," .
								"`platinumbonus` = :platinumbonus," .
								"`ultimatebonus` = :ultimatebonus," .
								"`difficulty` = :difficulty," .
								"`golddifficulty` = :golddifficulty," .
								"`ultimatedifficulty` = :ultimatedifficulty " .
								"WHERE `stripped` = :level");
	$query->bind(":qualify", $qualify);
	$query->bind(":gold", $gold);
	$query->bind(":ultimate", $ultimate);
	$query->bind(":standardiser", $standardiser);
	$query->bind(":basescore", $basescore);
	$query->bind(":basemultiplier", $basemultiplier);
	$query->bind(":goldbonus", $goldbonus);
	$query->bind(":platinumbonus", $platinumbonus);
	$query->bind(":ultimatebonus", $ultimatebonus);
	$query->bind(":difficulty", $difficulty);
	$query->bind(":golddifficulty", $golddifficulty);
	$query->bind(":ultimatedifficulty", $ultimatedifficulty);
	$query->bind(":level", $stripped);
	$query->execute();

	$query = pdo_prepare("UPDATE `officiallevels` SET " .
								"`qualify` = :qualify," .
								"`gold` = :gold," .
								"`ultimate` = :ultimate," .
								"`standardiser` = :standardiser," .
								"`basescore` = :basescore," .
								"`basemultiplier` = :basemultiplier," .
								"`goldbonus` = :goldbonus," .
								"`platinumbonus` = :platinumbonus," .
								"`ultimatebonus` = :ultimatebonus," .
								"`difficulty` = :difficulty," .
								"`golddifficulty` = :golddifficulty," .
								"`ultimatedifficulty` = :ultimatedifficulty " .
								"WHERE `stripped` = :level");
	$query->bind(":qualify", $qualify);
	$query->bind(":gold", $gold);
	$query->bind(":ultimate", $ultimate);
	$query->bind(":standardiser", $standardiser);
	$query->bind(":basescore", $basescore);
	$query->bind(":basemultiplier", $basemultiplier);
	$query->bind(":goldbonus", $goldbonus);
	$query->bind(":platinumbonus", $platinumbonus);
	$query->bind(":ultimatebonus", $ultimatebonus);
	$query->bind(":difficulty", $difficulty);
	$query->bind(":golddifficulty", $golddifficulty);
	$query->bind(":ultimatedifficulty", $ultimatedifficulty);
	$query->bind(":level", $stripped);
	$query->execute();
	echo("Updated Rating Information");
} else {
	echo("Level Rating Information:");
}
$misarray = getLevelArray($stripped);

if ($access > 1) {
?>
			<form action="scores.php" method="POST">
			<input type="hidden" name="game" value="<?php echo($_POST["game"]); ?>">
			<input type="hidden" name="diff" value="<?php echo($_POST["diff"]); ?>">
			<input type='hidden' name="level" value="<?php echo($_POST["level"]); ?>">
			<input type="hidden" name="info" value="true">
<?php } ?>
			<table class="table-bordered">
			<tbody>
			<tr>
			<td>Qualify Time:</td>
			<td><input type="text" name="QualifyTime" value="<?php echo($misarray["QualifyTime"]); ?>"></td>
			</tr>
			<tr>
			<td>Gold Time:</td>
			<td><input type="text" name="GoldTime" value="<?php echo($misarray["GoldTime"]); ?>"></td>
			</tr>
			<tr>
			<td>Ultimate Time:</td>
			<td><input type="text" name="UltimateTime" value="<?php echo($misarray["UltimateTime"]); ?>"></td>
			</tr>
			<tr>
			<td>Standardiser:</td>
			<td><input type="text" name="Standardiser" value="<?php echo($misarray["Standardiser"]); ?>"></td>
			</tr>
			<tr>
			<td>Base Score:</td>
			<td><input type="text" name="BaseScore" value="<?php echo($misarray["BaseScore"]); ?>"></td>
			</tr>
			<tr>
			<td>Base Multiplier:</td>
			<td><input type="text" name="BaseMultiplier" value="<?php echo($misarray["BaseMultiplier"]); ?>"></td>
			</tr>
			<tr>
			<td>Gold Bonus:</td>
			<td><input type="text" name="GoldBonus" value="<?php echo($misarray["GoldBonus"]); ?>"></td>
			</tr>
			<tr>
			<td>Platinum Bonus:</td>
			<td><input type="text" name="PlatinumBonus" value="<?php echo($misarray["PlatinumBonus"]); ?>"></td>
			</tr>
			<tr>
			<td>Ultimate Bonus</td>
			<td><input type="text" name="UltimateBonus" value="<?php echo($misarray["UltimateBonus"]); ?>"></td>
			</tr>
			<tr>
			<td>Difficulty</td>
			<td><input type="text" name="Difficulty" value="<?php echo($misarray["Difficulty"]); ?>"></td>
			</tr>
			<tr>
			<td>Gold Difficulty</td>
			<td><input type="text" name="GoldDifficulty" value="<?php echo($misarray["GoldDifficulty"]); ?>"></td>
			</tr>
			<tr>
			<td>Ultimate Difficulty</td>
			<td><input type="text" name="UltimateDifficulty" value="<?php echo($misarray["UltimateDifficulty"]); ?>"></td>
			</tr>
<?php if ($access > 1) { ?>
			<tr>
			<td colspan="2"><input type="submit" class="btn btn-primary" value="Update"></td>
			</tr>
<?php } ?>
			</tbody>
			</table>
<?php if ($access > 1) { ?>
			</form>
<?php } ?>
			</td>
			<td>
<?php
if (array_key_exists("notes", $_POST) && $access > 1) {
	$notes = $_POST["notes"];
	$query = pdo_prepare("UPDATE `levels` SET `notes` = :notes WHERE `stripped` = :level");
	$query->bind(":notes", $notes);
	$query->bind(":level", $stripped);
	$query->execute();

	$query = pdo_prepare("UPDATE `officiallevels` SET `notes` = :notes WHERE `stripped` = :level");
	$query->bind(":notes", $notes);
	$query->bind(":level", $stripped);
	$query->execute();

	$misarray["Notes"] = $notes;
	echo("Updated Level Notes");
} else {
	echo("Level Notes:");
}
?>
			<br>
<?php if ($access > 1) { ?>
			<form action="scores.php" method="POST">
			<input type="hidden" name="game" value="<?php echo($_POST["game"]); ?>">
			<input type="hidden" name="diff" value="<?php echo($_POST["diff"]); ?>">
			<input type='hidden' name="level" value="<?php echo($_POST["level"]); ?>">
			<input type="hidden" name="notes" value="true">
<?php } ?>
<textarea name="notes">
<?php echo($misarray["Notes"]); ?>
</textarea><br>
<?php if ($access > 1) { ?>
			<input type="submit" class="btn btn-primary" value="Update Notes">
			</form>
<?php
}
}
?>
			</tr>
			</tbody>
			</table>

<?php

if (array_key_exists("delete", $_POST) && array_key_exists("id", $_POST) && $access > MINIMUM_ACCESS - 1) {
	//Update their rating accordingly
	$query = pdo_prepare("SELECT `username`, `rating`, `level`, `score`, `gametype` FROM `scores` WHERE `id` = :id");
	$query->bind(":id", $_POST["id"]);
	$result = $query->execute();

	if ($result->rowCount()) {
		list($user, $rating, $level, $score, $gametype) = $result->fetchIdx();

		$query = pdo_prepare("SELECT `rating` FROM `scores` WHERE `level` = :level AND `score` >= :score AND `gametype` = :gametype AND `username` = :username AND `id` != :id LIMIT 1");
		$query->bind(":level", $level);
		$query->bind(":score", $score);
		$query->bind(":gametype", $gametype);
		$query->bind(":username", $user);
		$query->bind(":id", $_POST["id"]);
		$result = $query->execute();

		$nextbest = 0;
		if ($result->rowCount()) {
			$nextbest = $result->fetchIdx(0);
		}

		echo("$user just got a rating diff of " . ($rating - $nextbest));

		$ratingField = "rating_";

		if ($gametype == "Gold")
			$ratingField .= "mbg";
		else if ($gametype == "Platinum")
			$ratingField .= "mbp";
		else if ($gametype == "Ultra")
			$ratingField .= "mbu";
		else if ($gametype == "LBCustom")
			$ratingField .= "custom";

		$change = $rating - $nextbest;
		$query = pdo_prepare("UPDATE `users` SET `rating` = `rating` - :change, `$ratingField` = `$ratingField` - :change WHERE `username` = :username");
		$query->bind(":change", $change);
		$query->bind(":username", $user);
		$query->execute();

		$query = pdo_prepare("DELETE FROM `scores` WHERE `id` = :id");
		$query->bind(":id", $_POST["id"]);
		$result = $query->execute();
		echo("<br>Deleted score {$_POST["id"]}<br>");
	}
}

if (array_key_exists("level", $_POST)) {
?>
			<table class="table-striped table-bordered" style="position: absolute">
				<tbody>
					<tr>
						<th>Row</th>
						<th>Username</th>
						<th>Level</th>
						<th>Score</th>
						<th>Ultimate Time</th>
						<th>Platinum / Gold Time</th>
						<th>Modifiers</th>
						<th>Origin</th>
						<th>Old Rating</th>
						<th>New Rating</th>
						<th>&Delta; Rating</th>
<?php
$showbuttons = getAccess() > MINIMUM_ACCESS - 1;
if ($showbuttons) { ?>
						<th>Action</th>
<?php if (SHOW_MILLIONIFY) { ?>
						<th>Millionify</th>
<?php }
}
?>
					</tr>
<?php if ($access > MINIMUM_ACCESS - 1) {
	$misarray  = getLevelArray($stripped);

	if (array_key_exists("adding", $_POST) && array_key_exists("ausername", $_POST) && array_key_exists("ascore", $_POST)) {
		$ausername = $_POST["ausername"];
		$ascore = $_POST["ascore"];
		$amodifiers = $_POST["amodifiers"];

		//Get their new rating
		$newrating = getRating($ascore, $stripped, $misarray["MissionGame"]);

		//Get their old rating
		$aquery = pdo_prepare("SELECT `rating` FROM `scores` WHERE `level` = :stripped AND `username` = :username ORDER BY `score` ASC LIMIT 1");
		$aquery->bind(":stripped", $stripped);
		$aquery->bind(":username", $ausername);
		$aresult = $aquery->execute();

		$oldrating = 0;
		if ($aresult->rowCount())
			$oldrating = $aresult->fetchIdx(0);

		//What's the difference
		$add = max($newrating - $oldrating, 0);

		$time = getServerTime();

		//Now actually add it!
		$query = pdo_prepare("INSERT INTO `scores` SET `username` = :username, `score` = :score, `level` = :level, `type` = :type, `gametype` = :gametype, `rating` = :rating, `modifiers` = :modifiers, `origin` = 2, `time` = :time");
		$query->bind(":username", $ausername);
		$query->bind(":score", $ascore);
		$query->bind(":level", $stripped);
		$query->bind(":type", $misarray["MissionType"]);
		$query->bind(":gametype", $misarray["MissionGame"]);
		$query->bind(":rating", $newrating);
		$query->bind(":modifiers", $modifiers);
		$query->bind(":time", $time);
		$query->execute();

		$ratingField = "rating_";
		if ($misarray["MissionGame"] == "Gold")
			$ratingField .= "mbg";
		else if ($misarray["MissionGame"] == "Platinum")
			$ratingField .= "mbp";
		else if ($misarray["MissionGame"] == "Ultra")
			$ratingField .= "mbu";
		else if ($misarray["MissionGame"] == "MultiPlayer")
			$ratingField .= "mp";
		else if ($misarray["MissionGame"] == "LBCustom")
			$ratingField .= "custom";

		//And give them their rating
		$query = pdo_prepare("UPDATE `users` SET `rating` = `rating` + :add, `$ratingField` = `$ratingField` + :add WHERE `username` = :username");
		$query->bind(":add", $add);
		$query->bind(":username", $ausername);
		$query->execute();

		//Spit it back to us
		echo("ADDING $ausername: $ascore (rating: $newrating, old is $oldrating, rating to add is $add)\n");
	}
?>
<tr>
	<td><b>+</b></td>
	<td><input type="text" class="input" id="add-username" placeholder="Username"></td>
	<td><?php echo($stripped); ?></td>
	<td><input type="text" class="input-small" id="add-time" placeholder="Time"></td>
	<td><?php echo($misarray["UltimateTime"]); ?></td>
	<td><?php echo($misarray["GoldTime"]); ?></td>
	<td><input type="text" class="input-mini" id="add-modifiers" placeholder="Mods"></td>
	<td>Marbleblast.com</td>
	<td>N/A</td>
	<td id="add-rating">0</td>
	<td>N/A</td>
	<td colspan="2"><button class="btn btn-small btn-success" id="add-submit">Add</button>
<script type="text/javascript">
(function($){
	$("#add-username").keyup(function(event) {
		if (event.keyCode == 13) {
			//Enter, submit form here
			$("#add-submit").click();
			return;
		}
	});
	$("#add-modifiers").keyup(function(event) {
		if (event.keyCode == 13) {
			//Enter, submit form here
			$("#add-submit").click();
			return;
		}
	});
	$("#add-time").keyup(function(event) {
		if (event.keyCode == 13) {
			//Enter, submit form here
			$("#add-submit").click();
			return;
		}
		var parsed = parseInt($(this).val());
		$(this).val(parsed !== parsed ? "" : parsed);

		//Update rating here
		if (parsed === parsed) {
			<?php
			if ($misarray["Disabled"]) { ?>
			var rating = 0;
			<?php } else { ?>
			var type = "<?php echo($misarray["MissionType"]);?>";
			var game = "<?php echo($misarray["MissionGame"]);?>";

			var completion = 0;
			if (type == "Beginner") completion += 1000;
			if (type == "Intermediate") completion += 2000;
			if (type == "Advanced") completion += (game == "Platinum" ? 3000 : 4000);
			if (type == "Expert") completion += 5000;
			if (type == "LBCustom") completion += 4000;

			var bonus = 0;
			<?php if ($misarray["GoldTime"]) { ?>
			bonus += <?php echo(($misarray["PlatinumBonus"] ? $misarray["PlatinumBonus"] : $misarray["GoldBonus"])); ?>;
			<?php } ?>
			<?php if ($misarray["UltimateTime"]) { ?>
			bonus += <?php echo($misarray["UltimateBonus"]); ?>;
			<?php } ?>

			var time = parsed / 1000;
			var standardiser = <?php echo($misarray["Standardiser"]); ?>;
			var baseScore = <?php echo($misarray["BaseScore"]); ?>;
			var multiplier = <?php echo($misarray["BaseMultiplier"]); ?>;

			var rating = (completion + bonus + (((Math.log10(time) * standardiser) - baseScore) * -1)) * multiplier;
			rating = Math.floor(rating < 0 ? 0 : rating);
			<?php } ?>

			$("#add-rating").text(rating);
		}
	});
	$("#add-submit").click(function(event) {
		//Submit here
		var form = $("<form action=\"scores.php\" method=\"POST\"/>");
		form.append($("<input type=\"hidden\" name=\"game\"/>").val("<?php echo($_POST["game"]); ?>"));
		form.append($("<input type=\"hidden\" name=\"diff\"/>").val("<?php echo($_POST["diff"]); ?>"));
		form.append($("<input type=\"hidden\" name=\"level\"/>").val("<?php echo($_POST["level"]); ?>"));
		form.append($("<input type=\"hidden\" name=\"adding\"/>").val(true));
		form.append($("<input type=\"hidden\" name=\"ausername\"/>").val($("#add-username").val()));
		form.append($("<input type=\"hidden\" name=\"ascore\"/>").val($("#add-time").val()));
		form.append($("<input type=\"hidden\" name=\"amodifiers\"/>").val($("#add-modifiers").val()));
		form.appendTo($(document.body));
		form.submit();
	});
})(jQuery);
</script>
</td>
</tr>

<?php }

//List scores

$query = pdo_prepare("SELECT * FROM `scores` WHERE `level` = :level ORDER BY `score` ASC");
$query->bind(":level", $stripped);
$result = $query->execute();

$rows = min(10000, $result->rowCount());

for ($i = 0; $i < $rows; $i ++) {
	$row = $result->fetch();
	/* id, username, mission, time, old rating */

	$username  = str_replace("-SPC-", " ", $row["username"]);
	$time      = $row["score"];
	$mission   = $row["level"];
	$oldrating = $row["rating"];
	$id        = $row["id"];
	$origin    = $row["origin"];
	$modifiers = $row["modifiers"];
	$date      = $row["time"];

	$stripped  = stripLevel($mission);
	$misarray  = getLevelArray($stripped);
	$newrating = getRating($time, $stripped, $misarray["MissionGame"]);

	$ultimate = $misarray["UltimateTime"];
	$platinum = $misarray["GoldTime"];

	$modifiers = ($modifiers & EASTER_EGG ? "EE " : "") . ($modifiers & NO_JUMP ? "NJ " : "") . ($modifiers & DOUBLE_DIAMOND ? "DD " : "") . ($modifiers & NO_TT ? "TT " : "");

	//Modifiers patch (MBU achievements) released
	if ($date < 63411108 && $modifiers == 0)
		$modifiers .= "UNK";

	$origin = ($origin == 0 ? "PhilsEmpire" : "Marbleblast.com");

	$ratingchange = $newrating - $oldrating;

	echo("<tr>");
		echo("<td>");
			echo($i + 1);
		echo("</td>");
		echo("<td>");
			echo("$username");
		echo("</td>");
		echo("<td>");
			echo("$mission");
		echo("</td>");
		echo("<td>");
			echo("$time / " . formatTime($time));
		echo("</td>");
		echo("<td>");
			echo("$ultimate / " . formatTime($ultimate));
		echo("</td>");
		echo("<td>");
			echo("$platinum / " . formatTime($platinum));
		echo("</td>");
		echo("<td>");
			echo("$modifiers");
		echo("</td>");
		echo("<td>");
			echo("$origin");
		echo("</td>");
		echo("<td>");
			echo("$oldrating");
		echo("</td>");
		echo("<td>");
			echo("$newrating");
		echo("</td>");
		echo("<td>");
			echo("$ratingchange");
		echo("</td>");
		if ($showbuttons) {
			echo("<td>");
				echo("<form action='scores.php' method='POST'><input type='hidden' name='game' value=\"{$_POST["game"]}\"><input type='hidden' name='diff' value=\"{$_POST["diff"]}\"><input type='hidden' name='level' value=\"{$_POST["level"]}\"><input type='hidden' name='id' value='$id'><input type='hidden' name='delete' value='true'><input type='submit' class='btn btn-small btn-danger' value='Delete'></form>");
			echo("</td>");

			if (SHOW_MILLIONIFY) {
				echo("<td>");
					echo("<form action='scores.php' method='POST'><input type='hidden' name='game' value=\"{$_POST["game"]}\"><input type='hidden' name='diff' value=\"{$_POST["diff"]}\"><input type='hidden' name='level' value=\"{$_POST["level"]}\"><input type='hidden' name='id' value='$id'><input type='hidden' name='million' value='true'><input type='submit' class='btn btn-small btn-danger' value='Millionify'></form>");
				echo("</td>");
			}
		}
	echo("</tr>\n");
}
?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
	}
} else {
	accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
