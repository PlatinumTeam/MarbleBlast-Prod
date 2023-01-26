<?php
require("Database.php");

$uid = requireParam("uid");
$shapeFile = requireParam("shapeFile");
$skin = requireParam("skin");

$userInfo = getUserInfoByUid($uid);

$query = $db->prepare("SELECT `id` FROM `@_marble_selections` WHERE `userid` = :userid AND `shape_file` = :shapeFile AND `skin` = :skin ORDER BY `last_update` LIMIT 1");
$query->bindParam(":userid", $userInfo["id"]);
$query->bindParam(":shapeFile", $shapeFile);
$query->bindParam(":skin", $skin);
$query->execute();
if ($query->rowCount() == 0) {
	$query = $db->prepare("INSERT INTO `@_marble_selections` SET `userid` = :userid, `shape_file` = :shapeFile, `skin` = :skin");
	$query->bindParam(":userid", $userInfo["id"]);
	$query->bindParam(":shapeFile", $shapeFile);
	$query->bindParam(":skin", $skin);

	if ($query->execute()) {
		techo("SUCCESS");
		$marbleId = $db->lastInsertId();
	} else {
		error("FAILURE");
	}
} else {
	$marbleId = $query->fetchColumn(0);
}

$query = $db->prepare("UPDATE `@_marble_selections` SET `use_count` = `use_count` + 1 WHERE `userid` = :userid AND `shape_file` = :shapeFile AND `skin` = :skin");
$query->bindParam(":userid", $userInfo["id"]);
$query->bindParam(":shapeFile", $shapeFile);
$query->bindParam(":skin", $skin);
$query->execute();


$query = $db->prepare("INSERT INTO `@_user_marble_selection` SET `userid` = :userid, `marble_selection_id` = :marbleid ON DUPLICATE KEY UPDATE `marble_selection_id` = :marbleid");
$query->bindParam(":userid", $userInfo["id"]);
$query->bindParam(":marbleid", $marbleId);
$query->execute();
