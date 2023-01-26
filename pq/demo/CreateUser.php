<?php
require("Database.php");

$uid = param("uid");
//If they didn't give us a user, make up one
if ($uid === null) {
	$uid = uniqid("pqdemo_");
}

//Make sure the user doesn't exist yet
$query = $db->prepare("SELECT * FROM `lw3qp_users` WHERE `uid` = :uid");
$query->bindParam(":uid", $uid);
$query->execute();

//Zero rows: doesn't exist yet
if ($query->rowCount() === 0) {
	$query = $db->prepare("INSERT INTO `lw3qp_users` SET `uid` = :uid");
	$query->bindParam(":uid", $uid);
	$query->execute();

	techo("CREATE {$uid}");
} else {
	techo("ALREADY");
}