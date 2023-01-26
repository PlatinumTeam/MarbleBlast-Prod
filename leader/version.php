<?php
$allow_nonwebchat = true;
$ignore_keys = true;

function checkVersion($dump = false) {
	$query = pdo_prepare("SELECT `version` FROM `versions` ORDER BY `id` DESC LIMIT 1");
	$result = $query->execute();

	$version = $result->fetchIdx(0);
	$clientVersion = getPostValue("version");

	if ($version > $clientVersion) {
		if ($dump)
			echo("NEWVERSION $version\n");

		// Get some version info
		$query = pdo_prepare("SELECT * FROM `versions` WHERE `version` = :version");
		$query->bind(":version", $version);
		$result = $query->execute();
		if ($result->rowCount()) {
			$row = $result->fetch();

			$title = $row["title"];
			$desc = str_replace(array("\r\n", "\r", "\n"), "\\n", addslashes($row["desc"]));
			$url = $row["url"];
			$time = date("F jS Y \a\\t g:i a", strtotime($row["timestamp"]));

			if ($dump) {
				echo("TITLE $title\n");
				echo("DESC $desc\n");
				echo("URL $url\n");
				echo("TIME $time\n");
			}
		}

		return false;
	} else {
		if ($dump)
			echo("UPTODATE\n");
		return true;
	}
}

checkVersion(isTorque());

?>
