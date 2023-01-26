<?php
$start = gettimeofday(true);

//From PhilsEmpire.com
function compare_pswd($user, $givenpassword, $truepassword) {
	$salt = substr($user, 1, 3) . "XXXXXXXXXXXXXXXXXXXXX";
	$hash = md5($salt . md5($givenpassword . $salt));

	if ($hash == $truepassword)
		return true;
	else
		return false;
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
	$ms = floor($ms / 10);
	return ($neg ? "-" : "") . str_pad($m, 2, "0", STR_PAD_LEFT) . ":" . str_pad($s, 2, "0", STR_PAD_LEFT) . ":" . str_pad($ms, 2, "0", STR_PAD_RIGHT);
}

set_time_limit(0);

$allow_nonwebchat = true;
require("opendb.php");
require("lbratings.php");

$joomlaAuth = true;

//Errors redirect to here
$sig_redirect = "/index.php/profile";

$login = checkPostLogin();
if ($login != 7) { ?>
Marbleblast.com password was incorrect.
<a href="http://marbleblast.com/index.php/profile?impfail=1">Continue...</a>
<?php
	die();
}

// Check if they sent the right vars
if (!checkPostVars("pusername", "ppassword")) { ?>
Missing Data.
<a href="http://marbleblast.com/index.php/profile?impfail=1">Continue...</a>
<?php
	die();
}


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

list($pusername, $ppassword) = getPostValues("pusername", "ppassword");
$user = getUsername();

//Get old database hashed password
$query = $oldConnection->prepare("SELECT * FROM `users` WHERE `username` = :username");
$query->bind(":username", $pusername);
$result = $query->execute();

//No account
if ($result->rowCount() == 0) {?>
Can't find anyone on PhilsEmpire with that username! <a href="http://marbleblast.com/index.php/profile?impfail=1">Continue...</a>
<?php
	die();
}

$prow = $result->fetch();
$pserverpass = $prow["password"];

//Check it
if (!compare_pswd($pusername, $ppassword, $pserverpass)) { ?>
PhilsEmpire password was incorrect! <a href="http://marbleblast.com/index.php/profile?impfail=1">Continue...</a>
<?php
	die();
}

//Make sure they're not importing twice
if ($prow["imported"] == 1) { ?>
Already imported this account! <a href="http://marbleblast.com/index.php/profile?impfail=1">Continue...</a>
<?php
	die();
}

$query = $oldConnection->prepare("SELECT * FROM `times` WHERE `username` = :username ORDER BY `id` ASC");
$query->bind(":username", $pusername);
$result = $query->execute();

$rows = min(25, $result->rowCount());

echo("You have " . $result->rowCount() . " scores to import!<br>\n");
echo("Here is a table of the first $rows:<br>\n")
?>
<style>
table, tr, td, th {
	border: 1px solid #000;
	border-collapse: collapse;
}
</style>
<table>
	<tbody>
		<tr>
			<th>Username</th>
			<th>Level</th>
			<th>Score</th>
			<th>Qualify Time</th>
			<th>Platinum / Gold Time</th>
			<th>Ultimate Time</th>
			<th>Old Rating</th>
			<th>New Rating</th>
			<th>&delta; Rating</th>
		</tr>
<?php

$totalnewrating = 0;
$totaloldrating = 0;

while (($row = $result->fetchIdx()) !== false) {
	/* id, username, mission, time, old rating */

	$id        = $row[0];
	$username  = str_replace("-SPC-", " ", $row[1]);
	$mission   = $row[2];
	$time      = $row[3];
	$oldrating = $row[5];

	$stripped  = stripLevel($mission);
	$misarray  = getLevelArray($stripped);
	$newrating = getRating($time, $stripped, $misarray["MissionGame"]);

	$ultimate = $misarray["UltimateTime"];
	$qualify  = $misarray["QualifyTime"];
	$platinum = $misarray["GoldTime"];

	$ratingchange = max(0, $newrating) - $oldrating;

	if ($newrating >= 0) {
		$query = pdo_prepare("INSERT INTO `scores` (`username`, `score`, `level`, `type`, `gametype`, `rating`, `origin`, `time`) VALUES " .
									"(:username, :score, :level, :type, :gametype, :rating, :origin, :time)");
		$query->bind(":username",  $user);
		$query->bind(":score",     $time);
		$query->bind(":level",     $stripped);
		$query->bind(":type",      $misarray["MissionType"]);
		$query->bind(":gametype",  $misarray["MissionGame"]);
		$query->bind(":rating",    $newrating);
		$query->bind(":origin",    "0");
		$query->bind(":time",      getServerTime());
		$query->execute();

		if (!$misarray["Disabled"]) {
			$totalnewrating += $newrating;
			$totaloldrating += $oldrating;
		}
	}
	if ($newrating == -1) $newrating = "Invalid Mission!";
	if ($newrating == -2) $newrating = "Invalid Score!";

	if ($rows > 0 && !$misarray["Disabled"]) {
		echo("<tr>");
			echo("<td>");
				echo("$username");
			echo("</td>");
			echo("<td>");
				echo("$mission");
			echo("</td>");
			echo("<td>");
				echo(formatTime($time));
			echo("</td>");
			echo("<td>");
				echo(formatTime($qualify));
			echo("</td>");
			echo("<td>");
				echo(formatTime($platinum));
			echo("</td>");
			echo("<td>");
				echo(formatTime($ultimate));
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
		echo("</tr>\n");
		$rows --;
	}
}
?>
	<tr>
	<td colspan="6">Total</td>
	<td><?php echo($totaloldrating); ?></td>
	<td><?php echo($totalnewrating); ?></td>
	<td><?php echo($totalnewrating - $totaloldrating); ?></td>
	</tr>
	</tbody>
</table>
<?php
$user = getUsername();
//echo("RECALCULATING FOR $user<br><br>");
$query = pdo_prepare("SELECT * FROM `scores` WHERE `username` = :user ORDER BY `rating` DESC");
$query->bind(":user", $user, PDO::PARAM_STR);
$result = $query->execute();

$seen = array();
$changeAll = 0;
$changeTop = 0;

$largest = 0;
$largestA = 0;
$largestName = "";
$largestNameA = "";

$count = 0;
$last = array();

while (($row = $result->fetch()) !== false) {
	$score    = $row["score"];
	$level    = $row["level"];
	$type     = $row["type"];
	$gametype = $row["gametype"];
	$rating   = $row["rating"];
	$id       = $row["id"];

	if (array_key_exists("level", $last) &&
		$last["level"] == $row["level"] &&
	    $last["score"] == $row["score"] &&
	    $last["rating"] == $row["rating"] &&
	    $last["gametype"] == $row["gametype"] &&
	    $last["type"] == $row["type"]) {
		$query = pdo_prepare("DELETE FROM `scores` WHERE `id` = :id");
		$query->bind(":id", $id, PDO::PARAM_INT);
		//echo("Removed duplicate score for level $level of score $score and rating " . number_format($rating) . "!<br>");
		$query->execute();
		continue;
	}

	$last = $row;

	$realRating = getRating($score, $level, $gametype);

	//Something's strange
	if ($realRating == -5) {
		$data = getLevelArray($level);
		//echo("Update gametype on score for level $level of score $score and rating " . number_format($rating) . ". Was $gametype, should be {$data["MissionGame"]}!<br>");
		$gametype = $data["MissionGame"];
		$realRating = getRating($score, $level, $gametype);

		$query = pdo_prepare("UPDATE `scores` SET `gametype` = :gametype WHERE `id` = :id");
		$query->bind(":gametype", $gametype, PDO::PARAM_STR);
		$query->bind(":id", $id, PDO::PARAM_INT);
		$query->execute();
	}

	$difference = ($realRating - $rating);
	$changeAll += $difference;

	if ($realRating != $rating) {
		$count ++;
		//Update on the table
		$query = pdo_prepare("UPDATE `scores` SET `rating` = :realRating WHERE `id` = :id");
		$query->bind(":realRating", $realRating, PDO::PARAM_INT);
		$query->bind(":id", $id, PDO::PARAM_INT);
		$query->execute();

		//Update this too
		$row["rating"] = $realRating;
		$last = $row;
	}

	//No duplicates
	if (in_array($level, $seen))
		continue;
	array_push($seen, $level);

	//Now we have all the top scores

	$changeTop += $difference;

	if ($rating != $realRating) {
		$data = getLevelArray($level);

		if ($data == NULL)
			continue;
		$type = $data["MissionType"];
		$ratingField = "rating_";

		if ($data["MissionGame"] == "Gold")
			$ratingField .= "mbg";
		else if ($data["MissionGame"] == "Platinum")
			$ratingField .= "mbp";
		else if ($data["MissionGame"] == "MultiPlayer")
			$ratingField .= "mp";
		else if ($data["MissionGame"] == "LBCustom")
			$ratingField .= "custom";

		$query = pdo_prepare("UPDATE `users` SET `rating` = `rating`+$difference, `$ratingField` = `$ratingField`+$difference WHERE `username` = :user");
		$query->bind(":user", $user, PDO::PARAM_STR);
		$query->execute();

		if ($rating > $realRating && abs($difference) > $largestA) {
			$largestA = abs($difference);
			$largestNameA = $level;
		}
		if ($rating < $realRating && abs($difference) > $largest) {
			$largest = abs($difference);
			$largestName = $level;
		}

		//echo("Rating for $level of time $score is " . number_format($rating) . ", should be " . number_format($realRating) . ", discrepancy of " . number_format($difference));
		// if ($rating > $realRating)
			//echo("!");
		//echo("<br>");
	}
}

// if ($count == 0) {
	//echo("All scores already up to date.<br><br>");
// } else {
	//echo("All scores rating was " . number_format($changeAll) . " points lower than normal<br>");
	//echo("Top scores rating was " . number_format($changeTop) . " points lower than normal<br>");
	//echo("<br>");
	//echo("Largest difference (positive): $largestName with a rating difference of " . number_format($largest) . "<br>");
	//echo("Largest difference (negative): $largestNameA with a rating difference of -" . number_format($largestA) . "!<br>");
	//echo("<br>");
	//echo("Updated " . number_format($count) . " scores<br>");
// }

$query = pdo_prepare("SELECT * FROM `users` WHERE `username` = :user");
$query->bind(":user", $user, PDO::PARAM_STR);
$result = $query->execute();
$userArray = $result->fetch();

$query = pdo_prepare("SELECT * FROM `scores` WHERE `username` = :user ORDER BY `rating` DESC");
$query->bind(":user", $user, PDO::PARAM_STR);
$result = $query->execute();

$seen = array();
$finalRating = 0;
$finalCategories = array();

while (($row = $result->fetch()) !== false) {
	$score    = $row["score"];
	$level    = $row["level"];
	$type     = $row["type"];
	$gametype = $row["gametype"];
	$rating   = $row["rating"];
	$id       = $row["id"];

	//No duplicates
	if (in_array($level, $seen))
		continue;
	array_push($seen, $level);

	//Now we have all the top scores
	$finalRating += $rating;

	$data = getLevelArray($level);
	$type = $data["MissionType"];
	$ratingField = "rating_";

	if ($data["MissionGame"] == "Gold")
		$ratingField .= "mbg";
	else if ($data["MissionGame"] == "Platinum")
		$ratingField .= "mbp";
	else if ($data["MissionGame"] == "MultiPlayer")
		$ratingField .= "mp";
	else if ($data["MissionGame"] == "LBCustom")
		$ratingField .= "custom";

	if ($ratingField != "rating_") {
		if (array_key_exists($ratingField, $finalCategories))
			$finalCategories[$ratingField] += $rating;
		else
			$finalCategories[$ratingField]  = $rating;
	}
}

//And add achievements too
$finalRating += $userArray["rating_achievements"];

if ($userArray["rating"] != $finalRating) {
	$difference = $finalRating - $userArray["rating"];
	//echo("User's rating is now " . number_format($finalRating) . ", was " . number_format($userArray["rating"]) . ". Difference of " . number_format($difference) . "<br>");

	$query = pdo_prepare("UPDATE `users` SET `rating` = :finalRating WHERE `username` = :user");
	$query->bind(":finalRating", $finalRating, PDO::PARAM_INT);
	$query->bind(":user", $user, PDO::PARAM_STR);
	$query->execute();
}
foreach ($finalCategories as $ratingField => $rating) {
	if ($userArray[$ratingField] != $rating) {
		$difference = $rating - $userArray[$ratingField];
		//echo("User's rating for category $ratingField now " . number_format($rating) . ", was " . number_format($userArray[$ratingField]) . ". Difference of " . number_format($difference) . "<br>");

		$query = pdo_prepare("UPDATE `users` SET `$ratingField` = :rating WHERE `username` = :user");
		$query->bind(":rating", $rating, PDO::PARAM_INT);
		$query->bind(":user", $user, PDO::PARAM_STR);
		$query->execute();
	}
}

//echo("Scores update complete.<br>");

$query = $oldConnection->prepare("UPDATE `users` SET `imported` = 1, `imported_by` = :username WHERE `username` = :username");
$query->bind(":username", $pusername);
$query->execute();

$query = pdo_prepare("INSERT INTO `imports` (`username`, `pusername`) VALUES (:username, :pusername)");
$query->bind(":username", $user);
$query->bind(":pusername", $pusername);
$query->execute();

$time = gettimeofday(true) - $start;
echo("Update took $time seconds.");
?>
<br><br>
<a href="http://marbleblast.com/index.php/profile?imp=1">Continue...</a>