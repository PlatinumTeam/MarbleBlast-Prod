var modifierList = [
	{"field": "gotEasterEgg",  "name": "Got Easter Egg",   "flag": 1 << 0},
	{"field": "noJumping",     "name": "No Jumping",       "flag": 1 << 1},
	{"field": "doubleDiamond", "name": "Double Diamond",   "flag": 1 << 2},
	{"field": "noTimeTravels", "name": "No Time Travels",  "flag": 1 << 3},
	{"field": "quotaHundred",  "name": "Quota 100%",       "flag": 1 << 4},
	{"field": "gemMadnessAll", "name": "Gem Madness 100%", "flag": 1 << 5}
];
var modifierFlags = {
	"gotEasterEgg":  1 << 0,
	"noJumping":     1 << 1,
	"doubleDiamond": 1 << 2,
	"noTimeTravels": 1 << 3,
	"quotaHundred":  1 << 4,
	"gemMadnessAll": 1 << 5
};

function getNullScoreRating(score, ratingInfo, modifiers) {
	//Some quick bounds checking
	if (score < parseInt(ratingInfo.time_offset))
		return -2; //Bad Score

	// I just copied this all from 1.14
	var parTime = ratingInfo.par_time;
	var platinumTime = ratingInfo.platinum_time;
	var ultimateTime = ratingInfo.ultimate_time;
	var awesomeTime = ratingInfo.awesome_time;
	var completionBonus = ratingInfo.completion_bonus;

	//Levels with a difficulty automatically change their bonus
	completionBonus *= ratingInfo.difficulty;

	//This is the time used for calculating your score. If you got under par (and a par exists)
	// then your score will just be the score at par time, because the if-statement below will
	// reduce it linearly.
	var scoreTime;
	if (parTime > 0)
		scoreTime = Math.min(score, parTime) / 1000;
	else
		scoreTime = score / 1000;

	scoreTime -= parseInt(ratingInfo.time_offset) / 1000;
	scoreTime += 0.1;

	//You instantly get bonus points if you beat a challenge time
	var bonus = 0;
	if (platinumTime && score < platinumTime)
		bonus += ratingInfo.platinum_bonus * ratingInfo.platinum_difficulty;
	if (ultimateTime && score < ultimateTime)
		bonus += ratingInfo.ultimate_bonus * ratingInfo.ultimate_difficulty;
	if (awesomeTime && score < awesomeTime)
		bonus += ratingInfo.awesome_bonus * ratingInfo.awesome_difficulty;

	var standardiser = ratingInfo.standardiser;
	var setBaseScore = ratingInfo.set_base_score;
	var multiplierSetBase = ratingInfo.multiplier_set_base;

//(completion base score+(Platinum×platinum bonus)+(On Ult×platinum bonus)+(Ultimate×platinum bonus)+(Ultimate×ultimate bonus)+((LOG(Time,10)×Standardiser)−base score)×−1)×multiplier

	// Spy47 : Awesome formula (not made by me).
	var rating = (completionBonus + bonus + (((Math.log(scoreTime) / Math.log(10) * standardiser) - setBaseScore) * -1)) * multiplierSetBase;

	//If they get over the par time, linearly decrease the number of points they'll get until you hit 0
	if (score > parTime && (parTime > 0)) {
		//Number of points you will lose per second over par. It just divides the score at par
		// by the seconds after par until 99:59.999 (which gives a score of 0).
		var lostPerSec = (rating - 1) / (5999.999 - (parTime / 1000));

		//How many seconds over par you are
		var overPar = Math.max(score - parTime, 0) / 1000;

		//Just multiply them and that's how many points you lose
		rating -= overPar * lostPerSec;
	}

	// Spy47 : They'll probably commit suicide if they see a negative rating.
	rating = Math.floor(rating < 1 ? 1 : rating);

	return rating;
}

function getHuntScoreRating(score, ratingInfo, modifiers) {
	//Tons of bonuses
	var bonus = parseInt(ratingInfo.hunt_completion_bonus);
	if (ratingInfo.par_score && score >= ratingInfo.par_score)
		bonus += parseInt(ratingInfo.hunt_par_bonus);
	if (ratingInfo.platinum_score && score >= ratingInfo.platinum_score)
		bonus += parseInt(ratingInfo.hunt_platinum_bonus);
	if (ratingInfo.ultimate_score && score >= ratingInfo.ultimate_score)
		bonus += parseInt(ratingInfo.hunt_ultimate_bonus);
	if (ratingInfo.awesome_score && score >= ratingInfo.awesome_score)
		bonus += parseInt(ratingInfo.hunt_awesome_bonus);

	//Rating = HuntBaseScore (ℯ^(x / HuntStandardiser) - 1) + If[x ≥ Par, ParBonus, 0] + If[x ≥ Platinum, PlatinumBonus, 0] + If[x ≥ Ultimate, UltimateBonus, 0] + If[x ≥ Awesome, AwesomeBonus, 0] + CompletionBonus
	//Or more succinctly:
	//Rating = HuntBaseScore (ℯ^(x / HuntStandardiser) - 1) + Bonuses
	return Math.floor(ratingInfo.hunt_multiplier * (Math.exp(score / ratingInfo.hunt_divisor) - 1) + bonus);
}

function getGemMadnessScoreRating(score, ratingInfo, modifiers) {
	//Check for not all gems, (because it's cleaner)
	if ((modifiers & modifierFlags.gemMadnessAll) === 0) {
		return getHuntScoreRating(score, ratingInfo, modifiers);
	}

	//They have gotten all the hunt gems, so we need to combine the hunt rating for all gems
	// with a null rating of their time

	//Hunt rating for their points, which has to be calculated from their gem totals
	var huntRating = getHuntScoreRating(ratingInfo.hunt_max_score, ratingInfo);

	//Null rating of their time
	var nullRating = getNullScoreRating(score, ratingInfo, modifiers);

	return huntRating + nullRating;
}

function getQuotaScoreRating(score, ratingInfo, modifiers) {
	//Just the same as null
	var rating = getNullScoreRating(score, ratingInfo, modifiers);
	if (modifiers & modifierFlags.quotaHundred) {
		rating += ratingInfo.quota_100_bonus;
	}

	return rating;
}

function getRating(score, extendedInfo, modifiers) {
	//Combine the mission info with rating info

	var gameMode = extendedInfo.missionInfo["gamemode"];
	//Base mode is the first one
	var baseMode = gameMode.split(" ")[0].toLowerCase();
	switch (baseMode) {
		case "null":
			return getNullScoreRating(score, extendedInfo.ratingInfo, modifiers);
		case "hunt":
			return getHuntScoreRating(score, extendedInfo.ratingInfo, modifiers);
		case "gemmadness":
			return getGemMadnessScoreRating(score, extendedInfo.ratingInfo, modifiers);
		case "quota":
			return getQuotaScoreRating(score, extendedInfo.ratingInfo, modifiers);
	}

	//Unknown game mode, just use null mode
	return getNullScoreRating(score, extendedInfo.ratingInfo, modifiers);
}

//http://stackoverflow.com/a/14760377
String.prototype.paddingLeft = function (paddingValue) {
	return String(paddingValue + this).slice(-paddingValue.length);
};

function formatTime(time) {
	time = Math.abs(time);

	//xx:xx.xxx
	var millis  =            (time %  1000)        .toString().paddingLeft("000");
	var seconds = Math.floor((time % 60000) / 1000).toString();
	var minutes = Math.floor( time / 60000)        .toString();

	return (minutes > 0 ? minutes + ":" + seconds.paddingLeft("00") : seconds) + "." + millis;
}

var timeResolution = 100;

/**
 * Generate an object containing plot data for the ratings for a given level, including ticks
 * and xy data
 * @param extendedInfo The level's extended info
 * @param modifiers Bitfield of modifiers for rating
 * @returns {{x: Array, y: Array, tickvals: Array, ticktext: Array}}
 */
function generateRatingData(extendedInfo, modifiers) {
	var x = [];
	var y = [];

	var tickvals = [];
	var ticktext = [];

	var ratingInfo = extendedInfo.ratingInfo;

	var screenInterval;
	var outsideInterval;
	var outsideResolution;
	var specialTicks;
	var resolution;
	var displayConvert;
	var displayFormat;

	var gameMode = extendedInfo.missionInfo["gamemode"];
	//Base mode is the first one
	var baseMode = gameMode.split(" ")[0].toLowerCase();

	switch (baseMode) {
		case "null":
		case "quota":
		default:
			//1.5x par time should be enough to get the gist of the curve
			screenInterval = [parseInt(ratingInfo.time_offset), (parseInt(ratingInfo.par_time) === 0 ? ratingInfo.platinum_time * 4.0 : ratingInfo.par_time * 1.5)];
			outsideInterval = [Math.ceil(screenInterval[1] / 60000) * 60000, 6000000];
			outsideResolution = 60000;
			//Challenge time ticks
			specialTicks = [
				{label: "AT", time: ratingInfo.awesome_time},
				{label: "UT", time: ratingInfo.ultimate_time},
				{label: "PT", time: ratingInfo.platinum_time},
				{label: "Par", time: ratingInfo.par_time}
			];
			resolution = timeResolution;
			displayConvert = function(time) {
				return time / 1000;
			};
			displayFormat = function(time) {
				return formatTime(time);
			};
			break;
		case "hunt":
			screenInterval = [0, ratingInfo.awesome_score * 1.5];
			outsideInterval = [screenInterval[1], screenInterval[1] + 500];
			outsideResolution = 1;
			specialTicks = [
				{label: "Par", time: ratingInfo.par_score},
				{label: "PS", time: ratingInfo.platinum_score},
				{label: "US", time: ratingInfo.ultimate_score},
				{label: "AS", time: ratingInfo.awesome_score}
			];
			resolution = 1;
			displayConvert = function(time) {
				return time;
			};
			displayFormat = function(time) {
				return time;
			};
			break;
		case "gemmadness":
			if ((modifiers & modifierFlags.gemMadnessAll) === 0) {
				//Not all the gems
				screenInterval = [0, parseInt(ratingInfo.hunt_max_score) + 1];
				outsideInterval = [0, 0];
				outsideResolution = 1;
				specialTicks = [
					{label: "Par", time: ratingInfo.par_score},
					{label: "PS", time: ratingInfo.platinum_score},
					{label: "US", time: ratingInfo.ultimate_score},
					{label: "AS", time: ratingInfo.awesome_score}
				];
				if (parseInt(ratingInfo.awesome_score) !== parseInt(ratingInfo.hunt_max_score)) {
					specialTicks.push({label: "Max", time: ratingInfo.hunt_max_score});
				}
				resolution = 1;
				displayConvert = function(time) {
					return time;
				};
				displayFormat = function(time) {
					return time;
				};
			} else {
				//Yes all the gems
				screenInterval = [parseInt(ratingInfo.time_offset), parseInt(ratingInfo.par_time) + timeResolution];
				outsideInterval = [0, 0];
				outsideResolution = 1;
				specialTicks = [
					{label: "AT", time: ratingInfo.awesome_time},
					{label: "UT", time: ratingInfo.ultimate_time},
					{label: "PT", time: ratingInfo.platinum_time},
					{label: "Par", time: ratingInfo.par_time}
				];
				resolution = timeResolution;
				displayConvert = function(time) {
					return time / 1000;
				};
				displayFormat = function(time) {
					return formatTime(time);
				};
			}
			break;
	}

	//They always go in the tick list
	specialTicks.forEach(function(tick) {
		if (parseInt(tick.time) !== 0) {
			tickvals.push(displayConvert(tick.time));
			ticktext.push(tick.label);
		}
	});

	var nextSpecial = 0;

	for (var time = screenInterval[0]; time < screenInterval[1]; time += resolution) {
		//If we're about to hit a challenge time, create a special point for it so that the
		// bump looks as vertical as possible. Have to do this here because otherwise the line
		// will jump around all wonkily
		while (nextSpecial < specialTicks.length && time >= specialTicks[nextSpecial].time) {
			var tick = specialTicks[nextSpecial];
			//Don't add one if the time doesn't exist
			if (tick.time > 0) {
				x.push(displayConvert(tick.time - 1));
				y.push(getRating(tick.time - 1, extendedInfo, modifiers));

				//If this isn't the time we're about to add, add an after point
				if (parseInt(tick.time) !== time) {
					x.push(displayConvert(tick.time));
					y.push(getRating(tick.time, extendedInfo, modifiers));
				}
			}
			nextSpecial ++;
		}

		//Considered impossible
		var rating = getRating(time, extendedInfo, modifiers);
		if (rating < 0)
			continue;

		x.push(displayConvert(time));
		y.push(getRating(time, extendedInfo, modifiers));
	}
	for (time = outsideInterval[0]; time < outsideInterval[1]; time += outsideResolution) {
		x.push(displayConvert(time));
		y.push(getRating(time, extendedInfo, modifiers));
	}

	//Make some tick labels with actually formatted times, because plot.ly can't display
	// the custom time formatting that MB uses.

	//Pick the smallest interval for tick labels so that we don't get more than 10
	var tickSizes = [1, 2, 5, 10, 20, 50, 100, 200, 500, 1000, 2000, 5000, 10000, 15000, 30000, 60000, 120000, 150000, 300000, 600000];
	for (var i = 0; i < tickSizes.length; i ++) {
		var tickSize = tickSizes[i];
		var ticks = screenInterval[1] / tickSize;

		if (ticks < 11) {
			for (time = 0; time < screenInterval[1]; time += tickSize) {
				//Don't create ticks for the challenge times because that's redundant
				if (specialTicks.find(function(tick) {
						return tick.time === time;
					})) {
					continue;
				}

				tickvals.push(displayConvert(time));
				ticktext.push(displayFormat(time));
			}
			break;
		}
	}

	return {
		x: x,
		y: y,
		tickvals: tickvals,
		ticktext: ticktext,
		xrange: [0, displayConvert(screenInterval[1])],
		yrange: [0, Math.max(getRating(screenInterval[0], extendedInfo, modifiers), getRating(screenInterval[1], extendedInfo, modifiers))]
	};
}

/**
 * Update the plot.ly graph of rating vs time
 * @param extendedInfo Extended mission info obtained from GetRatingInfo.php
 * @param modifiers Bitfield of modifiers for rating
 */
function generateRatingPlot(extendedInfo, modifiers) {
	if (typeof(modifiers) === "undefined") {
		modifiers = 0;
	}

	//This function does all the work for us
	var ratingData = generateRatingData(extendedInfo, modifiers);

	Plotly.newPlot("ratingPlot", [{
		x: ratingData.x,
		y: ratingData.y
	}], {
		margin: {
			t: 0
		},
		xaxis: {
			title: "Final Score",
			tickvals: ratingData.tickvals,
			ticktext: ratingData.ticktext,
			range: ratingData.xrange
		},
		yaxis: {
			title: "Rating Points",
			range: ratingData.yrange
		},
		textfont: {
			color: '#ffffff'
		},
		fillcolor: 'rgb(0, 0, 0)'
	});
}

var currentModifier = 0;

var columnNames = {
	"par_time":              "Par Time",
	"platinum_time":         "Platinum Time",
	"ultimate_time":         "Ultimate Time",
	"awesome_time":          "Awesome Time",
	"quota_all_bonus":       "Quota 100% Bonus",
	"platinum_bonus":        "Platinum Bonus",
	"ultimate_bonus":        "Ultimate Bonus",
	"awesome_bonus":         "Awesome Bonus",
	"completion_bonus":      "Completion Bonus",
	"set_base_score":        "Set Base Score",
	"multiplier_set_base":   "Multiplier Set Base",
	"standardiser":          "Standardiser",
	"time_offset":           "Minimum Time",
	"difficulty":            "Difficulty",
	"platinum_difficulty":   "Platinum Difficulty",
	"ultimate_difficulty":   "Ultimate Difficulty",
	"awesome_difficulty":    "Awesome Difficulty",
	"par_score":             "Par Score",
	"platinum_score":        "Platinum Score",
	"ultimate_score":        "Ultimate Score",
	"awesome_score":         "Awesome Score",
	"hunt_multiplier":       "Hunt Multiplier",
	"hunt_divisor":          "Hunt Divisor",
	"hunt_completion_bonus": "Hunt Completion Bonus",
	"hunt_par_bonus":        "Hunt Par Bonus",
	"hunt_platinum_bonus":   "Hunt Platinum Bonus",
	"hunt_ultimate_bonus":   "Hunt Ultimate Bonus",
	"hunt_awesome_bonus":    "Hunt Awesome Bonus",
	"quota_100_bonus":       "Quota 100% Bonus",
	"revert":                "Revert Changes"
};

//Two rows of three columns of many fields
var ratingColumns = [
	[
		[
			{"field": "par_time", "type": "text"},
			{"field": "platinum_time", "type": "text"},
			{"field": "ultimate_time", "type": "text"},
			{"field": "awesome_time", "type": "text"}
		],
		[
			{"field": "completion_bonus", "type": "text"},
			{"field": "platinum_bonus", "type": "text"},
			{"field": "ultimate_bonus", "type": "text"},
			{"field": "awesome_bonus", "type": "text"}
		],
		[
			{"field": "set_base_score", "type": "text"},
			{"field": "multiplier_set_base", "type": "text"},
			{"field": "standardiser", "type": "text"},
			{"field": "time_offset", "type": "text"}
		],
		[
			{"field": "difficulty", "type": "text"},
			{"field": "platinum_difficulty", "type": "text"},
			{"field": "ultimate_difficulty", "type": "text"},
			{"field": "awesome_difficulty", "type": "text"}
		]
	],
	[
		[
			{"field": "par_score", "type": "text"},
			{"field": "platinum_score", "type": "text"},
			{"field": "ultimate_score", "type": "text"},
			{"field": "awesome_score", "type": "text"}
		],
		[
			{"field": "hunt_multiplier", "type": "text"},
			{"field": "hunt_divisor", "type": "text"},
			{"field": "hunt_completion_bonus", "type": "text"},
			{"field": "quota_100_bonus", "type": "text"}
		],
		[
			{"field": "hunt_par_bonus", "type": "text"},
			{"field": "hunt_platinum_bonus", "type": "text"},
			{"field": "hunt_ultimate_bonus", "type": "text"},
			{"field": "hunt_awesome_bonus", "type": "text"}
		]
	],
	[
		[
			{"field": "revert", "type": "button"}
		]
	]
];

/**
 * Update the text field matrix of rating constants
 * @param extendedInfo Extended mission info obtained from GetRatingInfo.php
 */
function updateRatingInfo(extendedInfo) {
	//Copy this for the revert button
	var savedInfo = JSON.stringify(extendedInfo);

	var infoDiv = $("#ratingInfo");
	infoDiv.empty();

	ratingColumns.forEach(function(row) {
		var rowDiv = $("<div></div>")
			.addClass("row");

		var colClass = "col-md-" + Math.floor(12 / row.length);

		row.forEach(function(column) {
			var columnDiv = $("<div></div>")
				.addClass(colClass);

			var form = $("<form></form>")
				.addClass("form-inline")
				.attr("action", "javascript:void(0)"); //So the buttons don't make us redirect

			column.forEach(function(field) {
				//Revert button has its own special case
				var colName = columnNames[field.field];
				switch (field.type) {
				case "button":
					var button;
					var revertDiv = $("<div>")
						.addClass("ratingBox")
						.append(button = $("<button>")
							.addClass("btn")
							.addClass(field.field === "revert" ? "btn-danger" : "btn-success")
							.text(colName)
							);

					if (field.field === "revert") {
						button.click(function() {
							//Just swap out with the saved version
							extendedInfo = JSON.parse(savedInfo);
							generateRatingPlot(extendedInfo, currentModifier);
							updateRatingInfo(extendedInfo);
						});
					}

					form.append(revertDiv);
					break;
				case "text":
					var fieldId = "rating-" + field.field;
					var fieldDiv = $("<div>")
						.addClass("ratingBox")
						.append($("<label></label>")
							.attr("for", fieldId)
							.text(colName))
						.append($("<input>")
							.attr("id", fieldId)
							.addClass("input")
							.addClass("form-control")
							.addClass("ratingInput")
							.val(extendedInfo.ratingInfo[field.field])
							.change(function() {
								//Swap out the field with the new value they entered and regen the plot
								extendedInfo.ratingInfo[field.field] = $(this).val();
								generateRatingPlot(extendedInfo, currentModifier);

								if (field.required) {
									if (parseInto(extendedInfo.ratingInfo[field.field]) === 0) {
										fieldDiv.addClass("has-error");
									} else {
										fieldDiv.removeClass("has-error");
									}
								}
							}));
					if (field.required && parseInt(extendedInfo.ratingInfo[field.field]) === 0) {
						fieldDiv.addClass("has-error");
					}

					form.append(fieldDiv);
					break;
				case "textarea":
					var fieldId = "rating-" + field.field;
					var fieldDiv = $("<div>")
						.addClass("ratingBox")
						.append($("<label></label>")
							.attr("for", fieldId)
							.text(colName))
						.append($("<textarea>")
							.attr("id", fieldId)
							.addClass("input")
							.addClass("form-control")
							.addClass("ratingInput")
							.text(extendedInfo.ratingInfo[field.field])
							.change(function() {
								//Swap out the field with the new value they entered and regen the plot
								extendedInfo.ratingInfo[field.field] = $(this).val();
							}));
					if (field.required && parseInt(extendedInfo.ratingInfo[field.field]) === 0) {
						fieldDiv.addClass("has-error");
					}

					form.append(fieldDiv);
					break;
				}
			});
			columnDiv.append(form);
			rowDiv.append(columnDiv);
		});
		infoDiv.append(rowDiv);
	});
}

function updateModifiers(extendedInfo) {
	var modifiersDiv = $("#ratingModifiers");
	modifiersDiv.empty();

	var listDiv = $("<div>")
		.addClass("row");

	modifierList.forEach(function(modifier) {
		var fieldId = "modifier-" + modifier.field;
		var modifierDiv = $("<div>")
			.addClass("col-md-2")
			.append($("<input>")
				.attr("type", "checkbox")
				.attr("id", fieldId)
				.change(function() {
					var selected = $(this).is(":checked");
					var flag = modifier.flag;
					if (selected) {
						currentModifier |= flag;
					} else {
						currentModifier &= ~flag;
					}
					generateRatingPlot(extendedInfo, currentModifier);
				})
			)
			.append(" ")
			.append($("<label>")
				.attr("for", fieldId)
				.text(" " + modifier.name)
			);
		listDiv.append(modifierDiv);
	});

	modifiersDiv.append(listDiv);
}

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
	//Update selector
	var selectMission = $("#selectMission");
	selectMission.children(":selected").attr("selected", null);
	selectMission.children("[data-mission-id=" + missionId + "]").attr("selected", "selected");

	//Rating info is in another file but we can just get it from the server
	$.ajax({
		method: "POST",
		url: "GetRatingInfo.php",
		dataType: "json",
		data: {
			missionId: missionId
		}
	}).done(function(extendedInfo) {
		//Gives us an extended mission info
		currentModifier = 0;
		updateModifiers(extendedInfo);
		generateRatingPlot(extendedInfo, currentModifier);
		updateRatingInfo(extendedInfo);
	});

	window.location.hash = "gameId=" + gameId + "&difficultyId=" + difficultyId + "&missionId=" + missionId;
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
	//Such a convenient file
	$.ajax({
		method: "POST",
		url: config.base + "/api/Mission/GetMissionList.php",
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
			if (matches.length > 0) {
				gameId = parseInt(matches[1]);
			}
			re = new RegExp("difficultyId=(.*?)(&|#|$)", "i");
			matches = hash.match(re);
			if (matches.length > 0) {
				difficultyId = parseInt(matches[1]);
			}
			re = new RegExp("missionId=(.*?)(&|#|$)", "i");
			matches = hash.match(re);
			if (matches.length > 0) {
				missionId = parseInt(matches[1]);
			}
		}

		onSelectGame(gameId);
		onSelectDifficulty(gameId, difficultyId);
		onSelectMission(gameId, difficultyId, missionId);

		//Generate the resolution list
		var selectTime = $("#timeResolution");
		var resolutions = [
			{step: 1, label: "1ms (Slow!)"}, //Why
			{step: 10, label: "10ms"},
			{step: 100, label: "100ms", selected: true},
			{step: 1000, label: "1s"}
		];

		resolutions.forEach(function(resolution) {
			selectTime.append(
				$("<option></option>")
					.text(resolution.label)
					.attr("data-time-resolution", resolution.step)
					.attr("selected", (resolution.selected ? "selected" : null))
			);
		});

		selectTime.change(function() {
			var selectMission = $("#selectMission");

			//We can just steal these from selectMission, how nice
			var gameId = parseInt(selectMission.children(":selected").attr("data-game-id"));
			var difficultyId = parseInt(selectMission.children(":selected").attr("data-difficulty-id"));
			var missionId = parseInt(selectMission.children(":selected").attr("data-mission-id"));
			timeResolution = parseInt(selectTime.children(":selected").attr("data-time-resolution"));

			//And pretend we updated the mission so we get a better graph
			onSelectMission(gameId, difficultyId, missionId);
		});
	});
}

//Start her up
getMissionList();
