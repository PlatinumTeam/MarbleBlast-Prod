<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");
require_once("../jsupport.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Dedicated Server Missions");

	//-------------------------------------------------------------------
	// Navbar start

	navbarCreate("MBP Admin");
	navbarAddSet("default");
	if ($access > 1) {
		navbarAddSet("admin");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	} else if ($access == 1) {
		navbarAddSet("mod");
		navbarAddItem("Log Out", "../logout.php?admin=true");
		navbarAddItem("Logged in as " . getPostValue("username"));
	}
	navbarAddItem("Access: " . accessTitle($access));
	navbarEnd();

	// Navbar end
	//------------------------------------––––––––––––––––––––––––––––---

function getMissionInfo($file) {
   $info = array();

   $handle = fopen($file, "r");
   if ($handle === false) {
   	return $info;
   }

	$inInfoBlock = false;

	while (($line = fgets($handle)) !== false) {
		$line = trim($line);
		if (!strlen($line))
			continue;

		if ($line == "new ScriptObject(MissionInfo) {") {
			$inInfoBlock = true;
			continue;
		} else if($inInfoBlock && $line == "};") {
			$inInfoBlock = false;
			break;
		}
		if ($inInfoBlock) {
			if (strpos($line, "=") !== false) {
				//First part
				$key = strtolower(trim(substr($line, 0, strpos($line, "="))));
				$value = stripslashes(trim(substr($line, strpos($line, "=") + 1, strlen($line))));

				if ($key !== "" && $value !== "") {
					//Semicolon and quotes
					$value = substr($value, 1, strlen($value) - 3);
					$info[$key] = $value;
				}
				continue;
			}
		}
	}

   fclose($handle);

   $info["file"] = $file;
   return $info;
}

if ($access > (MINIMUM_ACCESS - 1)) {
//So we need to get the entire contents of the dedicated server's custom missions directory
$dir = "/usr/games/marbleblast/MBP/platinumbeta/data/multiplayer/hunt/custom/";

$missions = array();

$handle = opendir($dir);
while (($file = readdir($handle)) !== false) {

	//Ignore current/parent directories
	if (substr($file, 0, 1) == ".")
		continue;

	//Ignore things that aren't missions
	if (pathinfo($file, PATHINFO_EXTENSION) != "mis")
		continue;

	//Extract the mission info
	$info = getMissionInfo("$dir$file");
	array_push($missions, $info);
}

usort($missions, function($a, $b) {
	return strcmp($a["name"], $b["name"]);
});

//Check for updates
if (array_key_exists("file", $_POST)) {
	//POSTing

	$keys = array_keys($_POST);
	$file = $_POST["file"];
	//Go build the mission info
	//TODO
	?>
	TODO: Mission Info
	<?php
}

?>
<script type="text/javascript">
var missions = <?php echo(json_encode($missions)); ?>;
var fields = ["name", "type", "level", "desc", "starthelptext", "artist", "music", "gamemode", "game", "time", "maxgemsperspawn", "radiusfromgem", "score[0]", "score[1]", "platinumscore[0]", "platinumscore[1]", "ultimatescore[0]", "ultimatescore[1]", "alarmstarttime", "overviewheight"];
var fieldNames = ["name", "type", "level", "desc", "startHelpText", "artist", "music", "gameMode", "game", "time", "maxGemsPerSpawn", "radiusFromGem", "score[0]", "score[1]", "platinumScore[0]", "platinumScore[1]", "ultimateScore[0]", "ultimateScore[1]", "alarmStartTime", "overviewHeight"];
$(document).ready(function() {
	var table = $("#missionlist");
	//Add items to table
	for (var i = 0; i < missions.length; i++) {
		var row = $("<tr><td>" + missions[i].name + "</td></tr>");
		row.attr("mission-id", i);
		row.click(function(event) {
			$(this).parent().children().removeClass("selected");
			$(this).addClass("selected");

			var index = $(this).attr("mission-id");

			var info = $("#missioninfo");
			info.empty();

			//Add the mission info!
			var infotable = $("<table></table>");
			infotable.addClass("table");

			for (var field in fields) {
				var row = $("<tr></tr>");
				row.append("<td>" + fieldNames[field] + "</td>");

				var edit = $("<input>");
				edit.val(missions[index][fields[field]]);
				edit.attr({
					type: "text",
					name: fields[field]
				});
				edit.addClass("input input-nopad");

				$("<td></td>").append(edit).appendTo(row);
				infotable.append(row);
			}
			for (var field in missions[index]) {
				if (fields.indexOf(field.toLowerCase()) != -1)
					continue;

				//Ignore the file as well
				if (field == "file")
					continue;

				var row = $("<tr></tr>");
				row.append("<td>" + field + "</td>");
				row.addClass("custom");

				var edit = $("<input>");
				edit.val(missions[index][field]);
				edit.attr({
					type: "text",
					name: field
				});
				edit.addClass("input input-nopad");

				$("<td></td>").append(edit).appendTo(row);
				infotable.append(row);
			}

			var form = $("<form></form>");
			form.attr({
				method: "POST",
				action: "missions.php"
			});

			form.append("<input type=\"hidden\" name=\"file\" value=\"" + missions[index].file + "\">");
			form.append(infotable);
			form.append("<input type=\"submit\" value=\"Update\" class=\"btn btn-primary\">");
			form.appendTo(info);
		});
		row.appendTo(table);
	}

var interiorCount = 0;
var errors = [];
var success = true;

	$('#missionUpload').fileupload({
		sequentialUploads: true,
		add: function (e, data) {
			$.each(data.files, function (index, file) {
				var input = data.fileInput[index].name;
				if (input == "missionFile") {
					$("#missionDisplay").text(file.name);
					var reader = new FileReader();

					reader.onload = function (e) {
						var text = e.target.result;
						var missionInfo = getMissionInfo(text);
						$("#missionDisplay").text(missionInfo.name);

						var info = $("#missionInfoDisplay");
						info.empty();

						//Add the mission info!
						var infotable = $("<table/>");
						infotable.addClass("table");

						for (var field in fields) {
							var row = $("<tr/>");
							row.append("<td>" + fieldNames[field] + "</td>");
							row.append("<td>" + missionInfo[fields[field]] + "<td>");
							infotable.append(row);
						}
						for (var field in missionInfo) {
							if (fields.indexOf(field.toLowerCase()) != -1)
								continue;

							var row = $("<tr></tr>");
							row.append("<td>" + field + "</td>");
							row.append("<td>" + missionInfo[field] + "<td>");

							row.addClass("custom");
							infotable.append(row);
						}
						infotable.appendTo(info);
					}
					reader.readAsText(file);
				}
				if (input == "missionImage") {
					var reader = new FileReader();
					reader.onload = function (e) {
						$("#imageDisplay").attr("src", e.target.result);
					}
					reader.readAsDataURL(file);
				}
				if (input == "missionInteriors[]") {
					var base = $("#interiorsDisplay");
					var item = $("<td/>").appendTo($("<tr/>"));

					item.attr("colspan", 2);
					item.append($("<b/>").text(file.name));
					<?php 
					//For a later day-- CBF to do this anymore
					// item.append($("<a/>")
					// 	.html("&#xD7; Remove")
					// 	.addClass("btn btn-danger btn-cancel")
					// 	.click(function(event) {
					// 	data.abort();
						
					// 	data.context1.remove();
					// 	data.context2.remove();
					// 	data.errorThrown = 'abort';

					// 	var that = $("#missionUpload").data('blueimp-fileupload') || $("#missionUpload").data('fileupload');

					// 	that._trigger('failed', e, data);
					// 	that._trigger('finished', e, data);
					// }));
					?>

					item.append("<br><br>Location:");
					var input = $("<input/>");
					input.attr({
						type: "text",
						name: "location-" + interiorCount,
					});
					input.addClass("input input-xxlarge input-location");
					input.val("platinumbeta/data/multiplayer/interiors/custom/" + file.name);
					item.append(input);

					var input = $("<input/>");
					input.attr({
						type: "hidden",
						name: "index-" + interiorCount,
					});
					input.val(file.name);
					item.append(input);

					interiorCount ++;

					base.append(item.parent());
					data.context = item;
				}
			});
			
			$("#modal-upload").attr("disabled", null)
				.click(function() {
					$(this).attr("disabled", "disabled");
					data.submit();
				});
		},
		done: function (e, data) {
			var result = JSON.parse(data.result);
			if (result.success != true) {
				//Alert the plebians!
				errors.push(result.error);
				success = false;
			}
		},
		stop: function (e, data) {
			$("#uploadFile").modal("hide");

			//Create an alert for the plebians
			$("#mainpage").prepend(
				$("<div/>")
				.addClass("alert alert-" + (success ? "success" : "danger"))
				.append($("<h4/>").text(success ? "Success" : "Failure"))
				.append($("<p/>").text(success ? "Your mission was successfully uploaded. Refresh the page to see changes." :
															"There was an error with the upload: " + errors[0] + " Refresh the page to try again."))
				.append($("<a/>")
					.addClass("btn btn-primary")
					.text("Refresh")
					.click(function(event) {
						window.location.reload();
					})
				)
			);
		},
		progressall: function(e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress .bar').css('width', progress + '%');
		}
	});
});


function getMissionInfo(file) {
   var info = Object();

	var inInfoBlock = false;
	var lines = file.split("\n");
	for (var i = 0; i < lines.length; i ++) {
		line = lines[i].trim();
		
		if (!line.length)
			continue;

		if (line == "new ScriptObject(MissionInfo) {") {
			inInfoBlock = true;
			continue;
		} else if (inInfoBlock && line == "};") {
			inInfoBlock = false;
			break;
		}
		if (inInfoBlock) {
			if (line.indexOf("=") != -1) {
				//First part
				var key = line.substr(0, line.indexOf("=")).trim().toLowerCase();
				var value = decodeURI(line.substr(line.indexOf("=") + 1, line.length).trim());

				if (key !== "" && value !== "") {
					//Semicolon and quotes
					value = value.substr(1, value.length - 3);
					info[key] = value;
				}
				continue;
			}
		}
	}

	return info;
}

function showUpload() {
	$("#uploadFile").modal();
	$("#modal-upload").attr("disabled", "disabled").click(function(){});
}

</script>

<style type="text/css">
tr.selected, tr.selected>td {
	background-color: #99ddff !important;
}

.input-nopad {
	margin-bottom: 0px !important;
}

tr.custom, tr.custom>td {
	background-color: #aaffaa !important;
}
#missionUpload {
	text-align: center;
}
#missionInfoDisplay {
	height: 207px;
	overflow-y: auto;
}
#imageContainer {
	padding: 10px;
	width: 248px;
	height: 187px;
	margin: 0 auto;
}
.input-location {
	float: right;
}
#progress {
	margin: 0px;
	margin-top: 2px;
	width: 70%;
	display: inline-block;
	float: left;
}
.btn-cancel {
	float: right;
}
.modal {
	width: 700px;
	margin-left: -350px;
}
@media(max-width: 550px) {
	#progress {
		width: 50%;
	}
}
</style>
<script type="text/javascript" src="./assets/file-upload/js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="./assets/file-upload/js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="./assets/file-upload/js/jquery.fileupload.js"></script>
<link rel="stylesheet" href="./assets/file-upload/css/jquery.fileupload.css">
<link rel="stylesheet" href="./assets/file-upload/css/jquery.fileupload-ui.css">
<div class="container-fluid">
	<div class="row-fluid">
		<?php sidebarCreate(); ?>
		<div class="span9 well" id="mainpage">
			<h1 class="text-center">Dedicated Server Missions</h1>
			<br>
			<div class="row-fluid">
				<div class="span6 well">
					<h3>Currently Installed Missions</h3>
					<table class="table table-striped table-bordered table-rounded" id="missionlist">
					</table>
					<a href="javascript:void(0);" class="btn btn-primary" onclick="showUpload();">Upload Mission</a>
				</div>
				<div class="span6 well">
					<h3>Mission Information</h3>
					<div id="missioninfo">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="uploadFile" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="uploadFiles" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&#xD7;</button>
		<h3 id="uploadFiles">Upload Mission...</h3>
	</div>
	<div class="modal-body">
		<form id="missionUpload" action="domissionupload.php" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="id" value="<?php echo(strRand(32));?>">
			<div>
				<p>Upload Mission Information:</p>
				<div class="fileupload-buttonbar">
					<span class="btn btn-success fileinput-button">
						<span>Mission File</span>
						<input type="file" name="missionFile">
					</span>
					<span class="btn btn-success fileinput-button">
						<span>Preview Image</span>
						<input type="file" accept="image/png,image/jpg" name="missionImage">
					</span>
					<span class="btn btn-success fileinput-button">
						<span>Add Interiors</span>
						<input type="file" name="missionInteriors[]" multiple>
					</span>
				</div>
			</div>
			<div>
				<h4>
					<span id="missionDisplay"></span>
				</h4>
				<div class="row-fluid">
					<div class="span7">
						<div id="missionInfoDisplay"></div>
					</div>
					<div class="span5">
						<div class="well" id="imageContainer">
							<img id="imageDisplay" width="248px" height="187px"/>
						</div>
					</div>
				</div>
				<hr>
				<p>Interiors:
					<table id="interiorsDisplay" class="table">
					</table>
				</p>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<div id="progress" class="progress progress-striped active">
			<div class="bar" style="width: 0%;"></div>
		</div>
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		<button id="modal-upload" class="btn btn-primary">Upload</button>
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
