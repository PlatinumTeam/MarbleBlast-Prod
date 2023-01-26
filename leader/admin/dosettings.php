<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	foreach ($_POST["settings"] as $key => $value) {
		if ($value == "") {
			// Set it to the default
			$query = pdo_prepare("SELECT `default` FROM `settings` WHERE `key` = :key");
			$query->bind(":key", $key);
			$result = $query->execute();
			if ($result->rowCount()) {
				$row = $result->fetchIdx();
				$value = $row[0];
			} else
				continue;
		} else
			$value = str_replace(array("\r\n", "\\r\\n", "\n", "<br>", "&#10;"), "\\n", $value);
		$query = pdo_prepare("UPDATE `settings` SET `value` = :value WHERE `key` = :key");
		$query->bind(":value", $value);
		$query->bind(":key", $key);
		$result = $query->execute();
	}
	headerDie("Location: settings.php");
}

?>
