<?php
define("PQ_RUN", true);
require_once("../leader/Framework.php");
?>

<html>
<head>
	<title>MB LBs WRs</title>
	<style>
		.container {
			display: flex;
			flex-direction: row;
		}
		.container * {
			flex-grow: 1;
		}
		.container * {
			margin: 10px;
		}
		table, td, th {
			border: 1px solid #999;
			border-collapse: collapse;
		}
	</style>
</head>
<body>
<div class="container">
	<table>
		<tr>
			<th>Game</th>
			<th>Difficulty</th>
			<th>Index</th>
			<th>Level</th>
			<th>Time</th>
			<th>Player</th>
			<th>Date</th>
			<th>Id</th>
		</tr>
<?php

$query = $db->prepare("
	SELECT * FROM ex82r_gen_world_records records
	 JOIN ex82r_user_scores score ON records.score_id = score.id
	 JOIN ex82r_missions mission
	   ON score.mission_id = mission.id
	 JOIN ex82r_mission_games game
	   ON mission.game_id = game.id
	 JOIN ex82r_mission_difficulties difficulty
	   ON mission.difficulty_id = difficulty.id
	 JOIN ex82r_mission_rating_info ratings
	   ON mission.id = ratings.mission_id
     JOIN prod_joomla.bv2xj_users user
       ON score.user_id = user.id
	WHERE game_type = 'Single Player'
	  AND is_custom = 0
	  AND game.disabled = 0
	  AND difficulty.disabled = 0
	  AND ratings.disabled = 0
    ORDER BY game.sort_index, difficulty.sort_index, mission.sort_index
");

$query->execute();
$rows = fetchAllTableAssociative($query);

foreach ($rows as $row) {
	$format = $row["score"]["score_type"] === "score" ? $row["score"]["score"] : formatTime($row["score"]["score"], true);
	$date = date("Y-m-d H:i:s", strtotime($row["score"]["timestamp"]));

	?>
	<tr>
		<td><?= htmlentities($row["game"]["display"]) ?></td>
		<td><?= htmlentities($row["difficulty"]["display"]) ?></td>
		<td><?= htmlentities($row["mission"]["sort_index"]) ?></td>
		<td><?= htmlentities($row["mission"]["name"]) ?></td>
		<td><?= htmlentities($format) ?></td>
		<td><?= htmlentities($row["user"]["name"]) ?></td>
		<td><?= htmlentities($date) ?></td>
		<td><?= htmlentities($row["mission"]["id"]) ?></td>
	</tr>
	<?php
}

?>
	</table>
	<table>
		<tr>
			<th>Player</th>
			<th>WR Count</th>
		</tr>
<?php

$query = $db->prepare("
	SELECT user.name, COUNT(*) AS count FROM ex82r_gen_world_records records
	 JOIN ex82r_user_scores score ON records.score_id = score.id
	 JOIN ex82r_missions mission
	   ON score.mission_id = mission.id
	 JOIN ex82r_mission_games game
	   ON mission.game_id = game.id
	 JOIN ex82r_mission_difficulties difficulty
	   ON mission.difficulty_id = difficulty.id
	 JOIN ex82r_mission_rating_info ratings
	   ON mission.id = ratings.mission_id
     JOIN prod_joomla.bv2xj_users user
       ON score.user_id = user.id
	WHERE game_type = 'Single Player'
	  AND is_custom = 0
	  AND game.disabled = 0
	  AND difficulty.disabled = 0
	  AND ratings.disabled = 0
    GROUP BY user.id
    ORDER BY count DESC, user.name ASC
");
$query->execute();

$counts = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($counts as $row) {
	?>
	<tr>
		<td><?= htmlentities($row["name"]) ?></td>
		<td><?= htmlentities($row["count"]) ?></td>
	</tr>
	<?php
}
?>
	</table>
</div>
</body>
</html>