<?php
define("PQ_RUN", true);
require_once("../Framework.php");

$query = $db->prepare("
SELECT
  `user_id`,
  SUM(`rating`) AS `rating`
FROM
  (
    SELECT DISTINCT
      `bests`.`mission_id`,
      `bests`.`user_id`,
      `rating`,
      `score`
    FROM
      -- Select all time scores
      (
        SELECT
          `user_id`,
          `mission_id`,
          MIN(`sort`) AS `minSort`
        FROM ex82r_user_scores
        GROUP BY `user_id`, `mission_id`
      ) AS `bests`
      -- Join the scores table so we can get other info
      JOIN ex82r_user_scores
        ON ex82r_user_scores.`mission_id` = `bests`.`mission_id`
           AND ex82r_user_scores.`user_id` = `bests`.`user_id`
           AND ex82r_user_scores.`sort` = `bests`.`minSort`
  ) AS `bestScores`
GROUP BY `user_id`
ORDER BY `rating` DESC
");
$query->execute();
$ratings = $query->fetchAll();

$newRatings = [];

foreach ($ratings as $row) {
	list($userId, $rating) = $row;
	$newRatings[$userId] = $rating;

//	$query = $db->prepare("UPDATE `ex82r_user_ratings` SET `rating_general` = :general WHERE `user_id` = :user_id");
//	$query->bindValue(":general", $rating);
//	$query->bindValue(":user_id", $userId);
//	$query->execute();
}
$query = $pdb->prepare("SELECT
  `id`,
  SUM(`rating`) AS `rating`
FROM
  (
    SELECT DISTINCT
      `bests`.`level`,
      `bests`.`username`,
      `rating`,
      `score`
    FROM
      -- Select all time scores
      (
        SELECT
          `username`,
          `level`,
          MIN(`score`) AS `minSort`
        FROM `scores`
        GROUP BY `username`, `level`
      ) AS `bests`
      -- Join the scores table so we can get other info
      JOIN `scores`
        ON `scores`.`level` = `bests`.`level`
           AND `scores`.`username` = `bests`.`username`
           AND `scores`.`score` = `bests`.`minSort`
  ) AS `bestScores`
  JOIN prod_joomla.bv2xj_users ON bestScores.username = bv2xj_users.username
GROUP BY `id`
ORDER BY `rating` DESC");
$query->execute();
while (($row = $query->fetch()) !== false) {
	list($userId, $rating) = $row;
	$newRating = $newRatings[$userId];
	$diff = $newRating - $rating;

	if ($diff != 0)
		echo("User $userId rating diff of $diff\n");
}
