<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Modify Super Challenges");

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
<script type="text/javascript">
var lastCollapse = -1;
function toggleRow(id) {
   if (lastCollapse == id) {
      $("#challenge" + id + "_0").collapse("hide");
      $("#challenge" + id + "_1").collapse("hide");
      $("#challenge" + lastCollapse).removeClass("select");
      lastCollapse = -1;
      return;
   }

   if (lastCollapse != -1) {
      $("#challenge" + lastCollapse + "_0").collapse("hide");
      $("#challenge" + lastCollapse + "_1").collapse("hide");
      $("#challenge" + lastCollapse).removeClass("select");
   }

   $("#challenge" + id + "_0").collapse("show");
   $("#challenge" + id + "_1").collapse("show");
   $("#challenge" + id).addClass("select");
   lastCollapse = id;
}
<?php
	require_once("../lbratings.php");
?>
var levelList = <?php echo(json_encode(getMissionList())); ?>;
</script>
<style type="text/css">
tr.select td {
   -webkit-box-shadow: inset 0 3px 8px rgba(0, 0, 0, 0.125);
   -moz-box-shadow: inset 0 3px 8px rgba(0, 0, 0, 0.125);
   box-shadow: inset 0 3px 8px rgba(0, 0, 0, 0.125);
   background-color: #cccccc !important;
}
tr.select td div.collapse {
   margin-top: 15px;
}
.propheader {
   height: 40px;
}
.propvalue {
   display: block;
}
</style>
<div class="container-fluid">
   <div class="row-fluid">
      <?php sidebarCreate(); ?>
      <div class="span9 well">
         <h1 class="text-center">Modify Super Challenges</h1>
         <br>
         <?php if (isset($_GET["error"])) {?>
         <div class="alert alert-danger">
            There was an error. The error was: <b><?php
            switch ($_GET["error"]) {
               case 0: echo("There was an internal server error"); break;
            }
            ?></b>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
         </div>
         <?php } ?>
         <?php if (isset($_GET["success"])) {?>
         <div class="alert alert-success">
            <?php
            switch ($_GET["success"]) {
					case 1: echo("The super challenge was deleted successfully"); break;
					case 2: echo("The super challenge was created successfully"); break;
					case 3: echo("The super challenge has been updated."); break;
            }
            ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
         </div>
         <?php } ?>
         <br>
         <a href="scadd.php" class="btn btn-success">Create Super Challenge</a>
         <table class="table table-striped table-bordered" style="margin-top: 10px">
            <tr>
               <th>Id</th>
               <th>Name</th>
               <th>Actions</th>
            </tr>
         <?php

$query = pdo_prepare("SELECT * FROM `scdata` ORDER BY `id` ASC");
$result = $query->execute();
if ($result) {
   $tabledata = array(
                   array("Name" => "Display Name",        "Default" => "",  "Internal" => "display",          "Append" => ""),
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
   $i = 0;
   while (($row = $result->fetch())) {
      $i ++;
      echo("<tr id=\"challenge{$row['id']}\">");
      echo(   "<td>");
      echo(      "{$i}.");
      echo(      "<div id=\"challenge{$row['id']}_0\" class=\"collapse\">");
      for ($j = 0; $j < count($tabledata); $j ++) {
         echo(         "<div class=\"propheader\">");
         echo(            $tabledata[$j]["Name"]);
         echo(         "</div>");
      }
      echo(      "</div>");
      echo(   "</td><td>");
      echo(      $row["display"]);
      echo(      "<div id=\"challenge{$row['id']}_1\" class=\"collapse\">");
      echo(         "<form action=\"doscedit.php\" method=\"POST\" autocomplete=\"off\">");
      for ($j = 0; $j < count($tabledata); $j ++) {
         if ($tabledata[$j]["Internal"] == "") {
            echo("<div class=\"propvalue\" style=\"height: 40px;\"></div>");
            continue;
         }
         echo(         "<div class=\"propvalue" . ($tabledata[$j]["Append"] != "" ? " input-append" : "") . "\">");
         echo(            "<input class=\"input " . $tabledata[$j]["Internal"] . "\" " . ($tabledata[$j]["Append"] != "" ? "id=\"appendedInput\"" : "") . "type=\"text\" placeholder=\"" . $tabledata[$j]["Default"] . "\" value=\"" . $row[$tabledata[$j]["Internal"]] . "\" name=\"" . $tabledata[$j]["Internal"] . "\"" . ($tabledata[$j]["Internal"] == "missions" ? " data-provide=\"typeahead\" data-items=\"6\"" : "") . ">");
         if ($tabledata[$j]["Append"] != "")
            echo(         "<span class=\"add-on\">" . $tabledata[$j]["Append"] . "</span>");
         echo(         "</div>");
      }
      echo(            "<input type=\"submit\" class=\"btn btn-success\" value=\"Update Super Challenge\">&nbsp;");
      echo(            "<a class=\"btn\" href=\"javascript:void(0);\" onclick=\"toggleRow({$row['id']});\">Cancel</a>");
      echo(            "<input type=\"hidden\" name=\"id\" value=\"{$row['id']}\">");
      echo(         "</form>");
      echo(      "</div>");
      echo(   "</td><td>");
      echo(      "<a class=\"btn btn-info\" id=\"edit{$row['id']}\" href=\"javascript:void(0);\" onclick=\"toggleRow({$row['id']});\">Edit</a>&nbsp;");
      echo(      "<a class=\"btn btn-danger\" href=\"doscdelete.php?challenge={$row['id']}\">Delete</a>");
      echo(   "</td>");
      echo("</tr>\n");
   }
}

         ?>
         </table>
      </div>
   </div>
</div>
<script>
$(".missions").each(function() {
	$(this).typeahead({source: levelList, minLength: 1});
});
</script>
   <?php
} else {
  accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
