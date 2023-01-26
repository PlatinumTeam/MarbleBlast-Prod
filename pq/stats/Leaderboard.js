var modifierList = [
	{"field": "gotEasterEgg",      "name": "Got Easter Egg",   "flag": 1 << 0},
	{"field": "noJumping",         "name": "No Jumping",       "flag": 1 << 1},
	{"field": "doubleDiamond",     "name": "Double Diamond",   "flag": 1 << 2},
	{"field": "noTimeTravels",     "name": "No Time Travels",  "flag": 1 << 3},
	{"field": "quotaHundred",      "name": "Quota 100%",       "flag": 1 << 4},
	{"field": "gemMadnessAll",     "name": "Gem Madness 100%", "flag": 1 << 5},
	{"field": "BeatParTime",       "name": "Beat Par Time", "flag": 1 << 6},
	{"field": "BeatPlatinumTime",  "name": "Beat Platinum Time", "flag": 1 << 7},
	{"field": "BeatUltimateTime",  "name": "Beat Ultimate Time", "flag": 1 << 8},
	{"field": "BeatAwesomeTime",   "name": "Beat Awesome Time", "flag": 1 << 9},
	{"field": "BeatParScore",      "name": "Beat Par Score", "flag": 1 << 10},
	{"field": "BeatPlatinumScore", "name": "Beat Platinum Score", "flag": 1 << 11},
	{"field": "BeatUltimateScore", "name": "Beat Ultimate Score", "flag": 1 << 12},
	{"field": "BeatAwesomeScore",  "name": "Beat Awesome Score", "flag": 1 << 13},
	{"field": "WasWorldRecord",    "name": "Was World Record", "flag": 1 << 14},
	{"field": "IsBestScore",       "name": "Current Best Score", "flag": 1 << 15},
	{"field": "Controller",        "name": "Controller Input", "flag": 1 << 16},
];

//http://stackoverflow.com/a/14760377
String.prototype.paddingLeft = function (paddingValue) {
	return String(paddingValue + this).slice(-paddingValue.length);
};

var columns = [
	{name: "id",            display: "Score ID",      type: "number"},
	{name: "placement",     display: "#",             type: "place"},
	{name: "user_id",       display: "User ID",       type: "number"},
	{name: "username",      display: "Username",      type: "string"},
	{name: "name",          display: "Player",        type: "string"},
	{name: "score",         display: "Score",         type: ""},
	{name: "score_type",    display: "Score Type",    type: "string"},
	{name: "modifiers",     display: "Modifiers",     type: "modifiers"},
	{name: "total_bonus",   display: "Total Bonus",   type: "time"},
	{name: "rating",        display: "Rating",        type: "score"},
	{name: "gem_count",     display: "Gem Count",     type: "score"},
	{name: "gems_1_point",  display: "Red Gems",      type: "score"},
	{name: "gems_2_point",  display: "Yellow Gems",   type: "score"},
	{name: "gems_5_point",  display: "Blue Gems",     type: "score"},
	{name: "gems_10_point", display: "Platinum Gems", type: "score"},
	{name: "origin",        display: "Origin",        type: "origin"},
	{name: "timestamp",     display: "Date",          type: "string"},
];

var columnsEnabled = [
	false,
	true,
	false,
	false,
	true,
	true,
	false,
	false,
	true,
	true,
	true,
	true,
	true,
	true,
	true,
	false,
	false
];

var queryTopScores = true;

function getCheckState() {
	var state = "";
	state += queryTopScores ? "1" : "0";
	columnsEnabled.forEach(function (enabled) {
		state += enabled ? "1" : "0";
	});
	return state;
}

function loadCheckState(state) {
	queryTopScores = state[0] === "1";
	columnsEnabled = columns.map(function (column, index) {
		if (state.length <= index + 1) {
			return columnsEnabled[index];
		}
		return state[index + 1] === "1";
	});
}

function updateWindowHash(gameId, difficultyId, missionId) {
	if (window.location.hash) {
		var hash = window.location.hash;
		var re = new RegExp("gameType=(.*?)(&|#|$)", "i");
		var matches = hash.match(re);
		if (matches && matches.length > 0) {
			window.location.hash = "gameType=" + encodeURIComponent(matches[1]) + "&gameId=" + gameId + "&difficultyId=" + difficultyId + "&missionId=" + missionId + "&checks=" + getCheckState();
			return;
		}
	}

	window.location.hash = "gameId=" + gameId + "&difficultyId=" + difficultyId + "&missionId=" + missionId + "&checks=" + getCheckState();
}

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

function displayScores(gameId, difficultyId, missionId, scores) {
	var container = $("#scores");
	container.empty();

	var table = $("<table/>")
		.addClass("table table-bordered table-striped");

	var header = $("<tr/>");

	columns.forEach(function(col, colIndex) {
		if (columnsEnabled[colIndex] === false)
			return;

		header.append($("<th/>").text(col.display));
	});

	table.append(header);

	scores.scores.forEach(function(score, index) {
		var row = $("<tr/>");
		columns.forEach(function(col, colIndex) {
			if (columnsEnabled[colIndex] === false)
				return;

			var value = score[col.name];
			var display = value;

			if (value === null) {
				display = "";
			} else {
				var type = col.type || score.score_type;
				switch (type) {
					case "time":
						display = formatTime(value);
						break;
					case "score":
						display = formatScore(display);
						break;
					case "place":
						display = value + ". ";
						break;
					case "modifiers":
						display = modifierList.filter(function (modifier) {
							return (value & modifier.flag) === modifier.flag;
						}).map(function (modifier) {
							return modifier.name;
						}).join("\n");
						break;
					case "origin":
						switch (value) {
							case "PhilsEmpire":
								display = "MBP < 1.50";
								break;
							case "MarbleBlast.com":
								display = "MBP >= 1.50";
								break;
							case "MarbleBlastPlatinum":
								display = "MBP >= 1.50";
								break;
							case "PlatinumQuest":
								display = "PQ >= 2.0.0";
								break;
							case "Ratings Viewer":
								display = "Manual Entry";
								break;
							default:
								display = value;
								break;
						}
						break;
				}
			}
			row.append($("<td/>").addClass("ws-pre").text(display));
		});

		table.append(row);
	});

	container.append(table);

	var columnContainer = $("#columns");
	columnContainer.empty();
	var columnList = $("<ul/>");
	columns.forEach(function(column, index) {
		var li = $("<li/>");
		var check = $("<input/>")
			.attr("type", "checkbox")
			.addClass("column-checkbox")
			.attr("id", "column-check-" + index);
		if (columnsEnabled[index]) {
			check.attr("checked", "checked");
		}
		check.click(function (e) {
			columnsEnabled[index] = check.is(":checked");
			updateWindowHash(gameId, difficultyId, missionId);
			displayScores(gameId, difficultyId, missionId, scores);
		});
		li.append(check);
		li.append(
			$("<label/>")
				.attr("for", "column-check-" + index)
				.text(" " + column.display)
		);
		columnList.append(li);
	});
	columnContainer.append(columnList);
	columnContainer.append("<hr/>")

	var topScoresCheck = $("<input/>")
		.attr("type", "checkbox")
		.addClass("column-checkbox")
		.attr("id", "top-scores-check");
	if (queryTopScores) {
		topScoresCheck.attr("checked", "checked");
	}
	topScoresCheck.click(function (e) {
		queryTopScores = topScoresCheck.is(":checked");
		onSelectMission(gameId, difficultyId, missionId);
	});
	columnContainer.append(
		$("<span/>")
			.append(topScoresCheck)
			.append(
				$("<label/>")
					.attr("for", "top-scores-check")
					.text(" Top Scores Only")
			)
			.append("<br/>")
			.append("Warning: Unchecking this will lag your browser")
	);
}

//-----------------------------------------------------------------------------

var missionList = [];

/**
 * Called when changing games, updates the difficulty list
 * @param gameId The new game's id
 */
function onSelectGame(gameId) {
	//Update selector
	var selectGame = $("#selectGame");
	selectGame.children(":selected").attr("selected", null);
	selectGame.children("[data-game-id=" + gameId + "]").attr("selected", "selected");

	var selectDifficulty = $("#selectDifficulty");
	var selectMission = $("#selectMission");
	selectDifficulty.empty();
	selectMission.empty();

	getGame(gameId).difficulties.forEach(function(difficulty) {
		//For some reason this is necessary because there's no associative list
		// of difficulty infos.
		var difficultyDisplay = difficulty.display;
		var difficultyId = difficulty.id;

		selectDifficulty.append(
			$("<option></option>")
				.val(difficultyId)
				.text(difficultyDisplay)
				.attr("data-game-id", gameId)
				.attr("data-difficulty-id", difficultyId)
		);
	});
}

/**
 * Called when changing difficulties, updates the mission list
 * @param gameId The current game's id
 * @param difficultyId The new difficulty's id
 */
function onSelectDifficulty(gameId, difficultyId) {
	//Update selector
	var selectDifficulty = $("#selectDifficulty");
	selectDifficulty.children(":selected").attr("selected", null);
	selectDifficulty.children("[data-difficulty-id=" + difficultyId + "]").attr("selected", "selected");

	var selectMission = $("#selectMission");
	selectMission.empty();

	getDifficulty(gameId, difficultyId).missions.forEach(function(missionInfo) {
		var missionId = missionInfo.id;
		var missionDisplay = missionInfo.name;

		selectMission.append(
			$("<option></option>")
				.val(missionId)
				.text(missionDisplay)
				.attr("data-game-id", gameId)
				.attr("data-difficulty-id", difficultyId)
				.attr("data-mission-id", missionId)
		);
	});
}

/**
 * Called when changing missions, updates the graph and rating fields
 * @param gameId The current game's id
 * @param difficultyId The current difficulty's id
 * @param missionId The new mission's id
 */
function onSelectMission(gameId, difficultyId, missionId) {
	updateWindowHash(gameId, difficultyId, missionId);

	//Update selector
	var selectMission = $("#selectMission");
	selectMission.children(":selected").attr("selected", null);
	selectMission.children("[data-mission-id=" + missionId + "]").attr("selected", "selected");

	//Clear scores to indicate updating
	var container = $("#scores");
	container.empty();

	var endpoint = (queryTopScores ? "/api/Score/GetGlobalTopScores.php" : "/api/Score/GetGlobalScores.php");

	//Rating info is in another file but we can just get it from the server
	$.ajax({
		method: "POST",
		url: config.base + endpoint,
		dataType: "json",
		data: {
			missionId: missionId
		}
	}).done(function(scores) {
		displayScores(gameId, difficultyId, missionId, scores);
	});
}

function selectMission(gameId, difficultyId, missionId) {
	onSelectGame(gameId);
	onSelectDifficulty(gameId, difficultyId);
	onSelectMission(gameId, difficultyId, missionId);
}

function getGame(gameId) {
	return missionList.games.find(function(testGame) {
		return testGame.id === gameId;
	});
}

function getDifficulty(gameId, difficultyId) {
	return getGame(gameId).difficulties.find(function(testDifficulty) {
		return testDifficulty.id === difficultyId;
	});
}

function getMission(gameId, difficultyId, missionId) {
	return getDifficulty(gameId, difficultyId).missions.find(function(testMission) {
		return testMission.id === missionId;
	});
}

function getMissionList() {
	var gameType = "Single Player";

	if (window.location.hash) {
		var hash = window.location.hash;
		var re = new RegExp("gameType=(.*?)(&|#|$)", "i");
		var matches = hash.match(re);
		if (matches && matches.length > 0) {
			gameType = matches[1].toString();
		}
	}

	//Such a convenient file
	$.ajax({
		method: "POST",
		url: config.base + "/api/Mission/GetMissionList.php",
		data: {
			gameType: gameType
		},
		dataType: "json"
	}).done(function(data) {
		var selectGame = $("#selectGame");
		var selectDifficulty = $("#selectDifficulty");
		var selectMission = $("#selectMission");
		selectGame.empty();
		selectDifficulty.empty();
		selectMission.empty();

		missionList = data;

		//Create the game list now since it never changes
		data.games.forEach(function(gameInfo) {
			var gameDisplay = gameInfo.display;
			var gameId = gameInfo.id;

			selectGame.append(
				$("<option></option>")
					.val(gameId)
					.text(gameDisplay)
					.attr("data-game-id", gameId)
			);
		});

		//Selectors have update methods that just call the functions above
		selectGame.change(function() {
			var gameId = parseInt(selectGame.children(":selected").attr("data-game-id"));
			var game = getGame(gameId);
			var difficultyId = game.difficulties[0].id;
			var missionId = game.difficulties[0].missions[0].id;

			onSelectGame(gameId);
			onSelectDifficulty(gameId, difficultyId);
			onSelectMission(gameId, difficultyId, missionId);
		});
		selectDifficulty.change(function() {
			var gameId = parseInt(selectDifficulty.children(":selected").attr("data-game-id"));
			var difficultyId = parseInt(selectDifficulty.children(":selected").attr("data-difficulty-id"));
			var missionId = getDifficulty(gameId, difficultyId).missions[0].id;

			onSelectDifficulty(gameId, difficultyId);
			onSelectMission(gameId, difficultyId, missionId);
		});
		selectMission.change(function() {
			var gameId = parseInt(selectMission.children(":selected").attr("data-game-id"));
			var difficultyId = parseInt(selectMission.children(":selected").attr("data-difficulty-id"));
			var missionId = parseInt(selectMission.children(":selected").attr("data-mission-id"));

			onSelectMission(gameId, difficultyId, missionId);
		});

		//And select the first one so stuff looks populated
		var gameId = data.games[0].id;
		var difficultyId = data.games[0].difficulties[0].id;
		var missionId = data.games[0].difficulties[0].missions[0].id;

		//If they have a mission selected in the url hash load that instead
		if (window.location.hash) {
			var hash = window.location.hash;

			//Extract information for each variable
			var re = new RegExp("gameId=(.*?)(&|#|$)", "i");
			var matches = hash.match(re);
			if (matches && matches.length > 0) {
				var id = parseInt(matches[1]);
				if (data.games.some(game => game.id === id)) {
					gameId = id;
				}
			}
			re = new RegExp("difficultyId=(.*?)(&|#|$)", "i");
			matches = hash.match(re);
			if (matches && matches.length > 0) {
				var id = parseInt(matches[1]);
				if (data.games.filter(game => game.id === gameId)[0].difficulties.some(difficulty => difficulty.id === id)) {
					difficultyId = id;
				}
			}
			re = new RegExp("missionId=(.*?)(&|#|$)", "i");
			matches = hash.match(re);
			if (matches && matches.length > 0) {
				var id = parseInt(matches[1]);
				if (data.games.filter(game => game.id === gameId)[0].difficulties.filter(difficulty => difficulty.id === difficultyId)[0].missions.some(mission => mission.id === id)) {
					missionId = id;
				}
			}
			re = new RegExp("checks=(.*?)(&|#|$)", "i");
			matches = hash.match(re);
			if (matches && matches.length > 0) {
				loadCheckState(matches[1]);
			}
		}

		onSelectGame(gameId);
		onSelectDifficulty(gameId, difficultyId);
		onSelectMission(gameId, difficultyId, missionId);

		var allMissions = data.games.flatMap((g) => g.difficulties).flatMap(d => d.missions);
		var searchBox = $("#searchBox");
		var fuseOptions = {
			keys: [
				"name"
			]
		};
		searchBox.fuzzyComplete(allMissions, {
			fuseOptions: fuseOptions,
			display: "name",
			key: "id"
		}).change(function(e) {

		});
	});
}

//Start her up
getMissionList();
