<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$user = Login::getCurrentUser();
$major = (int)param("major");
$minor = (int)param("minor");
$vendor = param("vendor");
$renderer = param("renderer");
$os = param("os");
$extensions = param("extensions");

//See if they have a row for this graphics card yet
$query = $db->prepare("
	SELECT id FROM ex82r_metrics_graphics_info WHERE
	user_id = :user_id AND
	major = :major AND
	minor = :minor AND
	vendor = :vendor AND
	renderer = :renderer AND 
	os = :os
");
$query->bindValue(":user_id", $user->id);
$query->bindValue(":major", $major);
$query->bindValue(":minor", $minor);
$query->bindValue(":vendor", $vendor);
$query->bindValue(":renderer", $renderer);
$query->bindValue(":os", $os);
$query->execute();

$graphicsId = 0;

if ($query->rowCount() > 0) {
	//Update existing data
	$graphicsId = $query->fetchColumn();
} else {
	//Create new data
	$query = $db->prepare("
		INSERT INTO ex82r_metrics_graphics_info SET
			user_id = :user_id,
			major = :major,
			minor = :minor,
			vendor = :vendor,
			renderer = :renderer, 
			os = :os
	");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":major", $major);
	$query->bindValue(":minor", $minor);
	$query->bindValue(":vendor", $vendor);
	$query->bindValue(":renderer", $renderer);
	$query->bindValue(":os", $os);
	$query->execute();
	$graphicsId = $db->lastInsertId();
}

//Find new extensions
$query = $db->prepare("
	SELECT extension FROM ex82r_metrics_graphics_extensions WHERE graphics_id = :gid
");
$query->bindValue(":gid", $graphicsId);
$query->execute();

$currExts = $query->fetchAll(PDO::FETCH_COLUMN);

$extensions = array_filter($extensions);
$newExts = array_diff($extensions, $currExts);

//Add new extensions
foreach ($newExts as $ext) {
	$query = $db->prepare("
		INSERT INTO ex82r_metrics_graphics_extensions SET
		graphics_id = :gid,
		extension = :extension
	");
	$query->bindValue(":gid", $graphicsId);
	$query->bindValue(":extension", $ext);
	$query->execute();
}

techo("SUCCESS");
