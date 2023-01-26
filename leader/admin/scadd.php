<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Create Super Challenge");

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
	?>

<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well">
			<h1 class="text-center">Create Super Challenge</h1>
			<br>
			<?php if (isset($_GET["error"])) {?>
			<div class="alert alert-danger">
				There was an error creating the super challenge. The error was: <b><?php
				switch ($_GET["error"]) {
					case 0: echo("There was an internal server error"); break;
					case 1: echo("Please specify a name for the super challenge"); break;
					case 2: echo("The super challenge name cannot be only symbols"); break;
					case 3: echo("A super challenge with that name already exists"); break;
				}
				?></b>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
			<?php } ?>
			<br>
			<form action="doscadd.php" method="POST" class="form" autocomplete="off">
				<table class="table">
					<tbody>
						<tr>
							<th>Field Name</th>
							<th>Field Value</th>
						</tr>
						<?php
						$tabledata = array(
	                   array("Name" => "Name",                "Default" => "",  "Internal" => "display",          "Append" => ""),
	                   array("Name" => "Missions",            "Default" => "",  "Internal" => "missions",         "Append" => ""),
	                   array("Name" => "Platinum Percentage", "Default" => 0.5, "Internal" => "platinumPercent",  "Append" => " / 1.0"),
	                   array("Name" => "Ultimate Percentage", "Default" => 0.8, "Internal" => "ultimatePercent",  "Append" => " / 1.0"),
	                   array("Name" => "Minimum Timeout",     "Default" => 5,   "Internal" => "minTimeout",       "Append" => "%"),
	                   array("Name" => "Maximum Timeout",     "Default" => 10,  "Internal" => "maxTimeout",       "Append" => "%"),
	                   array("Name" => "Bitmap Image",        "Default" => "",  "Internal" => "bitmap",           "Append" => ".png"),
	                   array("Name" => "Disabled",            "Default" => 0,   "Internal" => "disabled",         "Append" => ""),
	                   array("Name" => "Points for 2 Players",                  "Internal" => ""),
	                   array("Name" => "Winning",             "Default" => 5,   "Internal" => "points_2_win",     "Append" => "Points"),
	                   array("Name" => "Tie",                 "Default" => 3,   "Internal" => "points_2_tie",     "Append" => "Points"),
	                   array("Name" => "Losing",              "Default" => 1,   "Internal" => "points_2_lose",    "Append" => "Points"),
	                   array("Name" => "Forfeit",             "Default" => -1,  "Internal" => "points_2_forfeit", "Append" => "Points"),
	                   array("Name" => "Platinum Percentage", "Default" => 1,   "Internal" => "points_2_plat",    "Append" => "Points"),
	                   array("Name" => "Ultimate Percentage", "Default" => 3,   "Internal" => "points_2_ult",     "Append" => "Points"),
	                   array("Name" => "Points for 3 Players",                  "Internal" => ""),
	                   array("Name" => "Winning",             "Default" => 7,   "Internal" => "points_3_win",     "Append" => "Points"),
	                   array("Name" => "Tie (All)",           "Default" => 5,   "Internal" => "points_3_tieall",  "Append" => "Points"),
	                   array("Name" => "Tie (1st Place)",     "Default" => 3,   "Internal" => "points_3_tie1",    "Append" => "Points"),
	                   array("Name" => "Tie (2nd Place)",     "Default" => 2,   "Internal" => "points_3_tie2",    "Append" => "Points"),
	                   array("Name" => "Losing (2nd Place)",  "Default" => 3,   "Internal" => "points_3_lose2",   "Append" => "Points"),
	                   array("Name" => "Losing (3rd Place)",  "Default" => 1,   "Internal" => "points_3_lose3",   "Append" => "Points"),
	                   array("Name" => "Forfeit",             "Default" => -2,  "Internal" => "points_3_forfeit", "Append" => "Points"),
	                   array("Name" => "Platinum Percentage", "Default" => 2,   "Internal" => "points_3_plat",    "Append" => "Points"),
	                   array("Name" => "Ultimate Percentage", "Default" => 4,   "Internal" => "points_3_ult",     "Append" => "Points"),
	                   array("Name" => "Points for 4 Players",                  "Internal" => ""),
	                   array("Name" => "Winning",             "Default" => 10,  "Internal" => "points_4_win",     "Append" => "Points"),
	                   array("Name" => "Tie (All)",           "Default" => 7,   "Internal" => "points_4_tieall",  "Append" => "Points"),
	                   array("Name" => "Tie (1st Place)",     "Default" => 7,   "Internal" => "points_4_tie1",    "Append" => "Points"),
	                   array("Name" => "Tie (2nd Place)",     "Default" => 4,   "Internal" => "points_4_tie2",    "Append" => "Points"),
	                   array("Name" => "Tie (3rd Place)",     "Default" => 2,   "Internal" => "points_4_tie3",    "Append" => "Points"),
	                   array("Name" => "Losing (2nd Place)",  "Default" => 7,   "Internal" => "points_4_lose2",   "Append" => "Points"),
	                   array("Name" => "Losing (3rd Place)",  "Default" => 4,   "Internal" => "points_4_lose3",   "Append" => "Points"),
	                   array("Name" => "Losing (4th Place)",  "Default" => 1,   "Internal" => "points_4_lose4",   "Append" => "Points"),
	                   array("Name" => "Forfeit",             "Default" => -4,  "Internal" => "points_4_forfeit", "Append" => "Points"),
	                   array("Name" => "Platinum Percentage", "Default" => 3,   "Internal" => "points_4_plat",    "Append" => "Points"),
	                   array("Name" => "Ultimate Percentage", "Default" => 6,   "Internal" => "points_4_ult",     "Append" => "Points"),
	               );
						for ($j = 0; $j < count($tabledata); $j ++) {
				         if ($tabledata[$j]["Internal"] == "") {
				         	echo("<tr><td colspan=\"2\">" . $tabledata[$j]["Name"] . "</td></tr>");
				         	continue;
				         }
				         echo("<td>" . $tabledata[$j]["Name"] . "</td>");
				         echo("<td" . ($tabledata[$j]["Append"] != "" ? " class=\"input-append\"" : "") . ">");
				         echo("<input class=\"input\" " . ($tabledata[$j]["Append"] != "" ? "id=\"appendedInput\"" : "style=\"margin-bottom: 0px;\"") . "type=\"text\" placeholder=\"" . $tabledata[$j]["Default"] . "\" value=\"" . $tabledata[$j]["Default"] . "\" name=\"" . $tabledata[$j]["Internal"] . "\">");
				         if ($tabledata[$j]["Append"] != "")
				            echo("<span class=\"add-on\">" . $tabledata[$j]["Append"] . "</span>");
				         echo("</td></tr>");
				      }
						?>
					</tbody>
				</table>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Create Super Challenge</button>
						<a href="scedit.php" class="btn">Cancel</a>
					</div>
				</div>
			</form>
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
