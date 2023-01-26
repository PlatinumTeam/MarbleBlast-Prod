<?php

define("PQ_RUN", true);
require_once("../Framework.php");

Login::requirePrivilege("pq.admin.updateMissions");

function findGame($name) {
	global $db;

	$query = $db->prepare("SELECT `id` FROM `ex82r_mission_games` WHERE `name` = :name");
	$query->bindValue(":name", $name);
	$query->execute();
	if ($query->rowCount()) {
		return $query->fetchColumn(0);
	} else {
		return null;
	}
}

function findDifficulty($game_id, $name) {
	global $db;

	$query = $db->prepare("SELECT `id` FROM `ex82r_mission_difficulties` WHERE `name` = :name AND `game_id` = :game");
	$query->bindValue(":name", $name);
	$query->bindValue(":game", $game_id);
	$query->execute();
	if ($query->rowCount()) {
		return $query->fetchColumn(0);
	} else {
		return null;
	}
}

function createMission(&$mission) {
	global $db;

	$query = $db->prepare("INSERT INTO ex82r_missions SET game_id = :game, difficulty_id = :difficulty");
	$query->bindValue(":game", $mission["game_id"]);
	$query->bindValue(":difficulty", $mission["difficulty_id"]);
	requireExecute($query);
	$mission["id"] = $db->lastInsertId();

	$query = $db->prepare("INSERT INTO ex82r_mission_rating_info SET mission_id = :id");
	$query->bindValue(":id", $mission["id"]);
	requireExecute($query);
}

$template = [
	"id" => null,
	"game_id" => null,
	"difficulty_id" => null,
	"basename" => null,
	"file" => null,
	"name" => null,
	"hash" => null,
	"game" => null,
	"modification" => null,
	"difficulty" => null,
	"gamemode" => null,
	"sort_index" => null,
	"is_custom" => true,

	"rating_info" => [
		"has_egg" => 0,
		"par_time" => 0,
		"platinum_time" => 0,
		"ultimate_time" => 0,
		"awesome_time" => 0,
		"par_score" => 0,
		"platinum_score" => 0,
		"ultimate_score" => 0,
		"awesome_score" => 0,
		"versus_par_score" => 0,
		"versus_platinum_score" => 0,
		"versus_ultimate_score" => 0,
		"versus_awesome_score" => 0,
		"gem_count" => 0,
		"gem_count_1" => 0,
		"gem_count_2" => 0,
		"gem_count_5" => 0,
		"gem_count_10" => 0,
		"hunt_max_score" => 0,

		"completion_bonus" => 0,
		"set_base_score" => 0,
		"multiplier_set_base" => 0,
		"platinum_bonus" => 0,
		"ultimate_bonus" => 0,
		"awesome_bonus" => 0,
		"standardiser" => 0,
		"time_offset" => 100,
		"difficulty" => 1,
		"platinum_difficulty" => 1,
		"ultimate_difficulty" => 1,
		"awesome_difficulty" => 1,
		"hunt_multiplier" => 0,
		"hunt_divisor" => 0,
		"hunt_completion_bonus" => 1,
		"hunt_par_bonus" => 0,
		"hunt_platinum_bonus" => 0,
		"hunt_ultimate_bonus" => 0,
		"hunt_awesome_bonus" => 0,
		"quota_100_bonus" => 0,
		"egg_rating" => 0,

		"disabled" => 1,
		"notes" => "",
	]
];

// What comes out of the game
$params = "{\"basename\":\"SuperSecretPuzzle12\",\"file\":\"platinum/data/lbmissions_pq/bonus/SuperSecretPuzzle12.mcs\",\"name\":\"Super+Secret+Puzzle+12\",\"hash\":\"2EFE1798B032A92A1FB2C0A8389F83B6E0DED57E1F27D0824FA49FF67C364BD6\",\"game\":\"PlatinumQuest\",\"modification\":\"PlatinumQuest\",\"difficulty\":\"Bonus\",\"gamemode\":\"null\",\"sort_index\":\"9001\",\"rating_info\":{\"has_egg\":false,\"platinum_time\":480000,\"ultimate_time\":300000,\"awesome_time\":70000,\"platinum_score\":0,\"ultimate_score\":0,\"awesome_score\":0,\"gem_count\":0,\"gem_count_1\":0,\"gem_count_2\":0,\"gem_count_5\":0,\"gem_count_10\":0,\"hunt_max_score\":0}}";
$params = json_decode(urldecode($params), true);

$mission = $template;
foreach ($params as $key => $value) {
	if ($key === "rating_info") {
		foreach ($value as $rkey => $rvalue) {
			$mission["rating_info"][$rkey] = $rvalue;
		}
	} else {
		$mission[$key] = $value;
	}
}

$db->beginTransaction();

if ($mission["id"] === null) {
	$mission["game_id"] = findGame($mission["game"]);
	$mission["difficulty_id"] = findDifficulty($mission["game_id"], $mission["difficulty"]);
	createMission($mission);

	$mission["is_custom"] = false;
}

print_r($mission);

// Update info
$query = $db->prepare("
	UPDATE ex82r_missions SET
	game_id = :game_id,
	difficulty_id = :difficulty_id,
	name = :name,
	file = :file,
	modification = :modification,
	basename = :basename,
	gamemode = :gamemode,
	sort_index = :sort_index,
	hash = :hash
	WHERE id = :id
");

$query->bindValue(":game_id", $mission["game_id"]);
$query->bindValue(":difficulty_id", $mission["difficulty_id"]);
$query->bindValue(":name", $mission["name"]);
$query->bindValue(":file", $mission["file"]);
$query->bindValue(":modification", $mission["modification"]);
$query->bindValue(":basename", $mission["basename"]);
$query->bindValue(":gamemode", $mission["gamemode"]);
$query->bindValue(":sort_index", $mission["sort_index"]);
$query->bindValue(":hash", $mission["hash"]);
$query->bindValue(":id", $mission["id"]);
requireExecute($query);

// Update rating info
$oldInfo = Mission::getById($mission["id"]);
$query = $db->prepare("
	UPDATE ex82r_mission_rating_info SET
	has_egg = :has_egg,
	par_time = :par_time,
	platinum_time = :platinum_time,
	ultimate_time = :ultimate_time,
	awesome_time = :awesome_time,
	par_score = :par_score,
	platinum_score = :platinum_score,
	ultimate_score = :ultimate_score,
	awesome_score = :awesome_score,
	versus_par_score = :versus_par_score,
	versus_platinum_score = :versus_platinum_score,
	versus_ultimate_score = :versus_ultimate_score,
	versus_awesome_score = :versus_awesome_score,
	gem_count = :gem_count,
	gem_count_1 = :gem_count_1,
	gem_count_2 = :gem_count_2,
	gem_count_5 = :gem_count_5,
	gem_count_10 = :gem_count_10,
	hunt_max_score = :hunt_max_score,
	completion_bonus = :completion_bonus,
	set_base_score = :set_base_score,
	multiplier_set_base = :multiplier_set_base,
	platinum_bonus = :platinum_bonus,
	ultimate_bonus = :ultimate_bonus,
	awesome_bonus = :awesome_bonus,
	standardiser = :standardiser,
	time_offset = :time_offset,
	difficulty = :difficulty,
	platinum_difficulty = :platinum_difficulty,
	ultimate_difficulty = :ultimate_difficulty,
	awesome_difficulty = :awesome_difficulty,
	hunt_multiplier = :hunt_multiplier,
	hunt_divisor = :hunt_divisor,
	hunt_completion_bonus = :hunt_completion_bonus,
	hunt_par_bonus = :hunt_par_bonus,
	hunt_platinum_bonus = :hunt_platinum_bonus,
	hunt_ultimate_bonus = :hunt_ultimate_bonus,
	hunt_awesome_bonus = :hunt_awesome_bonus,
	quota_100_bonus = :quota_100_bonus,
	egg_rating = :egg_rating,
	disabled = :disabled,
	notes = :notes
	WHERE mission_id = :mission_id
");
$query->bindValue(":has_egg",               $mission["rating_info"]["has_egg"]);
$query->bindValue(":par_time",              $mission["rating_info"]["par_time"]);
$query->bindValue(":platinum_time",         $mission["rating_info"]["platinum_time"]);
$query->bindValue(":ultimate_time",         $mission["rating_info"]["ultimate_time"]);
$query->bindValue(":awesome_time",          $mission["rating_info"]["awesome_time"]);
$query->bindValue(":par_score",             $mission["rating_info"]["par_score"]);
$query->bindValue(":platinum_score",        $mission["rating_info"]["platinum_score"]);
$query->bindValue(":ultimate_score",        $mission["rating_info"]["ultimate_score"]);
$query->bindValue(":awesome_score",         $mission["rating_info"]["awesome_score"]);
$query->bindValue(":versus_par_score",      $mission["rating_info"]["versus_par_score"]);
$query->bindValue(":versus_platinum_score", $mission["rating_info"]["versus_platinum_score"]);
$query->bindValue(":versus_ultimate_score", $mission["rating_info"]["versus_ultimate_score"]);
$query->bindValue(":versus_awesome_score",  $mission["rating_info"]["versus_awesome_score"]);
$query->bindValue(":gem_count",             $mission["rating_info"]["gem_count"]);
$query->bindValue(":gem_count_1",           $mission["rating_info"]["gem_count_1"]);
$query->bindValue(":gem_count_2",           $mission["rating_info"]["gem_count_2"]);
$query->bindValue(":gem_count_5",           $mission["rating_info"]["gem_count_5"]);
$query->bindValue(":gem_count_10",          $mission["rating_info"]["gem_count_10"]);
$query->bindValue(":hunt_max_score",        $mission["rating_info"]["hunt_max_score"]);
$query->bindValue(":completion_bonus",      $mission["rating_info"]["completion_bonus"]);
$query->bindValue(":set_base_score",        $mission["rating_info"]["set_base_score"]);
$query->bindValue(":multiplier_set_base",   $mission["rating_info"]["multiplier_set_base"]);
$query->bindValue(":platinum_bonus",        $mission["rating_info"]["platinum_bonus"]);
$query->bindValue(":ultimate_bonus",        $mission["rating_info"]["ultimate_bonus"]);
$query->bindValue(":awesome_bonus",         $mission["rating_info"]["awesome_bonus"]);
$query->bindValue(":standardiser",          $mission["rating_info"]["standardiser"]);
$query->bindValue(":time_offset",           $mission["rating_info"]["time_offset"]);
$query->bindValue(":difficulty",            $mission["rating_info"]["difficulty"]);
$query->bindValue(":platinum_difficulty",   $mission["rating_info"]["platinum_difficulty"]);
$query->bindValue(":ultimate_difficulty",   $mission["rating_info"]["ultimate_difficulty"]);
$query->bindValue(":awesome_difficulty",    $mission["rating_info"]["awesome_difficulty"]);
$query->bindValue(":hunt_multiplier",       $mission["rating_info"]["hunt_multiplier"]);
$query->bindValue(":hunt_divisor",          $mission["rating_info"]["hunt_divisor"]);
$query->bindValue(":hunt_completion_bonus", $mission["rating_info"]["hunt_completion_bonus"]);
$query->bindValue(":hunt_par_bonus",        $mission["rating_info"]["hunt_par_bonus"]);
$query->bindValue(":hunt_platinum_bonus",   $mission["rating_info"]["hunt_platinum_bonus"]);
$query->bindValue(":hunt_ultimate_bonus",   $mission["rating_info"]["hunt_ultimate_bonus"]);
$query->bindValue(":hunt_awesome_bonus",    $mission["rating_info"]["hunt_awesome_bonus"]);
$query->bindValue(":quota_100_bonus",       $mission["rating_info"]["quota_100_bonus"]);
$query->bindValue(":egg_rating",            $mission["rating_info"]["egg_rating"]);
$query->bindValue(":disabled",              $mission["rating_info"]["disabled"]);
$query->bindValue(":notes",                 $mission["rating_info"]["notes"]);
$query->bindValue(":mission_id",            $mission["id"]);

$changes = [];
foreach ($mission["rating_info"] as $key => $newValue) {
	if ($oldInfo->ratingInfo[$key] != $newValue) {
		$changes[$key] = [
			"old" => $oldInfo->ratingInfo[$key],
			"new" => $newValue
		];
	}
}

//If they changed anything make a log of it
if (count($changes) >= 0) {
	$changelog = $db->prepare("INSERT INTO `ex82r_mission_change_log` SET
			`mission_id` = :mission_id,
			`changes` = :changes
		");
	$changelog->bindValue(":mission_id", $mission["id"]);
	$changelog->bindValue(":changes", json_encode($changes));
	$changelog->execute();
}

$db->commit();
