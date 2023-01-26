<?php

class Login {
	private static $cliUsername = "higuy";
	private static function isCLI() {
		return php_sapi_name() == "cli";
	}

	public static function getCurrentUsername() {
		if (self::isCLI()) {
			return self::$cliUsername;
		}
		$jUsername = JoomlaSupport::getCurrentUsername();
		if ($jUsername !== null) {
			return $jUsername;
		}
		return param("username");
	}

	public static function getCurrentUserId() {
		return JoomlaSupport::getUserId(self::getCurrentUsername());
	}

	public static function getCurrentUser() {
		return User::get(JoomlaSupport::getUserId(self::getCurrentUsername()));
	}

	public static function isLoggedIn() {
		if (self::isCLI()) {
			return true;
		}

		//Try cookie login
		if (JoomlaSupport::checkCookieLogin()) {
			return true;
		}

		if (param("username") === null) {
			return false;
		}
		$username = requireParam("username");
		if (param("password") === null) {
			//No password, maybe it's a key login?

			if (param("key") === null) {
				//No password, no key, no login
				return false;
			}

			//See if this matches
			$key = requireParam("key");
			return JoomlaSupport::checkLoginKey($username, $key);
		}

		$password = self::deGarbledeguck(requireParam("password"));

		//Just let Joomla get this for us
		return JoomlaSupport::checkLogin($username, $password);
	}

	private static $privMap = [
		"pq.admin.updateMissions" => 2,
		"pq.admin.bannedOverride" => 1,
		"pq.test.missionList" => 1,
		"pq.test.marbleList" => 2,
		"pq.mod.extendedScores" => 1,
		"pq.mod.editRatings" => 1,
		"pq.mod.chat" => 1,
		"pq.chat.formatting" => 1,
		"pq.test.frightfest" => 1,
		"pq.test.winterfest" => 1,
		"pq.test.debugLogging" => 2,
	];

	private static $userMap = [
		786 => [
			"pq.test.missionList",
			"pq.mod.extendedScores",
			"pq.mod.editRatings",
		]
	];

	public static function getUserPrivilege($user) {
		if ($user->leaderboards["access"] == 3) {
			//Guest!
			$priv = 0;
		} else {
			$priv = $user->leaderboards["access"];
		}
		return $priv;
	}

	public static function isPrivilege($action) {
		//CLI is root after all
		if (self::isCLI()) {
			return true;
		}
		//Make sure we're logged in
		if (!self::isLoggedIn()) {
			$priv = -1;
		} else {
			//TODO: Joomla permissions
			$user = self::getCurrentUser();
			return self::isUserPrivilege($user, $action);
		}
		//Check if they have more than the needed priv level
		return $priv >= (self::$privMap[$action] ?? 0);
	}

	public static function isUserPrivilege($user, $action) {
		$priv = self::getUserPrivilege($user);

		//Hardcoded because lazy
		if (array_key_exists($user->id, self::$userMap)) {
			if (in_array($action, self::$userMap[$user->id])) {
				return true;
			}
		}
		//Check if they have more than the needed priv level
		return $priv >= (self::$privMap[$action] ?? 0);
	}

	public static function requireLogin() {
		if (!self::isLoggedIn()) {
			error("FAILURE NEEDLOGIN");
		}
	}

	public static function requirePrivilege($action) {
		if (!self::isPrivilege($action)) {
			error("FAILURE NEEDPRIVILEGE");
		}
	}

	/**
	 * Decodes the garbledeguck() method in MBP
	 * @version 0.1
	 * @package leader
	 * @access public
	 * @var string $string The string to decode
	 * @return string The decoded value of $string, using the de-garbledeguck method
	 */
	private static function deGarbledeguck($string) {
		/*
		// Weak "encrypts" a string so it can't be seen in clear-text
		function garbledeguck(%string) {
			%finish = "";
			for (%i = 0; %i < strlen(%string); %i ++) {
				%char = getSubStr(%string, %i, 1);
				%val = chrValue(%char);
				%val = 128 - %val;
				%hex = dec2hex(%val);
				%finish = %hex @ %finish; //Why not?
			}
			return %finish;
		}
		*/
		if (substr($string, 0, 3) !== "gdg")
			return $string;
		$finish = "";
		for ($i = 3; $i < strlen($string); $i += 2) {
			$hex = substr($string, $i, 2);
			$val = hexdec($hex);
			$char = chr(128 - $val);
			$finish = $char . $finish;
		}
		return $finish;
	}

}
