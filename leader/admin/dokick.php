<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();
$kicker = getUsername();

if ($access > 0) {
	$tokick = getUsername($_POST["user"]);
	$message = $_POST["message"];
	$ban = $_POST["ban"] == "true" ? 1 : 0;

	$kickAccess = getAccess($tokick);

	if ($kickAccess < $access || $access == 2) {
		postNotify("kick", $tokick, 1, "$kicker $message");
		postNotify("logout", $tokick, -1);
		if ($ban) {
			$query =
				pdo_prepare("UPDATE `users` SET `banned` = 1 WHERE `username` = :tokick AND (`access` < :access OR `access` = 3)");
			$query->bind(":tokick", $tokick, PDO::PARAM_STR);
			$query->bind(":access", $access, PDO::PARAM_INT);
			$result = $query->execute();
		}
		$query =
			pdo_prepare("UPDATE `users` SET `kicknext` = 1 WHERE `username` = :tokick AND (`access` < :access OR `access` = 3)");
		$query->bind(":access", $access);
		$query->bind(":tokick", $tokick, PDO::PARAM_STR);
		$result = $query->execute();
		if ($result) {
			echo("GOOD\n");
		} else {
			echo("BAD\n");
		}
	} else {
		echo("BAD\n");
	}
}

?>
