<?php

/**
 * @property int id
 * @property array ratings
 * @property array joomla
 * @property array kunena
 * @property array leaderboards
 * @property array achievements
 * @property array streaks
 * @property array titles
 */
class User {
	/** @var int $id */
	protected $_id;
	/** @var array $ratings */
	protected $ratings;
	/** @var array $joomlaFields */
	protected $joomlaFields;
	/** @var array $kunenaFields */
	protected $kunenaFields;
	/** @var array $leaderboardsFields */
	protected $leaderboardsFields;
	/** @var array $achievements */
	protected $achievements;
	/** @var array $streaks */
	protected $streaks;
	/** @var array $titles */
	protected $titles;

	function __construct($userId) {
		$this->_id = (int)$userId;
		$this->ratings = [];
	}

	public function __get($field) {
		switch ($field) {
		case "id": return $this->_id;
		case "ratings": return $this->ratings;
		case "joomla": return $this->joomlaFields;
		case "kunena": return $this->kunenaFields;
		case "leaderboards": return $this->leaderboardsFields;
		case "achievements": return $this->achievements;
		case "streaks": return $this->streaks;
		case "titles": return $this->titles;
		}
		return null;
	}

	public function getUsername() {
		return JoomlaSupport::getUsername($this->id);
	}

	public function getDisplayName() {
		return JoomlaSupport::getDisplayName($this->id);
	}

	public function isGuest() {
		return $this->leaderboards["access"] === 3;
	}

	public function update() {
		global $db, $pdb;

		if ($this->id === 0) {
			return;
		}

		$query = $db->prepare("SELECT * FROM `ex82r_user_ratings` WHERE `user_id` = :user_id");
		$query->bindValue(":user_id", $this->id);
		$query->execute();

		if ($query->rowCount()) {
			$this->ratings = $query->fetch(PDO::FETCH_ASSOC);
		} else {
			$query = $db->prepare("INSERT INTO `ex82r_user_ratings` SET `user_id` = :user_id");
			$query->bindValue(":user_id", $this->id);

			if ($query->execute()) {
				$this->update();
			}
		}

		$query = $db->prepare("SELECT `achievement_id` FROM `ex82r_user_achievements` WHERE `user_id` = :user_id");
		$query->bindValue(":user_id", $this->id);
		$query->execute();
		$this->achievements = $query->fetchAll(PDO::FETCH_COLUMN);

		$jdb = new Database("joomla", []);
		$query = $jdb->prepare("SELECT * FROM `bv2xj_users` WHERE `id` = :id");
		$query->bindValue(":id", $this->id);
		$query->execute();
		$this->joomlaFields = $query->fetch(PDO::FETCH_ASSOC);

		$query = $jdb->prepare("SELECT * FROM `bv2xj_kunena_users` WHERE `userid` = :id");
		$query->bindValue(":id", $this->id);
		$query->execute();
		$this->kunenaFields = $query->fetch(PDO::FETCH_ASSOC);

		$query = $pdb->prepare("SELECT * FROM `users` WHERE `username` = :username");
		$query->bindValue(":username", $this->joomlaFields["username"]);
		$query->execute();
		$this->leaderboardsFields = $query->fetch(PDO::FETCH_ASSOC);

		$query = $db->prepare("SELECT * FROM `ex82r_user_streaks` WHERE `user_id` = :id");
		$query->bindValue(":id", $this->id);
		$query->execute();
		$this->streaks = $query->fetch(PDO::FETCH_ASSOC);

		$query = $jdb->prepare("
			SELECT
				IF(f.id = 0, NULL, f.title) AS flair,
				IF(s.id = 0, NULL, s.display_name) AS suffix,
				IF(p.id = 0, NULL, p.display_name) AS prefix
			FROM bv2xj_users
			  JOIN bv2xj_user_titles AS f ON f.id = bv2xj_users.titleFlair
			  JOIN bv2xj_user_titles AS s ON s.id = bv2xj_users.titleSuffix
			  JOIN bv2xj_user_titles AS p ON p.id = bv2xj_users.titlePrefix
			WHERE bv2xj_users.id = :id
		");
		$query->bindValue(":id", $this->id);
		$query->execute();
		$this->titles = $query->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Get the amount of rating from a particular game/source
	 * @param string $source The source for the rating
	 * @return int The rating from that source
	 */
	public function getRating($source) {
		return $this->ratings[$source];
	}

	/**
	 * Get the top scores for a user on a mission
	 * @param Mission $mission Which mission
	 * @param int $count How many scores to get
	 * @param int $modifiers If any modifiers should be used
	 * @return array
	 */
	public function getBestScores(Mission $mission, $count = 1, $modifiers = 0) {
		global $db;

		//So this isn't injectable
		$count = (int)$count;

		$query = $db->prepare("
			SELECT * FROM ex82r_user_scores
			WHERE `user_id` = :user_id
			  AND `mission_id` = :mission_id
			  AND (`modifiers` & :modifiers) = :modifiers2
			ORDER BY `sort` ASC
			LIMIT $count
		");

		$missionId = $mission->id;
		$query->bindValue(":user_id", $this->id);
		$query->bindValue(":mission_id", $missionId);
		$query->bindValue(":modifiers", $modifiers);
		$query->bindValue(":modifiers2", $modifiers);
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get how many scores a user has for a particular set of modifiers. Faster than
	 * doing count(getBestScores())
	 * @param Mission $mission
	 * @param int $modifiers
	 * @return int How many scores they have
	 */
	public function getScoreCount($mission, $modifiers = 0) {
		global $db;

		$query = $db->prepare("
			SELECT COUNT(*) FROM ex82r_user_scores
			WHERE `user_id` = :user_id
			  AND `mission_id` = :mission_id
			  AND (`modifiers` & :modifiers) = :modifiers2
		");

		$missionId = $mission->id;
		$query->bindValue(":user_id", $this->id);
		$query->bindValue(":mission_id", $missionId);
		$query->bindValue(":modifiers", $modifiers);
		$query->bindValue(":modifiers2", $modifiers);
		$query->execute();

		return $query->fetchColumn(0);
	}

	/**
	 * Award this user a specific title and set it as active on their profile
	 * @param int $titleId
	 */
	public function awardTitle($titleId) {
		$jdb = JoomlaSupport::db();

		//Make sure they don't have it first!
		$query = $jdb->prepare("SELECT * FROM `bv2xj_user_titles_earned` WHERE `userid` = :uid AND `titleid` = :titleid");
		$query->bindValue(":uid", $this->id);
		$query->bindValue(":titleid", $titleId);
		$query->execute();

		if ($query->rowCount()) {
			return;
		}

		//Make sure the title exists before giving it to them
		$query = $jdb->prepare("SELECT `position` FROM `bv2xj_user_titles` WHERE `id` = :titleid");
		$query->bindValue(":titleid", $titleId);
		$query->execute();

		if (!$query->rowCount()) {
			return;
		}

		$position = $query->fetchColumn(0);
		$column = "";
		if      ($position == 0) $column = "titleFlair";
		else if ($position == 1) $column = "titlePrefix";
		else if ($position == 2) $column = "titleSuffix";

		//Actually give it to them
		$query = $jdb->prepare("INSERT INTO `bv2xj_user_titles_earned` SET `userid` = :uid, `titleid` = :titleid");
		$query->bindValue(":uid", $this->id);
		$query->bindValue(":titleid", $titleId);
		$query->execute();

		//And set it as their title, because why not
		if ($column != "") {
			$query = $jdb->prepare("UPDATE `bv2xj_users` SET `$column` = :titleid WHERE `id` = :uid");
			$query->bindValue(":titleid", $titleId);
			$query->bindValue(":uid", $this->id);
			$query->execute();
		}
	}

	/**
	 * @return bool If the user can have a colored username
	 */
	public function getHasColor() {
		$query = JoomlaSupport::db()->prepare("SELECT `hasColor` FROM bv2xj_users WHERE `id` = :uid");
		$query->bindValue(":uid", $this->id);
		$query->execute();

		if (!$query->rowCount())
			return false;

		return $query->fetchColumn(0);
	}

	/**
	 * Allow this user to have a colored username
	 */
	public function awardColor() {
		//Pick a random color
		$color = rand(0, 0xFFFFFF);
		$color = sprintf("%06x", $color);
		//Give it to them

		$query = JoomlaSupport::db()->prepare("UPDATE bv2xj_users SET `hasColor` = 1, `colorValue` = :color WHERE `id` = :uid");
		$query->bindValue(":uid", $this->id);
		$query->bindValue(":color", $color);
		$query->execute();
	}

	/**
	 * Get a user's info
	 * @param int $userId The user's id
	 * @return User The user object
	 */
	public static function get($userId): User {
		$user = new User($userId);
		$user->update();
		return $user;
	}
};
