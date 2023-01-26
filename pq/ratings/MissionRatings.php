<?php
define("PQ_RUN", true);
require_once("../leader/Framework.php");
?>

<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <script src="https://jquery.cdn.tuxis.nl/jquery-3.2.1.min.js"></script>

    <style type="text/css">
	.positive, .neutral, .negative {
		display:inline-block;
		margin:0;
		padding:0;
	}
	.positive {
		background-color:#00ff00;
	}
	.neutral {
		background-color:#0000ff;
		text-align: center;
		color: #fff;
	}
	.negative {
		background-color:#ff0000;
	}
	.many {
		background-color:#f8f8f8;
	}
	.few {
		background-color:#cccccc;
	}
</style>
</head>
<body>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
			<h1 class="text-center">Multiplayer Level Ratings</h1>
			<br>
			<label for="huntOnly">Official Hunt Only: <input type="checkbox" id="huntOnly"></label>
			<h3 class="text-center">Level List</h3>
			<div>
				<table class="table table-rounded table-bordered" id="levels">
					<tr>
						<td><a href="javascript:void(0);" class="sorter" sort-by="Level">Level</a></td>
						<td><a href="javascript:void(0);" class="sorter" sort-by="Count">Count</a></td>
						<td>&#x2713;</td>
						<td>?</td>
						<td>x</td>
						<td><a href="javascript:void(0);" class="sorter" sort-by="Ratio">Ratio</a></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var ratings = <?php

	$query = $db->prepare("
        SELECT
          SUM(IF(rating = 1, 1, 0)) AS positive,
          SUM(IF(rating = 0, 1, 0)) AS neutral,
          SUM(IF(rating = -1, 1, 0)) AS negative,
          name,
          gamemode
        FROM ex82r_user_mission_ratings
        JOIN ex82r_missions ON ex82r_user_mission_ratings.mission_id = ex82r_missions.id
        JOIN ex82r_mission_rating_info ON ex82r_missions.id = ex82r_mission_rating_info.mission_id
        WHERE is_custom = 0
        GROUP BY ex82r_missions.id
	");

	$query->execute();
	$levels = array();

	while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
		$level    = $row["name"];
		$positive = $row["positive"];
		$neutral  = $row["neutral"];
		$negative = $row["negative"];
		$mode     = $row["gamemode"];
		$total    = $positive + $neutral + $negative;

		if ($total > 0)
			$levels[] = [$level, $positive, $neutral, $negative, $total, $mode];
	}

	echo(json_encode($levels));
?>;

	var huntOnly = false;

	function sortLevels(col, direction) {
		ratings.sort(function(a, b) {
			if (a[4] >= 10 && b[4] < 10)
				return -1;
			if (a[4] < 10 && b[4] >= 10)
				return 1;

			switch (col) {
				case "Ratio":
					var a_rating = (a[1] - a[3]) / a[4];
					var b_rating = (b[1] - b[3]) / b[4];
					if (a_rating > b_rating)
						return -1 * direction;
					if (a_rating < b_rating)
						return 1 * direction;
					break;
				case "Count":
					break;
				case "Level":
					return a[0].localeCompare(b[0]) * direction;
			}

			if (a[4] > b[4])
				return -1 * direction;
			if (a[4] < b[4])
				return 1 * direction;

			return 0;
		});

		showLevels();
	}

	function showLevels() {
		var table = $("#levels");
		table.children().children(".level").remove();
		ratings.forEach(function(rating) {
			var level    = rating[0];
			var positive = rating[1];
			var neutral  = rating[2];
			var negative = rating[3];
			var total    = rating[4];
			var mode     = rating[5];

			if ((mode == null || mode.toLowerCase() !== "hunt") && huntOnly)
				return;

			var css = (total >= 10 ? "many" : "few");
			var elem = $("<tr></tr>").addClass(css).addClass("level");

			elem.append($("<td></td>").text(level));
			elem.append($("<td></td>").text(total));
			elem.append($("<td></td>").text(positive));
			elem.append($("<td></td>").text(neutral));
			elem.append($("<td></td>").text(negative));

			var votey = $("<td></td>").css("width", "33%");
			votey.append($("<div></div>")
				.addClass("positive")
				.attr("votes", positive)
				.css("width", (100 * positive / total) + "%")
				.html("&nbsp;")
			);
			votey.append($("<div></div>")
				.addClass("neutral")
				.attr("votes", neutral)
				.css("width", (100 * neutral / total) + "%")
				.text("|")
			);
			votey.append($("<div></div>")
				.addClass("negative")
				.attr("votes", negative)
				.css("width", (100 * negative / total) + "%")
				.html("&nbsp;")
			);

			elem.append(votey);
			table.append(elem);
		});
	}

	var direction = 1;
	var lastSort = "";

	$.each($(".sorter"), function(id, sorter) {
		var $sorter = $(sorter);
		$sorter.click(function(e){
			var type = $sorter.attr("sort-by");
			if (lastSort === type)
				direction *= -1;
			else
				direction = 1;

			sortLevels(type, direction);
			$(".sorter").children(".fa").remove();
			$sorter.html(type + " " + (direction === 1 ? "<i class='fa fa-caret-down'></i>" : "<i class='fa fa-caret-up'></i>"));

			lastSort = type;
		});
	});

	$(".sorter[sort-by='Ratio']").click();

	$("#huntOnly").click(function() {
		huntOnly = $("#huntOnly").is(":checked");
		sortLevels(lastSort, direction);
	});
</script>
</body>
</html>