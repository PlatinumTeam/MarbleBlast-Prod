<?php
define("MAX_PLAYERS", 2);

$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("MultiPlayer Rating Tester");

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

if ($access > (MINIMUM_ACCESS - 1)) {
	?>

<script type="text/javascript">
var maxPlayers = <?php echo(MAX_PLAYERS); ?>;

function updateRatings() {
	for (var i = 0; i < maxPlayers; i ++) {
		var oldrating = parseFloat($("#prev" + (i + 1)).val());
		var finalrating = 0;
		var change = 0;
		var placement = parseInt($("#place" + (i + 1)).val());
		var opponents = 0;
		var games = parseInt($("#provgames" + (i + 1)).val());
		if (games != games)
			games = 0;
		var provis = games < 20;
		var handicap = parseInt($("#handicaps" + (i + 1)).val());

		$("#games" + (i + 1)).html("");

		if ($("#prev" + (i + 1)).val() === "")
			continue;

		for (var j = 0; j < maxPlayers; j ++) {
			if (j == i)
				continue;
			if ($("#prev" + (j + 1)).val() === "")
				continue;

			var oppplacement = parseInt($("#place" + (j + 1)).val());
			var oppgames = parseInt($("#provgames" + (j + 1)).val());
			if (oppgames != oppgames)
				oppgames = 0;
			var oppprovis = oppgames < 20;
			var winloss = 0.5;
			var opprating = parseFloat($("#prev" + (j + 1)).val());
			var opphandicap = parseInt($("#handicaps" + (j + 1)).val());

			if (oppplacement > placement)
				winloss = 1;
			if (oppplacement < placement)
				winloss = 0;

			var newrating = 0;

			var handicapDiff = (oppplacement > placement ? handicap - opphandicap : opphandicap - handicap);

			if (!provis && !oppprovis)
				newrating = 32 + handicapDiff;
			if (!provis && oppprovis)
				newrating = (16+(16*(oppgames/20))) + handicapDiff;
			if (provis && !oppprovis)
				newrating = (16+(16*(games/20))) + handicapDiff;
			if (provis && oppprovis)
				newrating = (16+(16*((oppgames + games)/40))) + handicapDiff;

			newrating *= (winloss-(1/(1+Math.pow(10, (opprating - oldrating) / 400))));
			change += newrating;
			opponents ++;

			$("#games" + (i + 1)).html($("#games" + (i + 1)).html() + (newrating > 0 ? "+" : "") + round3(newrating) + ", ");
		}

		change /= (opponents == 0 ? 1 : opponents);
		finalrating = oldrating + change;
		$("#final" + (i + 1)).html(round3(finalrating));
		$("#change" + (i + 1)).html((change > 0 ? "+" : "") + round3(change));
	}
}
function round3(float) {
	return Math.round(float);
}

function addPlayer() {
   maxPlayers ++;
   $("#table").append("<tr id=\"row" + maxPlayers + "\"><td><input id=\"place" + maxPlayers + "\" type=\"text\" onkeyup=\"updateRatings();\" class=\"input-small\" value=\"" + maxPlayers + "\"></td><td><input id=\"prev" + maxPlayers + "\" type=\"text\" onkeyup=\"updateRatings();\" class=\"input-small\" value=\"1500\"></td><td><input id=\"provgames" + maxPlayers + "\" type=\"text\" onkeyup=\"updateRatings();\" class=\"input-small\" value=\"20\"></td><td id=\"games" + maxPlayers + "\"></td><td id=\"change" + maxPlayers + "\"></td><td id=\"final" + maxPlayers + "\"></td><td><input id=\"delete" + maxPlayers + "\" type=\"submit\" onclick=\"removePlayer(" + maxPlayers + ");\" class=\"btn btn-danger\" value=\"&ndash;\"></td></tr>");
   updateRatings();
}

function removePlayer(player) {
   $("#row" + player).remove();
   for (var i = player; i < maxPlayers; i ++) {
      $("#place"     + (i + 1)).attr("id", "place"     + i);
      $("#row"       + (i + 1)).attr("id", "row"       + i);
      $("#prev"      + (i + 1)).attr("id", "prev"      + i);
      $("#provgames" + (i + 1)).attr("id", "provgames" + i);
      $("#games"     + (i + 1)).attr("id", "games"     + i);
      $("#change"    + (i + 1)).attr("id", "change"    + i);
      $("#final"     + (i + 1)).attr("id", "final"     + i);
      $("#delete"    + (i + 1)).attr("id", "delete"    + i);
      $("#delete"    + i).attr("onclick", "removePlayer(" + i + ");");
   }
   maxPlayers --;
   updateRatings();
}

setTimeout(updateRatings, 1000);

</script>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">MultiPlayer Rating Tester</h1>
			<br>
			<br>
			<table id="table" class="table table-rounded table-bordered">
				<tr>
					<th>
						Final Placement
					</th>
					<th>
						Previous Rating
					</th>
					<th>
						Played Games
					</th>
					<th>
						Handicaps
					</th>
					<th>
						Game Scores
					</th>
					<th>
						Rating Change
					</th>
					<th>
						Final Rating
					</th>
					<th>
					   Delete
               </th>
				</tr>
<?php
for ($i = 0; $i < MAX_PLAYERS; $i ++) { ?>
				<tr id="row<?php echo($i+1);?>">
					<td><input id="place<?php echo($i+1);?>"     type="text" onkeyup="updateRatings();" class="input-small" value="<?php echo($i+1);?>"   ></td>
					<td><input id="prev<?php echo($i+1);?>"      type="text" onkeyup="updateRatings();" class="input-small" value="1500"></td>
					<td><input id="provgames<?php echo($i+1);?>" type="text" onkeyup="updateRatings();" class="input-small" value="20"  ></td>
					<td><input id="handicaps<?php echo($i+1);?>" type="text" onkeyup="updateRatings();" class="input-small" value="0"  ></td>
					<td id="games<?php echo($i+1);?>"></td>
					<td id="change<?php echo($i+1);?>"></td>
					<td id="final<?php echo($i+1);?>"></td>
					<td><input id="delete<?php echo($i+1);?>"    type="submit" onclick="removePlayer(<?php echo($i+1);?>);" class="btn btn-danger" value="&ndash;"></td>
				</tr>
<?php } ?>
			</table>
			<button class="btn btn-success" onclick="addPlayer();">Add Player (+)</button>
		</div>
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
