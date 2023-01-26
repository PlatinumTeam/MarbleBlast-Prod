<?php

/**
 * @property int id
 * @property string basename
 * @property int game_id
 * @property int difficulty_id
 * @property string name
 * @property string gamemode
 * @property int sort_index
 * @property bool custom
 * @property array missionInfo
 * @property array ratingInfo
 * @property array difficultyInfo
 * @property array gameInfo
 */
class Mission {
	protected $_id;
	protected $fields;

	protected function __construct($id) {
		
		$this->_id = $id;
		$this->fields = [];
	}

	/**
	 * Get any field
	 * @param string $field Name of the desired field
	 * @return mixed Its value
	 */
	public function __get($field) {
		if ($field === "id")
			return $this->_id;
		if ($field === "custom")
			return $this->fields["is_custom"];
		return $this->fields[$field];
	}

	public function update() {
		global $db;
		$query = $db->prepare("
			SELECT * FROM
				(SELECT * FROM `ex82r_missions` AS `missionInfo` WHERE `id` = :id) AS `info`
			JOIN `ex82r_mission_rating_info` AS `ratingInfo`
			  ON `info`.`id` = `ratingInfo`.`mission_id`
			JOIN `ex82r_mission_difficulties` AS `difficultyInfo`
			  ON `info`.`difficulty_id` = `difficultyInfo`.`id`
			JOIN `ex82r_mission_games` AS `gameInfo`
			  ON `info`.`game_id` = `gameInfo`.`id`"
		);
		$query->bindValue(":id", $this->id);
		$query->execute();
		$result = fetchTableAssociative($query);
		$this->fields = $result["missionInfo"];

		$this->fields["missionInfo"] = $result["missionInfo"];
		$this->fields["ratingInfo"] = $result["ratingInfo"];
		$this->fields["difficultyInfo"] = $result["difficultyInfo"];
		$this->fields["gameInfo"] = $result["gameInfo"];
	}

	/**
	 * Get the top scores (one per player) on this mission
	 * @param int $modifiers Any modifiers for the scores
	 * @param int $count Maximum number of scores to return
	 * @return array The scores
	 */
	public function getTopScores($modifiers = 0, $count = 10000) {
		global $db;

		$count = (int)$count;

		//Get all times
		$query = $db->prepare("
			SELECT ex82r_user_scores.id, `uniques`.`user_id`, `username`, SANITIZE_NAME(`name`) AS `name`, `score`, `score_type`, `modifiers`, `total_bonus`, `gem_count`, `rating`, `gems_1_point`, `gems_2_point`, `gems_5_point`, `gems_10_point`, `origin`, `timestamp` FROM
			  -- Get min of id of the scores so it doesn't show duplicates
			(SELECT `bests`.`user_id`, MIN(ex82r_user_scores.id) AS id FROM
			  -- Best for each user
			  (SELECT `user_id`, MIN(`sort`) AS `minSort`
			   FROM ex82r_user_scores
			   JOIN prod_joomla.bv2xj_users ON ex82r_user_scores.user_id = bv2xj_users.id
			   WHERE `mission_id` = :id
		         AND `modifiers` & :modifiers = :modifiers2
		         AND `block` = 0
			   GROUP BY `user_id`
			  ) AS `bests`
			  -- Combine with the scores table to get the rest of the info
			  JOIN ex82r_user_scores
			    ON ex82r_user_scores.`user_id` = `bests`.`user_id`
			   AND ex82r_user_scores.`sort` = `bests`.`minSort`
			  WHERE `mission_id` = :id2
			  GROUP BY `bests`.`user_id`, `score`, `score_type`
			) AS `uniques`
			-- Join back with scores for extra info
			JOIN ex82r_user_scores ON uniques.id = ex82r_user_scores.id
			-- And get some user info
			JOIN `prod_joomla`.`bv2xj_users`
			  ON `uniques`.`user_id` = `prod_joomla`.`bv2xj_users`.`id`
			ORDER BY `sort` ASC, ex82r_user_scores.id ASC
			LIMIT $count
		");
		$query->bindValue(":id", $this->id);
		$query->bindValue(":id2", $this->id);
		$query->bindValue(":modifiers", $modifiers);
		$query->bindValue(":modifiers2", $modifiers);
		$query->execute();

		//Get all the results
		$result = $query->fetchAll(PDO::FETCH_ASSOC);

		for ($i = 0; $i < count($result); $i ++) {
			if ($i > 0 && $result[$i]["score"] === $result[$i - 1]["score"]) {
				$result[$i]["placement"] = $result[$i - 1]["placement"];
			} else {
				$result[$i]["placement"] = $i + 1;
			}
		}

		return $result;
	}

	/**
	 * Get the placement on the leader boards that a given time will earn you
	 * @param array $scoreInfo Information about the score
	 * @return int How many scores are better than the input score
	 *                         (0 == this score would be a world record)
	 */
	public function getScorePlacement($scoreInfo) {
		global $db;

		$sort = getScoreSorting($scoreInfo);

		//Get all times
		$query = $db->prepare("
			SELECT COUNT(*) FROM
			-- Best for each user
			  (SELECT `user_id`, MIN(`sort`) AS `minSort`
			      FROM ex82r_user_scores
			      JOIN prod_joomla.bv2xj_users ON ex82r_user_scores.user_id = bv2xj_users.id
			      WHERE `mission_id` = :id
			      AND block = 0
			      GROUP BY `user_id`
			  ) AS `bests`
			WHERE `bests`.`minSort` < :sort
		");


		$query->bindValue(":id", $this->id);
		$query->bindValue(":sort", $sort);
		$query->execute();

		//Merge the two into one big happy array
		return $query->fetchColumn(0);
	}

	/**
	 * Get if a score would beat the world record
	 * @param array $scoreInfo Information about the score
	 * @return bool If the score is better than the world record
	 */
	public function getScoreBeatsRecord($scoreInfo) {
		global $db;

		$sort = getScoreSorting($scoreInfo);

		//Get all times
		$query = $db->prepare("
			SELECT COUNT(*) FROM
			-- Best for each user
			  (SELECT `user_id`, MIN(`sort`) AS `minSort`
			      FROM ex82r_user_scores
			      JOIN prod_joomla.bv2xj_users ON ex82r_user_scores.user_id = bv2xj_users.id
			      WHERE `mission_id` = :id
			      AND block = 0
			      GROUP BY `user_id`
			  ) AS `bests`
			WHERE `bests`.`minSort` <= :sort
		");


		$query->bindValue(":id", $this->id);
		$query->bindValue(":sort", $sort);
		$query->execute();

		//Merge the two into one big happy array
		return $query->fetchColumn(0) === 0;
	}

	public function isDisabled() {
		return $this->ratingInfo["disabled"] === 1
			|| $this->gameInfo["disabled"] === 1
			|| $this->difficultyInfo["disabled"] === 1;
	}

	//-------------------------------------------------------------------------

	/**
	 * Get a Mission object by its id
	 * @param int $id The mission's id
	 * @return Mission|null
	 */
	public static function getById($id) {
		global $db;
		$query = $db->prepare("
			SELECT `ex82r_missions`.`id` FROM `ex82r_missions`
			WHERE `ex82r_missions`.`id` = :id
		");
		$query->bindValue(":id", $id);
		$query->execute();
		if ($query->rowCount() === 0) {
			return null;
		}

		$mission = new Mission($id);
		$mission->update();

		if ($mission->isDisabled() && !Login::isPrivilege("pq.test.missionList")) {
			return null;
		}

		return $mission;
	}

	/**
	 * Get a Mission object by its basename
	 * @param string $basename The mission's basename
	 * @return Mission|null
	 */
	public static function getByBasename($basename, $multiplayer = false) {
		global $db;
		$query = $db->prepare("
			SELECT `ex82r_missions`.`id` FROM `ex82r_missions`
			JOIN `ex82r_mission_games` ON `ex82r_missions`.`game_id` = `ex82r_mission_games`.`id`
			WHERE `ex82r_missions`.`basename` = :name
		    AND `ex82r_mission_games`.`game_type` = :type
		");
		$query->bindValue(":name", $basename);
		$query->bindValue(":type", ($multiplayer ? "Multiplayer" : "Single Player"));
		$query->execute();

		if ($query->rowCount()) {
			$id = $query->fetchColumn(0);
			$mission = new Mission($id);
			$mission->update();

			return $mission;
		} else {
			return null;
		}
	}

	public static function getCustomByParams($allowCreateCustom = true) {
		global $db;

		//See if we can find it by hash
		$hash = requireParam("missionHash");
		$query = $db->prepare("
			SELECT `ex82r_missions`.`id` FROM `ex82r_missions`
			WHERE `hash` = :hash
		");
		$query->bindValue(":hash", $hash);
		$query->execute();
		if ($query->rowCount()) {
			$id = $query->fetchColumn(0);
			$mission = new Mission($id);
			$mission->update();

			if ($mission->custom) {
				return $mission;
			}
		}
		//Maybe it's by file?
		$file = requireParam("missionFile");
		$query = $db->prepare("
			SELECT `ex82r_missions`.`id` FROM `ex82r_missions`
			WHERE `file` = :file
		");
		$query->bindValue(":file", $file);
		$query->execute();
		if ($query->rowCount()) {
			$id = $query->fetchColumn(0);
			$mission = new Mission($id);
			$mission->update();

			if ($mission->custom) {
				return $mission;
			}
		}
		//Nope, doesn't exist.
		if (!$allowCreateCustom) {
			//Can't find it
			return null;
		}

		//Better create it.

		//Some params
		$name = requireParam("missionName");
		$difficultyId = requireParam("difficultyId");
		$gamemode = requireParam("missionGamemode");

		//See if this difficulty supports local missions
		$query = $db->prepare("
			SELECT `is_local`, `game_id` FROM `ex82r_mission_difficulties` WHERE `id` = :id
		");
		$query->bindValue(":id", $difficultyId);
		$query->execute();

		//Make sure we can actually add this
		if (!$query->rowCount()) {
			error("ARGUMENT difficultyId invalid");
		}
		$row = $query->fetch(PDO::FETCH_ASSOC);
		if ($row["is_local"] === 0) {
			error("ARGUMENT difficulty does not support custom");
		}

		$query = $db->prepare("
			INSERT INTO `ex82r_missions` SET 
			`game_id` = :game_id,
			`difficulty_id` = :difficulty_id,
			`file` = :file,
			`basename` = :basename,
			`name` = :name,
			`gamemode` = :gamemode,
			`is_custom` = 1,
			`hash` = :hash
		");
		$query->bindValue(":game_id", $row["game_id"]);
		$query->bindValue(":difficulty_id", $difficultyId);
		$query->bindValue(":file", $file);
		$query->bindValue(":basename", pathinfo($file, PATHINFO_BASENAME));
		$query->bindValue(":name", $name);
		$query->bindValue(":gamemode", $gamemode);
		$query->bindValue(":hash", $hash);
		$query->execute();
		$id = $db->lastInsertId();

		$query = $db->prepare("
			INSERT INTO `ex82r_mission_rating_info` SET
			`mission_id` = :id
		");
		$query->bindValue(":id", $id);
		$query->execute();

		$mission = Mission::getById($id);
		return $mission;
	}

	/**
	 * Get a Mission object using the POST parameters
	 */
	public static function getByParams($allowCustom = false, $allowCreateCustom = false) {
		$missionId = param("missionId");

		//If they try to use a basename instead
		if ($missionId === null) {
			if ($allowCustom === false) {
				error("ARGUMENT missionId");
			}

			//Maybe we need to find it
			return Mission::getCustomByParams($allowCreateCustom);
		} else {
			return Mission::getById($missionId);
		}
	}
}