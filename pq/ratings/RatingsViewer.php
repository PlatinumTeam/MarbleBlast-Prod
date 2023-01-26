<html id="nightMode">
	<head>
		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

		<!-- Plot.ly -->
		<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

		<link rel="stylesheet" href="RatingsViewer.css">

		<script type="text/javascript">
			config = {
				"base": "https://marbleblast.com/pq/leader"
			}
		</script>

		<title>Rating Viewer</title>
	</head>
	<body>
		<form action="javascript:void(0);" class="form-inline" id="selectorsForm">
			<div class="row">
				<div class="col-md-12">
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
			</div>
		</form>
		<div id="ratingPlot"></div>
		<div id="ratingModifiers"></div>
		<div id="ratingInfo"></div>
		<script src="RatingsViewer.js"></script>
	</body>
</html>
