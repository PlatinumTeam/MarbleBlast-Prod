<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$deleteid = intVal($_GET["challenge"]);
	$query = pdo_prepare("DELETE FROM `scdata` WHERE `id` = :deleteid");
	$query->bind(":deleteid", $deleteid, PDO::PARAM_INT);
	$result = $query->execute();
	if ($result)
		headerDie("Location: scedit.php?success=1");
	else
		headerDie("Location: scedit.php?error=1");
}

?>
