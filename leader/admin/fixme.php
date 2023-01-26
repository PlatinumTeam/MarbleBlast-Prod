<?php

exit();

$allow_nonwebchat = true;

define("TEST", true);

require("../opendb.php");

set_error_handler(NULL);

$query = pdo_prepare("SELECT * FROM (
    SELECT `id`, `username`, `address`, SUM(`hits`) AS `hits`, COUNT(*) AS `count` FROM `addresses` GROUP BY `username`, `address`
) AS `counts` WHERE `count` > 1");
$addresses = $query->execute()->fetchAll();

foreach ($addresses as $list => $arr) {
	$query = pdo_prepare("UPDATE `addresses` SET `hits` = :hits WHERE `id` = :id");
	$query->bind(":hits", $arr["hits"]);
	$query->bind(":id", $arr["id"]);
	$query->execute();

	//Find others with this user/addr
	$query = pdo_prepare("DELETE FROM `addresses` WHERE `username` = :username AND `address` = :address AND `id` != :id");
	$query->bind(":username", $arr["username"]);
	$query->bind(":address", $arr["address"]);
	$query->bind(":id", $arr["id"]);
	$query->execute();
}
