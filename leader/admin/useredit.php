<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Edit User Data");

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
			<h1 class="text-center">Edit User Data</h1>
			<br>
			<br>
			<form action="douseredit.php" method="POST" style="margin-bottom: 0px">
				<table class="table table-bordered table-striped" style="margin-bottom: 0px">
					<tr>
						<th>Field</th>
						<th>Value</th>
					</tr>
				<?php
list($username) = getPostValues("edituser");
$query = pdo_prepare("SELECT * FROM `users` WHERE `username` = :user");
$query->bind(":user", $username);
$result = $query->execute();

if ($result->rowCount() && $result) {
	$row = $result->fetch();

	$row["display"] = getDisplay($row["username"]);

	$tabledata = array(
				 array("Name" => "Username",              "Default" => "",    "Internal" => "username",            "Append" => "",       "Type" => "-1"),
				 array("Name" => "Display Name",          "Default" => "",    "Internal" => "display",             "Append" => "",       "Type" => ($row["joomla"] ? "-1" : "0")),
				 array("Name" => "Id",                    "Default" => "",    "Internal" => "id",                  "Append" => "",       "Type" => "-1"),
				 array("Name" => "Banned",                "Default" => "0",   "Internal" => "banned",              "Append" => "",       "Type" => "5"),
				 array("Name" => "Mute Index",            "Default" => "0",   "Internal" => "muteIndex",           "Append" => "",       "Type" => "0"),
				 array("Name" => "Mute Index Multiplier", "Default" => "1",   "Internal" => "muteMultiplier",      "Append" => "x",      "Type" => "0"),
				 array("Name" => "Join Date",             "Default" => "0",   "Internal" => "joindate",            "Append" => "",       "Type" => "4"),
				 array("Name" => "Last Action",           "Default" => "0",   "Internal" => "lastaction",          "Append" => "",       "Type" => "4"),
				 array("Name" => "New Password",          "Default" => "",    "Internal" => "newpassword",         "Append" => "",       "Type" => ($row["joomla"] ? "-1" : "2")),
				 array("Name" => "Email",                 "Default" => "",    "Internal" => "email",               "Append" => "",       "Type" => ($row["joomla"] ? "-1" : "0")),
				 array("Name" => "Show Email",            "Default" => false, "Internal" => "showemail",           "Append" => "",       "Type" => "6"),
				 array("Name" => "Personal",              "Default" => "",    "Internal" => ""),
				 array("Name" => "Secret Question",       "Default" => "",    "Internal" => "secretq",             "Append" => "",       "Type" => ($row["joomla"] ? "-1" : "0")),
				 array("Name" => "Secret Answer",         "Default" => "",    "Internal" => "secreta",             "Append" => "",       "Type" => ($row["joomla"] ? "-1" : "0")),
				 array("Name" => "Signature",             "Default" => "",    "Internal" => "signature",           "Append" => "",       "Type" => "0"),
				 array("Name" => "Access",                "Default" => "0",   "Internal" => "access",              "Append" => "",       "Type" => "3"),
				 array("Name" => "Ratings",               "Default" => "",    "Internal" => ""),
				 array("Name" => "Rating",                "Default" => "0",   "Internal" => "rating",              "Append" => "Points", "Type" => "0"),
				 array("Name" => "Rating (MBP)",          "Default" => "0",   "Internal" => "rating_mbp",          "Append" => "Points", "Type" => "0"),
				 array("Name" => "Rating (MBG)",          "Default" => "0",   "Internal" => "rating_mbg",          "Append" => "Points", "Type" => "0"),
				 array("Name" => "Rating (MBU)",          "Default" => "0",   "Internal" => "rating_mbu",          "Append" => "Points", "Type" => "0"),
				 array("Name" => "Rating (Custom)",       "Default" => "0",   "Internal" => "rating_custom",       "Append" => "Points", "Type" => "0"),
				 array("Name" => "Rating (Achievements)", "Default" => "0",   "Internal" => "rating_achievements", "Append" => "Points", "Type" => "0"),
				 array("Name" => "Rating (MP)",           "Default" => "0",   "Internal" => "rating_mp",           "Append" => "Points", "Type" => "0"),
				 array("Name" => "Multiplayer Games",     "Default" => "0",   "Internal" => "rating_mpgames",      "Append" => "Games",  "Type" => "0"),
				 array("Name" => "Challenge Points",      "Default" => "0",   "Internal" => "challengepoints",     "Append" => "Points", "Type" => "0"),
				 );
for ($j = 0; $j < count($tabledata); $j ++) {
	if ($tabledata[$j]["Internal"] == "") {
		echo("<tr><td colspan=\"2\" style=\"display: block; height: 40px;\"></tr></td><tr><td colspan=\"2\"><b>" . $tabledata[$j]["Name"] . "</b></td></tr>");
		continue;
	}
	echo("<tr><td>" . $tabledata[$j]["Name"] . "</td>");
	echo("<td" . ($tabledata[$j]["Append"] != "" ? " class=\"input-append\"" : "") . ">");
	$key = $tabledata[$j]["Internal"];
	$value = $row[$key];
	switch ($tabledata[$j]["Type"]) {
		case -1:
			if ($key == "username")
				$key = "user";
			echo("$value<input type=\"hidden\" name=\"$key\" value=\"$value\">");
			break;
		case 0:
			echo("<input class=\"input input-xlarge\" " . ($tabledata[$j]["Append"] != "" ? "id=\"appendedInput\"" : "style=\"margin-bottom: 0px;\"") . "type=\"text\" placeholder=\"" . $tabledata[$j]["Default"] . "\" value=\"$value\" name=\"$key\">");
			break;
		case 1:
			echo("<textarea rows=\"6\" class=\"input-xxlarge\" name=\"$key]\">" . str_replace(array("\r\n", "\\r\\n", "\n", "\\n"), "&#10;", $value) . "</textarea>");
			break;
		case 2:
			echo("<input class=\"input input-xlarge\" " . ($tabledata[$j]["Append"] != "" ? "id=\"appendedInput\"" : "style=\"margin-bottom: 0px;\"") . "type=\"password\" placeholder=\"" . $tabledata[$j]["Default"] . "\" value=\"$value\" name=\"$key\">");
			break;
		case 3:	//Access popup
			echo("<select name=\"$key\">");
			echo("<option value=\"-3\""   . ($value == -3 ? "selected=\"selected\"" : "") . ">Banned: -3</option>");
			echo("<option value=\"0\""    . ($value ==  0 ? "selected=\"selected\"" : "") . ">User: 0</option>");
			if ($access >= 2) {
				echo("<option value=\"1\"" . ($value ==  1 ? "selected=\"selected\"" : "") . ">Moderator: 1</option>");
				echo("<option value=\"2\"" . ($value ==  2 ? "selected=\"selected\"" : "") . ">Administrator: 2</option>");
			}
			echo("</select>");
			break;
		case 4:
			$display = strtotime($value);
			$display = date("r", $display);
			echo("$display<input type=\"hidden\" name=\"$key\" value=\"$value\">");
			break;
		case 5:
			$display = ($value ? "True" : "False");
			echo("$display<input type=\"hidden\" name=\"$key\" value=\"$value\">");
			break;
		case 6:
			$value = $value ? "on" : "";
			echo("<input type=\"checkbox\" name=\"$key\" value=\"$value\">");
			break;
		default:
			break;
		}
		if ($tabledata[$j]["Append"] != "")
			echo("<span class=\"add-on\">" . $tabledata[$j]["Append"] . "</span>");
		echo("</td></tr>");
	}
}

$query = pdo_prepare("SELECT `address` FROM `addresses` WHERE `username` = :user");
$query->bind(":user", $username);
$result = $query->execute();
if ($result->rowCount() && $result) {
	echo("<tr><td colspan=\"2\" style=\"display: block; height: 40px;\"></td></tr>");
	echo("<tr><td><b>Known IP Addresses</b></td><td><b>Shared With</b></td></tr>");

	$addresses = $result->fetchAll();

	foreach ($addresses as $index => $list) {
		$address = $list["address"];

		//Find shared
		$query = pdo_prepare("SELECT `users`.`username`, `users`.`banned` FROM
(SELECT `username` FROM `addresses` WHERE `username` != :user AND `address` = :address AND `username` != '') AS `results`
INNER JOIN `users` ON `users`.`username` = `results`.`username`");

		$query->bind(":user", $username);
		$query->bind(":address", $address);
		$result = $query->execute();
		$shared = $result->fetchAll();

		$sharedNames = array_reduce($shared, function($carry, $item) {
			$name = $item["username"];
			if ($item["banned"])
				$name .= " <b>[Banned]</b>";
			return $carry . " " . $name;
		}, "");

		echo("<tr><td>$address</td><td>$sharedNames</td></tr>");
	}
}

/*
while (($row = $result->fetch()) !== false) {
	echo("<tr><td>");
	echo($row["displayname"] . ":");
	echo("</td><td>");

	switch ($row["type"]) {
		case -1:
			echo("<span class=\"input-xlarge uneditable-input\">{$row['value']}</span><input type=\"hidden\" name=\"settings[{$row['key']}]\" value=\"{$row['value']}\">");
			break;
		case 0:
			echo("<input class=\"input-xlarge\" type=\"text\" name=\"settings[{$row['key']}]\" placeholder=\"{$row['default']}\" value=\"{$row['value']}\">");
			break;
		case 1:
			echo("<textarea rows=\"6\" class=\"input-xxlarge\" name=\"settings[{$row['key']}]\">" . str_replace(array("\r\n", "\\r\\n", "\n", "\\n"), "&#10;", $row['value']) . "</textarea>");
		default:
			break;
	}
	echo("</td></tr>");
}*/
				?>
					<tr>
						<td colspan="2">
							<div class="text-center">
								<input type="submit" class="btn btn-primary" value="Update Settings">
							</div>
						</td>
					</tr>
				</table>
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
