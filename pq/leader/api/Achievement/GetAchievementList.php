<?php

define("PQ_RUN", true);
require_once("../../Framework.php");

$result = [
	"categories" => [],
	"categoryNames" => [],
	"achievements" => []
];

$query = $db->prepare("SELECT * FROM `ex82r_achievement_categories`");
$query->execute();
$categories = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($categories as $category) {
	$result["categoryNames"][] = $category["title"];
	$result["categories"][$category["title"]] = $category;
}

$query = $db->prepare("
	SELECT `ex82r_achievement_names`.`id`, `ex82r_achievement_categories`.`title` AS `category`, `index`, `ex82r_achievement_names`.`title`, `description`, `rating`, `bitmap_extent` FROM `ex82r_achievement_names`
	-- Check if we have the achievement. Don't show masked ones if we don't have that achievement
    LEFT JOIN (
        SELECT * FROM ex82r_user_achievements WHERE user_id = :user_id
    ) AS `userAchievements`
    ON userAchievements.achievement_id = ex82r_achievement_names.id
    JOIN ex82r_achievement_categories ON ex82r_achievement_names.category_id = ex82r_achievement_categories.id
	WHERE mask = 0 OR `user_id` IS NOT NULL
	ORDER BY ex82r_achievement_names.sort ASC
");
//Note that this means offline players won't see them at all
$query->bindValue(":user_id", Login::isLoggedIn() ? Login::getCurrentUserId() : 0);
$query->execute();

$achievements = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($achievements as $achievement) {
	$category = $achievement["category"];
	if (!array_key_exists($category, $result["achievements"])) {
		$result["achievements"][$category] = [];
	}
	$result["achievements"][$category][] = $achievement;
}

techo(json_encode($result));
