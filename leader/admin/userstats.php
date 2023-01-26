<?php

$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Tracking Data");

	//-------------------------------------------------------------------
	// Navbar start

	navbarCreate("MBP Admin");
	navbarAddSet("default");
	if ($access > 1) {
		navbarAddSet("admin");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	}
	navbarAddItem("Access: " . accessTitle($access));
	navbarEnd();

	// Navbar end
	//------------------------------------––––––––––––––––––––––––––––---

if ($access > 1) {

	$user = "lzx";

	$query = pdo_prepare("SELECT `username`, `level`, AVG(`place`) AS `avplace`, AVG(`score`) AS `avscore`, SUM(`change`) AS `totchange`, COUNT(*) AS `count`
	FROM `serverscores` AS `scores0` WHERE `username` = :user AND `players` > 1 AND `custom` = 0 GROUP BY `level`");
	$query->bind(":user", $user);
	$results = $query->execute()->fetchAll();

	?>

<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

// Load the Visualization API and the piechart package.
google.load('visualization', '1.1', {'packages':['bar']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawCharts);

function drawCharts() {
	drawUser0();
}
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">User Stats for <?=getDisplayName($user)?></h1>
			<br>
			<br>
<div class="text-center">
<h3>MBG Levels</h3>
<div id="user0scores"></div>
<script type="text/javascript">
function drawUser0() {
	// Create the data table.
	var data = new google.visualization.arrayToDataTable([
	['Level Name', 'Place'],	
<?php
foreach ($results as $row) {
	echo("['" . addSlashes($row['level']) . "', {$row["totchange"]}],\n");
}
?>
	]);
	var options = {'title':'Overall Average Place',
		'backgroundColor': '#f5f5f5',
		'series': {
			0: { axis: 'place' },
		},
		'legend': {
			position: 'top',
			maxLines: 3
		},
		'width': 800,
		'height': 400,
		'orientation': 'vertical',
		'axes': {
			'y': {
				'place': {
					'label': "Average Place"
				},
			}
		}
	};
	var chart = new google.charts.Bar(document.getElementById('user0scores'));
	chart.draw(data, options);
}
</script>
		</div>
	</div>

	<?php
} else {
	accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
