<?php
define("PQ_RUN", true);
require_once("../leader/Framework.php");

$query = $db->prepare("
    SELECT * FROM ex82r_gen_mission_stats stats
      JOIN ex82r_missions mission ON mission.id = stats.mission_id
      JOIN ex82r_mission_rating_info info ON stats.mission_id = info.mission_id
      JOIN ex82r_mission_difficulties difficulty ON mission.difficulty_id = difficulty.id
      JOIN ex82r_mission_games game ON difficulty.game_id = game.id
    WHERE info.disabled = 0
    ORDER BY game.sort_index, difficulty.sort_index, mission.sort_index
");
$query->execute();
$missions = fetchAllTableAssociative($query);

$games = [];
foreach ($missions as $mission) {
    $gameId = $mission["mission"]["game_id"];
    $difficultyId = $mission["mission"]["difficulty_id"];
	if (!array_key_exists($gameId, $games)) {
		$games[$gameId] = [
			"info" => $mission["game"],
			"difficulties" => []
		];
	}
	if (!array_key_exists($difficultyId, $games[$gameId]["difficulties"])) {
	    $games[$gameId]["difficulties"][$difficultyId] = [
	        "info" => $mission["difficulty"],
            "missions" => []
        ];
    }
	$games[$gameId]["difficulties"][$difficultyId]["missions"][] = $mission;
}
//How many have beat each

function pickMissionScore($mission, $timeField, $scoreField) {
	$score = (int)$mission["info"][$scoreField];
	$time = (int)$mission["info"][$timeField];

	if ($score !== 0) {
		return $score;
	}
	return $time;
}

function formatMissionScore($mission, $timeField, $scoreField) {
	$score = $mission["info"][$scoreField];
	$time = $mission["info"][$timeField];

	if ($score !== 0) {
		return $score;
	}
	//Use time
    if ($time !== 0) {
	    return formatTime($time);
    }

    return "";
}

function formatPercent($ratio) {
    return sprintf("%.2f%%", $ratio * 100);
}

function quoteCSV($string) {
    return "\"" . str_replace("\"", "\"\"", $string) . "\"";
}

switch ($_GET["format"]) {
    case "html":
    default:
        outputHTML($games);
        break;
    case "csv":
        outputCSV($games);
        break;
}

function outputHTML($games) {
    ?>
    <style>
        .stats-table, .stats-table th, .stats-table td {
            border: 1px solid #999;
            border-collapse: collapse;
        }

        .numeric {
            min-width: 4em;
            text-align: right;
        }

        .par {
        }

        .platinum {
            background-color: #eeeeee;
        }

        .ultimate {
            background-color: #ffeecc;
        }

        .awesome {
            background-color: #ffcccc;
        }
    </style>
    <table class="stats-table">
		<?php foreach ($games as $game) { ?>
            <tr>
                <th colspan="15">
                    Game: <?= $game["info"]["display"] ?>
                </th>
            </tr>
			<?php foreach ($game["difficulties"] as $difficulty) { ?>
                <tr>
                    <th colspan="15">
                        Difficulty: <?= $difficulty["info"]["display"] ?>
                    </th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Level</th>
                    <th>Par</th>
                    <th>Platinum</th>
                    <th>Ultimate</th>
                    <th>Awesome</th>
                    <th># Plays</th>
                    <th># Par</th>
                    <th># Platinum</th>
                    <th># Ultimate</th>
                    <th># Awesome</th>
                    <th>% Par</th>
                    <th>% Platinum</th>
                    <th>% Ultimate</th>
                    <th>% Awesome</th>
                </tr>
				<?php foreach ($difficulty["missions"] as $mission) {
					$hasPar = pickMissionScore($mission, "par_time", "par_score") !== 0;
                    $hasPlatinum = pickMissionScore($mission, "platinum_time", "platinum_score") !== 0;
                    $hasUltimate = pickMissionScore($mission, "ultimate_time", "ultimate_score") !== 0;
                    $hasAwesome = pickMissionScore($mission, "awesome_time", "awesome_score") !== 0;
				    ?>
                    <tr>
                        <td>
							<?= $mission["mission"]["sort_index"] ?>
                        </td>
                        <td>
							<?= $mission["mission"]["name"] ?>
                        </td>
                        <td class="numeric par">
							<?= formatMissionScore($mission, "par_time", "par_score") ?>
                        </td>
                        <td class="numeric platinum">
							<?= formatMissionScore($mission, "platinum_time", "platinum_score") ?>
                        </td>
                        <td class="numeric ultimate">
							<?= formatMissionScore($mission, "ultimate_time", "ultimate_score") ?>
                        </td>
                        <td class="numeric awesome">
							<?= formatMissionScore($mission, "awesome_time", "awesome_score") ?>
                        </td>
                        <td class="numeric">
							<?= $mission["stats"]["count_plays"] ?>
                        </td>
                        <td class="numeric par">
							<?= $hasPar ? $mission["stats"]["count_par"] : "" ?>
                        </td>
                        <td class="numeric platinum">
							<?= $hasPlatinum ? $mission["stats"]["count_platinum"] : "" ?>
                        </td>
                        <td class="numeric ultimate">
							<?= $hasUltimate ? $mission["stats"]["count_ultimate"] : "" ?>
                        </td>
                        <td class="numeric awesome">
							<?= $hasAwesome ? $mission["stats"]["count_awesome"] : "" ?>
                        </td>
                        <td class="numeric par">
							<?= $hasPar ? formatPercent($mission["stats"]["count_par"] / $mission["stats"]["count_plays"]) : "" ?>
                        </td>
                        <td class="numeric platinum">
							<?= $hasPlatinum ? formatPercent($mission["stats"]["count_platinum"] / $mission["stats"]["count_plays"]) : "" ?>
                        </td>
                        <td class="numeric ultimate">
							<?= $hasUltimate ? formatPercent($mission["stats"]["count_ultimate"] / $mission["stats"]["count_plays"]) : "" ?>
                        </td>
                        <td class="numeric awesome">
							<?= $hasAwesome ? formatPercent($mission["stats"]["count_awesome"] / $mission["stats"]["count_plays"]) : "" ?>
                        </td>
                    </tr>
				<?php } ?>
			<?php } ?>
		<?php } ?>
    </table>

    <a href="?format=csv">Get as CSV</a>

	<?php
}

function outputCSV($games) {
	header('Content-Disposition: attachment; filename="stats.csv"');
	header('Content-type: text/csv');
    echo(
        "Game" . "," .
        "Difficulty" . "," .
        "#" . "," .
        "Level" . "," .
        "Par" . "," .
        "Platinum" . "," .
        "Ultimate" . "," .
        "Awesome" . "," .
        "# Plays" . "," .
        "# Par" . "," .
        "# Platinum" . "," .
        "# Ultimate" . "," .
        "# Awesome" . "," .
        "% Par" . "," .
        "% Platinum" . "," .
        "% Ultimate" . "," .
        "% Awesome"
    );
	echo("\n");

	foreach ($games as $game) {
		foreach ($game["difficulties"] as $difficulty) {
			foreach ($difficulty["missions"] as $mission) {
				$hasPar = pickMissionScore($mission, "par_time", "par_score") !== 0;
				$hasPlatinum = pickMissionScore($mission, "platinum_time", "platinum_score") !== 0;
				$hasUltimate = pickMissionScore($mission, "ultimate_time", "ultimate_score") !== 0;
				$hasAwesome = pickMissionScore($mission, "awesome_time", "awesome_score") !== 0;

                echo(quoteCSV($game["info"]["name"]) . ",");
                echo(quoteCSV($difficulty["info"]["name"]) . ",");
                echo($mission["mission"]["sort_index"] . ",");
                echo(quoteCSV($mission["mission"]["name"]) . ",");
                echo(pickMissionScore($mission, "par_time", "par_score") . ",");
                echo(pickMissionScore($mission, "platinum_time", "platinum_score") . ",");
                echo(pickMissionScore($mission, "ultimate_time", "ultimate_score") . ",");
                echo(pickMissionScore($mission, "awesome_time", "awesome_score") . ",");
                echo($mission["stats"]["count_plays"] . ",");
                echo(($hasPar ? $mission["stats"]["count_par"] : "") . ",");
                echo(($hasPlatinum ? $mission["stats"]["count_platinum"] : "") . ",");
                echo(($hasUltimate ? $mission["stats"]["count_ultimate"] : "") . ",");
                echo(($hasAwesome ? $mission["stats"]["count_awesome"] : "") . ",");
                echo(($hasPar ? formatPercent($mission["stats"]["count_par"] / $mission["stats"]["count_plays"]) : "") . ",");
                echo(($hasPlatinum ? formatPercent($mission["stats"]["count_platinum"] / $mission["stats"]["count_plays"]) : "") . ",");
                echo(($hasUltimate ? formatPercent($mission["stats"]["count_ultimate"] / $mission["stats"]["count_plays"]) : "") . ",");
                echo(($hasAwesome ? formatPercent($mission["stats"]["count_awesome"] / $mission["stats"]["count_plays"]) : "") . ",");
                echo("\n");
			}
		}
	}
}

