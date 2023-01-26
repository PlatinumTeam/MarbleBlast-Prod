<?php
/**
 * Gross compatibility scripts for access to the platinum database
 */
namespace Platinum {
	/**
	 * Returns a the server setting/preference for the specified key
	 * @var string $key The server setting/preference to get
	 * @return string The server setting/preference for the given key, or "" is no
	 * preference was found for that key.
	 */
	function getServerPref($key) {
		global $pdb;
		$query = $pdb->prepare("SELECT `value` FROM `settings` WHERE `key` = :key");
		$query->bindValue(":key", $key);
		requireExecute($query);

		// If it doesn't exist, return nothing
		if (!$query->rowCount())
			return "";

		// Otherwise, this is simple
		return $query->fetchColumn(0);
	}

	/**
	 * Returns the server time
	 * @return float The SIG code (see sig.php) for the login attempt
	 */
	function getServerTime() {
		return (float)round((float)time() - floatval(getServerPref("servertime")), 1);
	}

	/**
	 * Post a notification to `notify`
	 * @var string $type The type of notification
	 * @var string $user The username that the notification pertains to
	 * @var int $access The access required to see the notification
	 * @var string $message Optional message to include with the notification
	 */
	function postNotify($type, $user, $access = 0, $message = "") {
		global $pdb;
		// Quick hack to make sure it's a number
		$access += 0;
		$time = getServerTime();
		$query = $pdb->prepare("INSERT INTO `notify` (`username`, `type`, `message`, `access`, `time`) VALUES (:user, :type, :message, :access, :time)");
		$query->bindValue(":user", $user);
		$query->bindValue(":type", $type);
		$query->bindValue(":message", $message);
		$query->bindValue(":access", $access);
		$query->bindValue(":time", $time);
		requireExecute($query);
	}

	/**
	 * Encodes a name so torque can parse it
	 * @var string $name The username to encode
	 * @return string The username with all spaces escaped to "-SPC-"
	 */

	function escapeName($name) {
		$name = str_replace(" ", "-SPC-", $name);
		$name = str_replace("\t", "-TAB-", $name);
		$name = str_replace("\n", "-NL-", $name);
		$name = mb_convert_encoding($name, "ASCII");
		return $name;
	}

	/**
	 * Synonym form escapeName
	 * @var string $name The username to encode
	 * @return string The username with all spaces escaped to "-SPC-"
	 */

	function encodeName($name) {
		$name = str_replace(" ", "-SPC-", $name);
		$name = str_replace("\t", "-TAB-", $name);
		$name = str_replace("\n", "-NL-", $name);
		$name = mb_convert_encoding($name, "ASCII");
		return $name;
	}

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
		global $pdb;
		$query = $pdb->prepare("SELECT * FROM `qotd` WHERE `selected` = 1");
		$query->execute();

		if ($query->rowCount() == 1) {
			$qotd = "Leaderboards' Quote of the Day: ";
		} else if ($query->rowCount() > 1) {
			$qotd = "Leaderboards' Quotes of the Day:";
		} else {
			return "";
		}

		while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
			$username = $row["username"];
			$user = \User::get(\JoomlaSupport::getUserId($username));

			$text = $row["text"];
			$time = $row["timestamp"];

			$dt = new \DateTime($time);
			$year = $dt->format("Y");

			$qotd .= "\n\"$text\" -{$user->getDisplayName()} $year";

			if ($mod) {
				$now = new \DateTime();
				$diff = $now->diff($dt);
				if ($diff->days > 1) {
					$qotd .= " [No longer today's quote, update this ya dummy]";
				}
			}
		}
		return $qotd;
	}

	function checkKey($username, $key) {
		global $pdb;
		//Query the database for their key
		$query = $pdb->prepare("SELECT `chatKey` FROM users WHERE username = :username");
		$query->bindValue(":username", $username);
		$query->execute();

		$chatKey = $query->fetchColumn(0);

		return $chatKey !== null && $chatKey === $key;

	}

}