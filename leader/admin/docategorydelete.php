<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$deleteid = intVal($_GET["category"]);
	$query = pdo_prepare("DELETE FROM `categories` WHERE `id` = :deleteid");
	$query->bind(":deleteid", $deleteid, PDO::PARAM_INT);
	$result = $query->execute();
	if ($result)
		headerDie("Location: categories.php?success=1");
	else
		headerDie("Location: categories.php?error=1");
}

?>
