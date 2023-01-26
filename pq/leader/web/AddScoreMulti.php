<?php
define("PQ_RUN", true);
require_once("../Framework.php");

if (!Login::isLoggedIn()) {
	$return = 'https://marbleblast.com' . $_SERVER["REQUEST_URI"];
	$loginUrl = '/index.php?' . JUri::buildQuery(['option' => 'com_users', 'view' => 'login', 'return' => base64_encode($return)]);

	header("Location: $loginUrl");
	header("HTTP/1.1 307 Temporary Redirect");
	die();
}
if (!Login::isPrivilege("pq.mod.editRatings")) {
	header("Location: https://marbleblast.com/pq/ratings/AddScoreMulti.php");
	header("HTTP/1.1 301 Moved Permanently");
	die();
}
?>
<html>
<head>
	<title>MULTI SCORE ADD</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

	<script type="text/javascript">
		config = {
			"base": "https://marbleblast.com/pq/leader"
		}
	</script>
</head>
<body>
<h1>Multi Score Add</h1>
<h2>For when you're lazy</h2>
<div>Usage so you don't fuck it up:
    <ol>
        <li>
            Enter times in the format below
            <ul>
                <li>Make sure you use their username and the mission's in-game name (as seen on ratings editor)</li>
                <li>Yes you need to put all the fields in</li>
            </ul>
        </li>
        <li>Press Check Scores</li>
        <li>Make sure it actually found all of the missions</li>
        <li>Press Actually do it</li>
    </ol>
</div>
<div>
	Game/Difficulty/Mission/User/Score/Bonus/Gems/R/G/B/P<br>
	Game/Difficulty/Mission/User/Score/Bonus/Gems/R/G/B/P<br>
	...
</div>
<textarea name="times" id="times" cols="100" rows="10">Gold/Advanced/A-Maze-ing/someusernamehere/2680/0/0/0/0/0/0</textarea>
<br>
<button id="testSubmit">Check Scores</button>
<button id="submit">Actually do it</button>
<div id="output"></div>
<script type="text/javascript">

// DANGER: Lazy code
// Mostly copied from RatingsViewer.js
// Read at your own peril!

let missionList = {};

document.getElementById("testSubmit").addEventListener("click", async () => {
	let text = document.getElementById("times").value;
	let lines = text.split("\n").map((line) => {
		return line.split("/");
	});

	let output = document.getElementById("output");

	output.innerText = "Here are the scores that will be added:\n";
	for (let line of lines) {
		if (line.length !== 11) {
			continue;
        }
		let mis = getMission(line[0], line[1], line[2]);
		let user = line[3];
		let score = parseInt(line[4]);
		let bonus = parseInt(line[5]);
		let gems = parseInt(line[6]);
		let gemsR = parseInt(line[7]);
		let gemsY = parseInt(line[8]);
		let gemsB = parseInt(line[9]);
		let gemsP = parseInt(line[10]);
		let scoreType = (gemsR > 0 || gemsY > 0 || gemsB > 0 ? "score" : "time");
        if (isNaN(score) || isNaN(bonus) || isNaN(gems) || isNaN(gemsR) || isNaN(gemsY) || isNaN(gemsB) || isNaN(gemsP)) {
        	continue;
        }

		if (mis === undefined) {
			output.innerText += `INVALID MISSION ${line[0]}/${line[1]}/${line[2]}`;
		} else {
			output.innerText += `${mis.id} / ${mis.name}`;
		}
		let fancyScore = (scoreType === "time" ? formatTime(score) : formatScore(score));
		let fancyBonus = formatTime(bonus);
		output.innerText += ` ${user} ${scoreType} of ${fancyScore}+${fancyBonus} / GEMS: ${gems} (${gemsR}R/${gemsY}Y/${gemsB}B/${gemsP}P) `;
		output.innerText += ` ==> TBD POINTS\n`;
	}
});

document.getElementById("submit").addEventListener("click", async () => {
	let text = document.getElementById("times").value;
	let lines = text.split("\n").map((line) => {
		return line.split("/");
	});

	let output = document.getElementById("output");

	output.innerText = "";
	for (let line of lines) {
		if (line.length !== 11) {
			continue;
        }
		let mis = getMission(line[0], line[1], line[2]);
		let user = line[3];
		let score = parseInt(line[4]);
		let bonus = parseInt(line[5]);
		let gems = parseInt(line[6]);
		let gemsR = parseInt(line[7]);
		let gemsY = parseInt(line[8]);
		let gemsB = parseInt(line[9]);
		let gemsP = parseInt(line[10]);
		let scoreType = (gemsR > 0 || gemsY > 0 || gemsB > 0 ? "score" : "time");
		if (isNaN(score) || isNaN(bonus) || isNaN(gems) || isNaN(gemsR) || isNaN(gemsY) || isNaN(gemsB) || isNaN(gemsP)) {
			continue;
		}

		if (mis === undefined) {
			output.innerText += `INVALID MISSION ${line[0]}/${line[1]}/${line[2]}`;
			continue;
		} else {
			output.innerText += `${mis.id} / ${mis.name}`;
		}
		let fancyScore = (scoreType === "time" ? formatTime(score) : formatScore(score));
		let fancyBonus = formatTime(bonus);
		output.innerText += ` ${user} ${scoreType} of ${fancyScore}+${fancyBonus} / GEMS: ${gems} (${gemsR}R/${gemsY}Y/${gemsB}B/${gemsP}P) `;

		let ratings = await $.ajax({
			method: "POST",
			url: config.base + "/web/AddScore.php",
			dataType: "json",
			data: {
				"username": user,
				"modifiers": 0,
				"missionId": mis.id,
				"score_type": (gemsR > 0 || gemsY > 0 || gemsB > 0 ? "score" : "time"),
				"score": score,
				"total_bonus": bonus,
				"gem_count": gems,
				"gems_1_point": gemsR,
				"gems_2_point": gemsY,
				"gems_5_point": gemsB,
				"gems_10_point": gemsP,
			}
		});
		output.innerText += ` ==> ${ratings.rating} POINTS\n`;
	}
});

//http://stackoverflow.com/a/14760377
String.prototype.paddingLeft = function (paddingValue) {
	return String(paddingValue + this).slice(-paddingValue.length);
};

function formatTime(time) {
	time = Math.abs(time);

	//xx:xx.xxx
	var millis  =            (time %  1000)        .toString().paddingLeft("000");
	var seconds = Math.floor((time % 60000) / 1000).toString().paddingLeft("00");
	var minutes = Math.floor( time / 60000)        .toString().paddingLeft("00");

	return minutes + ":" + seconds + "." + millis;
}

function formatScore(score) {
	return score.toLocaleString();
}

function getGame(gameName) {
	return missionList.games.find(function(testGame) {
		return testGame.name.toLowerCase() === gameName.toLowerCase();
	});
}

function getDifficulty(gameName, difficultyName) {
	return getGame(gameName).difficulties.find(function(testDifficulty) {
		return testDifficulty.name.toLowerCase() === difficultyName.toLowerCase();
	});
}

function getMission(gameName, difficultyName, missionName) {
	return getDifficulty(gameName, difficultyName).missions.find(function(testMission) {
		return testMission.name.toLowerCase() === missionName.toLowerCase();
	});
}

function getMissionList() {
	//Such a convenient file
	$.ajax({
		method: "POST",
		url: config.base + "/api/Mission/GetMissionList.php",
		dataType: "json"
	}).done(function(data) {
		missionList = data;
	});
}
getMissionList();

</script>
</body>
</html>