<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$query = pdo_prepare("SELECT * FROM `loggedIn`");
	$result = $query->execute();
	while (($row = $result->fetch()) !== false) {

	}
}
?>
