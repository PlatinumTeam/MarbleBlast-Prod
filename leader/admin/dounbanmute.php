<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$id = $_POST["id"];
	$query = pdo_prepare("UPDATE `bans` SET `end` = CURRENT_TIMESTAMP WHERE `id` = :id");
	$query->bind(":id", $id, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result)
		echo("GOOD\n");
	else
		echo("BAD\n");
}

?>
