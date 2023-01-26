<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	// Tons of variables
	$username            =       ($_POST["user"]);
	$email               =       ($_POST["email"]);
	$showemail           = intval($_POST["showemail"]);
	$signature           =       ($_POST["signature"]);
	$access              = intval($_POST["access"]);
	$rating              = intval($_POST["rating"]);
	$rating_mbp          = intval($_POST["rating_mbp"]);
	$rating_mbg          = intval($_POST["rating_mbg"]);
	$rating_mbu          = intval($_POST["rating_mbu"]);
	$rating_mp           = intval($_POST["rating_mp"]);
	$rating_custom       = intval($_POST["rating_custom"]);
	$rating_achievements = intval($_POST["rating_achievements"]);
	$challengepoints     = intval($_POST["challengepoints"]);
	$muteIndex           =       ($_POST["muteIndex"]);
	$muteMultiplier      =       ($_POST["muteMultiplier"]);

	$query = pdo_prepare("UPDATE `users` SET
								 `email` = ?,
								 `showemail` = ?,
								 `signature` = ?,
								 `access` = ?,
								 `rating` = ?,
								 `rating_mbp` = ?,
								 `rating_mbg` = ?,
								 `rating_mbu` = ?,
								 `rating_mp` = ?,
								 `rating_custom` = ?,
								 `rating_achievements` = ?,
								 `challengepoints` = ?,
								 `muteIndex` = ?,
								 `muteMultiplier` = ?
								 WHERE `username` = ?");

	$query->bindParam(1, $email,                PDO::PARAM_STR);
	$query->bindParam(2, $showemail,            PDO::PARAM_STR);
	$query->bindParam(3, $signature,            PDO::PARAM_STR);
	$query->bindParam(4, $access,               PDO::PARAM_INT);
	$query->bindParam(5, $rating,               PDO::PARAM_INT);
	$query->bindParam(6, $rating_mbp,           PDO::PARAM_INT);
	$query->bindParam(7, $rating_mbg,           PDO::PARAM_INT);
	$query->bindParam(8, $rating_mbu,           PDO::PARAM_INT);
	$query->bindParam(9, $rating_mp,            PDO::PARAM_INT);
	$query->bindParam(10, $rating_custom,       PDO::PARAM_INT);
	$query->bindParam(11, $rating_achievements, PDO::PARAM_INT);
	$query->bindParam(12, $challengepoints,     PDO::PARAM_INT);
	$query->bindParam(13, $muteIndex,           PDO::PARAM_INT);
	$query->bindParam(14, $muteMultiplier,      PDO::PARAM_INT);
	$query->bindParam(15, $username,            PDO::PARAM_STR);

	$result = $query->execute();

	if ($result)
		headerDie("Location: users.php?success=3");
	else
//	echo($query);
		headerDie("Location: useredit.php?error=1");
}

?>
