<?php

function getWelcomeMessage($mod = false, $guest = false) {
	$welcome = getServerPref("welcome");
	$invite = $guest ? "" : ("\n" . getServerPref("welcomediscord") . "\n");
	$welcome = str_replace('$INVITE', $invite, $welcome);
	$welcome .= "\n\n" . getQOTDText($mod);
	$welcome = str_replace("\n", "\\n", $welcome);

	return $welcome;
}

function getWebchatWelcomeMessage($mod = false, $guest = false) {
	$welcome = getServerPref("webwelcome");
	$invite = $guest ? "" : ("\n" . getServerPref("webwelcomediscord") . "\n");
	$welcome = str_replace('$INVITE', $invite, $welcome);
	$welcome .= "\n\n" . getQOTDText($mod);
	$welcome = str_replace("\n", "\\n", $welcome);

	return $welcome;
}

function getQOTDText($mod = false) {
	$qotd = "";
	$query = pdo_prepare("SELECT * FROM `qotd` WHERE `selected` = 1");
	$result = $query->execute();

	if ($result->rowCount() == 1) {
		$qotd = "Leaderboards' Quote of the Day: ";
	} else if ($result->rowCount() > 1) {
		$qotd = "Leaderboards' Quotes of the Day:";
	} else {
		return "";
	}

	while (($row = $result->fetch()) !== false) {
		$text = $row["text"];
		$user = getDisplayName($row["username"]);
		$time = $row["timestamp"];

		$dt = new DateTime($time);
		$year = $dt->format("Y");

		$qotd .= "\n\"$text\" -$user $year";

		if ($mod) {
			$now = new DateTime();
			$diff = $now->diff($dt);
			if ($diff->days > 1) {
				$qotd .= " [No longer today's quote, update this ya dummy]";
			}
		}
	}
	return $qotd;
}
