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
    header("Location: https://marbleblast.com/pq/ratings/RatingsViewer.php");
	header("HTTP/1.1 301 Moved Permanently");
	die();
}
?>
<html id="nightMode">
	<head>
		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

		<!-- Plot.ly -->
		<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

        <!-- Handlebars -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.10/handlebars.min.js"></script>

		<link rel="stylesheet" href="RatingsViewer.css">

		<script type="text/javascript">
			config = {
				"base": "https://marbleblast.com/pq/leader"
			}
		</script>

		<title>Rating Editor</title>
	</head>
	<body>
		<form action="javascript:void(0);" class="form-inline" id="selectorsForm">
			<div class="row">
				<div class="col-md-11">
					<div class="row">
						<div class="col-md-3">
							<label for="selectGame">Game: </label>
							<select class="form-control" name="selectGame" id="selectGame"></select>
						</div>
						<div class="col-md-3">
							<label for="selectDifficulty">Difficulty: </label>
							<select class="form-control" name="selectDifficulty" id="selectDifficulty"></select>
						</div>
						<div class="col-md-4">
							<label for="selectMission">Mission: </label>
							<select class="form-control" name="selectMission" id="selectMission"></select>
						</div>
						<div class="col-md-2">
							<label for="timeResolution">Resolution: </label>
							<select class="form-control" name="timeResolution" id="timeResolution"></select>
						</div>
					</div>
				</div>
				<div class="col-md-1">
					<button class="btn" data-toggle="modal" data-target="#allChangeLogsModal">All Changes</button>
				</div>
			</div>
		</form>
		<div id="ratingModifiers"></div>
        <div id="rrecDownloads" class="row" style="margin: 20px auto;">
            <ul id="rrecDownloadReplay" class="col-md-6"></ul>
            <ul id="rrecDownloadEggReplay" class="col-md-6"></ul>
        </div>
        <div id="topScoresPanel">
            <table class="table table-rounded table-striped" id="topScoresTable"></table>
            <script id="topScoresHeaderTemplate" type="text/x-handlebars-template">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Score</th>
                    <th>Type</th>
                    <th>Rating</th>
                    <th>Bonus Time</th>
                    <th>Gem Count</th>
                    <th>Red Gems</th>
                    <th>Yellow Gems</th>
                    <th>Blue Gems</th>
                    <th>Platinum Gems</th>
                    <th>Timestamp</th>
                    <th>Origin</th>
                    <th></th>
                </tr>
            </script>
            <script id="topScoresAdderTemplate" type="text/x-handlebars-template">
                <tr id="topScoresAdder">
                    <td>New</td>
                    <td><input type="text" class="form-control" data-attr="username"></td>
                    <td><input type="text" class="form-control" data-attr="score"></td>
                    <td id="topScoresAdderScoreType"></td>
                    <td id="topScoresAdderRating"></td>
                    <td><input type="text" class="form-control" data-attr="total_bonus"></td>
                    <td><input type="text" class="form-control" data-attr="gem_count"></td>
                    <td><input type="text" class="form-control" data-attr="gems_1_point"></td>
                    <td><input type="text" class="form-control" data-attr="gems_2_point"></td>
                    <td><input type="text" class="form-control" data-attr="gems_5_point"></td>
                    <td><input type="text" class="form-control" data-attr="gems_10_point"></td>
                    <td>Now</td>
                    <td>Ratings Viewer</td>
                    <td><button class="btn add">Add</button></td>
                </tr>
            </script>
            <script id="topScoresTemplate" type="text/x-handlebars-template">
                <tr>
                    <td>{{index}}</td>
                    <td>{{username}}</td>
                    <td>{{score}}</td>
                    <td>{{score_type}}</td>
                    <td>{{rating}}</td>
                    <td>{{total_bonus}}</td>
                    <td>{{gem_count}}</td>
                    <td>{{gems_1_point}}</td>
                    <td>{{gems_2_point}}</td>
                    <td>{{gems_5_point}}</td>
                    <td>{{gems_10_point}}</td>
                    <td>{{timestamp}}</td>
                    <td>{{origin}}</td>
                    <td><button class="btn delete" data-id="{{id}}">Delete</button></td>
                </tr>
            </script>
        </div>
        <div id="ratingPanel">
            <div id="ratingPlot"></div>
            <div id="ratingInfo"></div>
            <div id="ratingChangeLog" class="row"></div>
        </div>
		<div id="allChangeLogsModal" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">All Level Changes</h4>
					</div>
					<div class="modal-body">
						<div class="row" id="allChangeLogs">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<script src="RatingsViewer.js"></script>
	</body>
</html>
