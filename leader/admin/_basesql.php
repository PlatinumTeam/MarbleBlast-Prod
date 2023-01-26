<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0 && array_key_exists("sub", $_POST)) {
	$result = $lb_connection->query($_POST["query"]);
	if ($result) {
		if ($result->rowCount()) {
			echo("Found rows: " . $result->rowCount() . "\n");
			$i = 0;
			while (($row = $result->fetch()) !== false) {
				$keys = array_keys($row);
				if ($i == 0) {
					echo("Columns: ");
					for ($k = 0; $k < count($keys); $k ++) {
						if ($k > 0) echo(" | ");
						echo($keys[$k] . " ");
					}
					echo("\n\n");
				}
				for ($k = 0; $k < count($keys); $k ++) {
					if ($k > 0) echo(" | ");
					echo($row[$keys[$k]]);
				}
				echo("\n");
				$i ++;
			}
		} else {
		   echo("No rows modified.");
		}
	} else {
		echo("Result is null");
	}
	die();
}

//----------------------------------------------------------------------
// Document start
documentHeader("Ban Player");

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

	if ($access > 1) { ?>

<style type="text/css">
#output {
	font-family: Menlo, Courier;
	font-size: 11pt;
}
</style>

<script type="text/javascript">
function doQuery() {
	var btn = $("#query");
	var query = encodeURIComponent(btn.val());
	btn.attr("disabled", "disabled");
	$.post("_basesql.php", "query=" + query + "&sub=1",
		function (data) {
			btn.attr("disabled", null);
			$("#output").html(data.replace(/\n/gi, "<br>"));
			$("#output").removeClass("hidden");
		}
	);
}
</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Evaluate SQL</h1>
			<br>
			<br>
			<form action="javascript:void(0);">
				<input type="text" class="input input-xxlarge" placeholder="Query" id="query"><br>
				<button class="btn btn-warning" onclick="doQuery();">Submit</button>
			</form>
			<div id="output" class="well hidden">
			</div>
		</div>
	</div>
</div>
	<?php }

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
