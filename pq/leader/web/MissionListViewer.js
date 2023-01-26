var missionInfoList;

function getMissionList() {
	var gameList = $("#gameList");
	gameList.empty();

	//Such a convenient file
	$.ajax({
		method: "POST",
		url: config.base + "/api/Mission/GetMissionList.php",
		dataType: "json"
	}).done(function(data) {
		missionInfoList = data;

		//Create the game list now since it never changes
		data.gameIds.forEach(function(gameInfo) {
			var gameDisplay = gameInfo.display;
			var gameId = gameInfo.id;

			var gameRow;
			var gameTitle;
			var difficultyList;

			gameList.append(
				gameRow = $("<div></div>")
					.addClass("gameRow")
					.attr("data-game-id", gameId)
					.append(
						gameTitle = $("<div></div>")
							.addClass("gameTitle")
							.text(gameDisplay)
							.click(function(){
								difficultyList.toggle();
								gameRow.toggleClass("expanded");
							})
					)
					.append(
						difficultyList = $("<div></div>")
							.addClass("difficultyList")
							.attr("data-game-id", gameId)
					)
			);

			data.difficulties[gameId].forEach(function(difficultyId) {
				//For some reason this is necessary because there's no associative list
				// of difficulty infos.
				var difficultyInfo = data.difficultyIds.find(function(testDifficulty) {
					return testDifficulty.id == difficultyId;
				});
				var difficultyDisplay = difficultyInfo.display;

				var difficultyCol;
				var difficultyTitle;
				var missionList;

				difficultyList.append(
					difficultyCol = $("<div></div>")
						.addClass("difficultyCol")
						.attr("data-game-id", gameId)
						.attr("data-difficulty-id", difficultyId)
						.append(
							difficultyTitle = $("<div></div>")
								.addClass("difficultyTitle")
								.text(difficultyDisplay)
								.click(function(){
									missionList.toggle();
									difficultyCol.toggleClass("expanded");
								})
						)
						.append(
							missionList = $("<ol></ol>")
								.addClass("missionList")
								.attr("data-game-id", gameId)
								.attr("data-difficulty-id", difficultyId)
						)
				);
				data.missions[gameId][difficultyId].forEach(function(missionInfo) {
					var missionId = missionInfo.id;
					var missionDisplay = missionInfo.name;

					var missionTitle;

					missionList.append(
						missionTitle = $("<li></li>")
							.addClass("missionTitle")
							.text(missionDisplay)
							.attr("data-game-id", gameId)
							.attr("data-difficulty-id", difficultyId)
							.attr("data-mission-id", missionId)
					);
				});

				missionList.hide();
			});
			difficultyList.hide();
			difficultyList.children(".difficultyCol").children(".missionList").sortable({
				connectWith: ".missionList[data-game-id=" + gameId + "]",
				placeholder: "sortable-placeholder",
				stop: function(event, ui) {
					var gameId = ui.item.attr("data-game-id");
					var difficultyId = ui.item.attr("data-difficulty-id");
					var missionId = ui.item.attr("data-mission-id");

					var newDifficultyId = ui.item.parent().attr("data-difficulty-id");
					var newSortPosition = ui.item.index();

					var missionInfo = data.missions[gameId][difficultyId].find(function(testMission) {
						if (testMission.id === missionId)
							return testMission;
					});

					onMoveMission(missionInfo, newDifficultyId, newSortPosition);
				}
			}).disableSelection();
		});
	});
}

/**
 * Generate some info and tell the database we've moved stuff around
 * @param missionInfo The moved mission's info
 * @param newDifficultyId The new difficulty for the mission
 * @param newSortIndex The new index for sorting the mission
 */
function onMoveMission(missionInfo, newDifficultyId, newSortIndex) {
	var changes = {};

	//Update every mission after this one in the category we've
	var oldDifficultyId = missionInfo.difficulty_id;
	var gameId = missionInfo.game_id;

	//Find its position
	var oldSortIndex = missionInfoList.missions[gameId][oldDifficultyId].findIndex(function(testMission) {
		return testMission.id === missionInfo.id;
	});
	if (oldDifficultyId === newDifficultyId) {
		//No change in difficulty
		changes[missionInfo.id] = {
			name: missionInfo.name,
			oldSortIndex: oldSortIndex + 1,
			newSortIndex: newSortIndex + 1
		};
		//Reorder a few things in this difficulty
		missionInfoList.missions[gameId][oldDifficultyId].forEach(function(mission, index) {
			if (index > oldSortIndex && index <= newSortIndex) {
				changes[mission.id] = {
					name: mission.name,
					oldSortIndex: index + 1,
					newSortIndex: index
				};
			}
		});
	} else {
		//Remove it from the list
		missionInfoList.missions[gameId][oldDifficultyId].splice(oldSortIndex, 1);

		changes[missionInfo.id] = {
			name: missionInfo.name,
			newDifficultyId: newDifficultyId,
			oldSortIndex: oldSortIndex + 1,
			newSortIndex: newSortIndex + 1
		};
		//Reorder everything after in this difficulty
		missionInfoList.missions[gameId][oldDifficultyId].forEach(function(mission, index) {
			if (index >= oldSortIndex) {
				changes[mission.id] = {
					name: mission.name,
					oldSortIndex: index + 2,
					newSortIndex: index + 1
				};
			}
		});
		//Reorder everything after it in the new difficulty
		missionInfoList.missions[gameId][newDifficultyId].forEach(function(mission, index) {
			if (index >= newSortIndex) {
				changes[mission.id] = {
					name: mission.name,
					oldSortIndex: index + 1,
					newSortIndex: index + 2
				}
			}
		});
	}

	$.ajax({
		method: "POST",
		url: config.base + "/web/UpdateMissionOrder.php",
		data: {changes: changes}
	}).done(function(data) {
		console.log(data);
	});
}

//Start her up
getMissionList();