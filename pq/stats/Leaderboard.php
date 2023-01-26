<html id="nightMode">
<head>
	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.4.1"></script>
    <script src="fuzzycomplete.min.js"></script>

    <!-- Plot.ly -->
	<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<link rel="stylesheet" href="Leaderboard.css">

	<script type="text/javascript">
		config = {
			"base": "https://marbleblast.com/pq/leader"
		}
	</script>

	<title>PQ Leaderboards</title>
</head>
<body>
<form action="javascript:void(0);" class="form-inline container-fluid" id="selectorsForm">
    <div class="row">
        <div class="col-lg-4">
            <label for="selectGame">Game: </label>
            <select class="form-control" name="selectGame" id="selectGame"></select>
        </div>
        <div class="col-lg-4">
            <label for="selectDifficulty">Difficulty: </label>
            <select class="form-control" name="selectDifficulty" id="selectDifficulty"></select>
        </div>
        <div class="col-lg-4">
            <label for="selectMission">Mission: </label>
            <select class="form-control" name="selectMission" id="selectMission"></select>
        </div>
        <div class="col-lg-3" style="display: none">
            <label for="searchBox">Search: </label>
            <input class="form-control" type="text" id="searchBox">
        </div>
	</div>
</form>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-2" id="settings">
            <h3>Show Columns</h3>
            <div id="columns"></div>
        </div>
        <div class="col-lg-10" id="scores">

        </div>
    </div>
</div>
<script src="Leaderboard.js"></script>
</body>
</html>
