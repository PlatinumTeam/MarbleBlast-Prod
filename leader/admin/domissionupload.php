<?php

define("MAX_MISSION_SIZE",    256 * 1024); //Maximum size of the mission file  (256KB)
define("MAX_IMAGE_SIZE",      256 * 1024); //Maximum size of the level picture (256KB)
define("MAX_INTERIOR_SIZE",  1024 * 1024 * 2); //Maximum size of one interior  (2MB)
define("MAX_INTERIOR_TOTAL", 1024 * 1024 * 4); //Maximum size of all interiors (4MB)
//If we ever have custom shapes, they need to total be max 256KB
//If we ever have custom skies, they need to total be max 768KB
//So with these values, we should have a 4.5MB (5.5MB) max for all mission data.

//Default sub-paths, interior is required
define("MISSION_SUBPATH", "platinumbeta/data/multiplayer/hunt/custom/");
define("IMAGE_SUBPATH", "platinumbeta/data/multiplayer/hunt/custom/");
define("SENDER_SUBPATH", "platinumbeta/data/multiplayer/hunt/custom/.screenshots/");
define("INTERIOR_SUBPATH", "platinumbeta/data/");
define("BASE_DIR", "platinumbeta");

define("SENDER_TEMPLATE", "SendTemplate.dif");

define("USE_IMAGE_SENDER", false);

$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > (MINIMUM_ACCESS - 1)) {

	$dedhost = MBDB::getDatabaseHost("dedicated");
	$deduser = MBDB::getDatabaseUser("dedicated");
	$dedpass = MBDB::getDatabasePass("dedicated");
	$deddata = MBDB::getDatabaseName("dedicated");

	$dsn = "mysql:dbname=" . $deddata . ";host=" . $dedhost;
	// Connect + select
	try {
		global $ded_connection;
	   $ded_connection = new SpDatabaseConnection($dsn, $deduser, $dedpass);
	} catch (SpDatabaseLoginException $e) {
		die("Could not open database connection.");
	}
	if ($ded_connection == null) {
		die("Could not connect to database.");
	}

	function dprepare($query) {
		global $ded_connection;

		return $ded_connection->prepare($query);
	}

	list($username, $id) = getPostValues("username", "id");

	//Check for ids
	$query = dprepare("SELECT * FROM `uploadids` WHERE `id` = :id");
	$query->bind(":id", $id);
	$result = $query->execute();

	if ($result->rowCount()) {
		//Ok so they've already used this id before
		//Check for if it's finished

		$row = $result->fetch();
		if ($row["status"] == 1) {
			//Yeah your upload is done, moron
			die(json_encode(array("success" => false, "error" => "Upload already completed!")));
		}
		if ($row["status"] == -1) {
			//Yeah your upload is done, moron
			die(json_encode(array("success" => false, "error" => "Upload failed!")));
		}
	} else {
		//First upload for an id, create row
		$query = dprepare("INSERT INTO `uploadids` SET `id` = :id, `username` = :username");
		$query->bind(":id", $id);
		$query->bind(":username", $username);
		$query->execute();
	}

	//Ok so now we know they have a row in the table.
	//What did they upload?
	/*
	$_FILES:
	Array
	(
	    [missionFile] => Array
	        (
	            [name] => Bowl.mis
	            [type] => application/octet-stream
	            [tmp_name] => /tmp/php2p85mw
	            [error] => 0
	            [size] => 83970
	        )

	)
	$_POST:
	Array
	(
	    [id] => vdaqi42fptaajo0cdgdw1l2ryeny0jd18ckwcuezyjjagc9n2rwaognqtwlcncds
	    [location-0] => platinumbeta/data/multiplayer/interiors/beginner/Bowl.dif
	    [location-1] => platinumbeta/data/multiplayer/interiors/beginner/BowlOuterRing.dif
	)

	Interior $_FILES:
	Array
	(
	    [missionInteriors] => Array
	        (
	            [name] => Array
	                (
	                    [0] => Bowl.dif
	                )
	            [type] => Array
	                (
	                    [0] => video/x-dv
	                )
	            [tmp_name] => Array
	                (
	                    [0] => /tmp/phpHcbCQ9
	                )
	            [error] => Array
	                (
	                    [0] => 0
	                )
	            [size] => Array
	                (
	                    [0] => 269091
	                )
	        )
	)
	*/

	function cancelUpload($id) {
		//Kill all their files here

		//Cancel their upload
		$query = dprepare("UPDATE `uploadids` SET `status` = -1 WHERE `id` = :id");
		$query->bind(":id", $id);
		$query->execute();

		//Delete all their files to save space
		$query = dprepare("SELECT * FROM `uploadids` WHERE `id` = :id");
		$query->bind(":id", $id);
		$result = $query->execute();
		$row = $result->fetch();

		//Unlink all the files
		unlink($row["missionfile"]);
		unlink($row["imagefile"]);

		//Ruin their interiors' dreams
		$interiors = json_decode($row["interiorfiles"], true);
		foreach ($interiors as $interior) {
			unlink($interior);
		}

		//Remove all associated information
		$query = dprepare("UPDATE `uploadids` SET `missionfile` = NULL, `imagefile` = NULL WHERE `id` = :id");
		$query->bind(":id", $id);
		$query->execute();
	}

	$keys = array_keys($_FILES);

	//Normally /usr/games/marbleblast/MBP/
	$basePath = dprepare("SELECT `gamelocation` FROM `servers`")->execute()->fetchIdx(0);

	foreach ($keys as $key) {
		$file = $_FILES[$key];

		//Interiors come as arrays
		$name = (gettype($file["name"]) == "string" ? $file["name"] : $file["name"][0]);

		//No path separators in your file names, plebs
		if (strstr($name, "/") !== false || strstr($name, ":") !== false) {
			//Yeah no. Go die in a fire
			cancelUpload($id);
			die(json_encode(array("success" => false, "error" => "Invalid filename: \"{$name}\" for parameter \"$key\"")));
		}

		if ($key == "missionFile") {
			//Mission file

			//Make sure the extension is .mis
			if (pathinfo($file["name"], PATHINFO_EXTENSION) != "mis") {
				cancelUpload($id);
				die(json_encode(array("success" => false, "error" => "Mission extension has to be .mis!")));
			}

			//Make sure the mission isn't too large
			if ($file["size"] > MAX_MISSION_SIZE) {
				cancelUpload($id);
				die(json_encode(array("success" => false, "error" => "Mission file is too large! (Max: " . MAX_MISSION_SIZE . " bytes)")));
			}

			//Afaik that's all we have to check for
			//Upload it

			$finalPath = $basePath . MISSION_SUBPATH . $file["name"];

			//Make sure the file doesn't actually exist!
			if (is_file($finalPath)) {
				$counter = 0;
				do {
					//$finalPath_0, _1, etc
					$newPath = dirname($finalPath) . "/" . basename($finalPath, ".mis") . "_" . $counter . ".mis";
					$counter ++;
				} while (is_file($newPath));

				$finalPath = $newPath;
			}

			move_uploaded_file($file["tmp_name"], $finalPath);

			//Store the mission file into the database so we can keep track of it
			$query = dprepare("UPDATE `uploadids` SET `missionfile` = :finalpath WHERE `id` = :id");
			$query->bind(":finalpath", $finalPath);
			$query->bind(":id", $id);
			$query->execute();

			//If we have an image, move it as well
			$query = dprepare("SELECT `imagefile` FROM `uploadids` WHERE `id` = :id");
			$query->bind(":id", $id);
			$result = $query->execute();
			$image = $result->fetchIdx(0);

			if ($image != NULL) {
				//Move the image file as well
				$imagePath = dirname($finalPath) . "/" . basename($finalPath, ".mis") . "." . pathinfo($image, PATHINFO_EXTENSION);


				//If there's another image there, we'll have to bulldoze it out of the way or something
				rename($image, $imagePath);

				//Store the image file into the database so we can keep track of it
				$query = dprepare("UPDATE `uploadids` SET `imagefile` = :imagepath WHERE `id` = :id");
				$query->bind(":imagepath", $imagePath);
				$query->bind(":id", $id);
				$query->execute();
			}

			//If we have any interior adjustments, we have to modify the mission
			$query = dprepare("SELECT `interioradjustments` FROM `uploadids` WHERE `id` = :id");
			$query->bind(":id", $id);
			$result = $query->execute();
			$interiorAdjustments = $result->fetchIdx(0);

			if ($image != NULL) {
				$interiorAdjustments = json_decode($interiorAdjustments);

				//Grab mission contents
				$conts = file_get_contents($finalPath);

				//WARNING: Ugly code :(

				//Remove any "~/data/" nonsense
				$conts = str_replace("\"~/", "\"" . BASE_DIR . "/", $conts);
				//Remove any "./" nonsense as well
				$conts = str_replace("\"./", "\"" . dirname($finalPath) . "/", $conts);
				//Remove any "../" nonsense as well (platinumbeta/data/multiplayer/hunt/missions/custom/mission)
				//Five levels gets you do platinumbeta/data, the base directory for mission stuff
				$conts = str_replace("\"../../../../../", "\"" . dirname(dirname(dirname(dirname(dirname(dirname($finalPath)))))) . "/", $conts);
				$conts = str_replace("\"../../../../", "\""    . dirname(dirname(dirname(dirname(dirname($finalPath)))))          . "/", $conts);
				$conts = str_replace("\"../../../", "\""       . dirname(dirname(dirname(dirname($finalPath))))                   . "/", $conts);
				$conts = str_replace("\"../../", "\""          . dirname(dirname(dirname($finalPath)))                            . "/", $conts);
				$conts = str_replace("\"../", "\""             . dirname(dirname($finalPath))                                     . "/", $conts);

				foreach ($interiorAdjustments as $original => $updated) {
					//Modify the mission file to include the updates!
					$conts = str_replace($location, $finalLocation, $conts);
				}
				//Update mission file
				file_put_contents($missionFile, $conts);
			}

			//Ok we're good here
			die(json_encode(array("success" => true)));
		}

		if ($key == "missionImage") {
			//Image file

			//Make sure the extension is correct:
			$extension = pathinfo($file["name"], PATHINFO_EXTENSION);
			if ($extension != "png" && $extension != "jpg" && $extension != "jpeg") {
				cancelUpload($id);
				die(json_encode(array("success" => false, "error" => "Image extension has to be .png, .jpg, or .jpeg!")));
			}

			//Make sure the image isn't too large
			if ($file["size"] > MAX_IMAGE_SIZE) {
				cancelUpload($id);
				die(json_encode(array("success" => false, "error" => "Image file is too large! (Max: " . MAX_IMAGE_SIZE . " bytes)")));
			}

			//Afaik that's all we have to check for
			//Upload it

			//Check for a mission file first, if we have one, follow its lead
			$query = dprepare("SELECT `missionfile` FROM `uploadids` WHERE `id` = :id");
			$query->bind(":id", $id);
			$result = $query->execute();
			$missionFile = $result->fetchIdx(0);

			//Temp path (may be changed)
			$finalPath = $basePath . IMAGE_SUBPATH . $file["name"];

			//We should always have the same basename as the mission file
			if ($missionFile == NULL) {
				//If we don't have a mission yet, just do the regular checks and the mission uploader will grab the image later

				//Make sure the file doesn't actually exist!
				if (is_file($finalPath)) {
					$counter = 0;
					do {
						//$finalPath_0, _1, etc
						$newPath = dirname($finalPath) . "/" . pathinfo($finalPath, PATHINFO_FILENAME) . "_" . $counter . "." . $extension;
						$counter ++;
					} while (is_file($newPath));

					$finalPath = $newPath;
				}
			} else {
				//We do have a mission file, follow its lead
				$finalPath = dirname($missionFile) . "/" . basename($missionFile, ".mis") . "." . $extension;
			}

			//Now move the image into location
			move_uploaded_file($file["tmp_name"], $finalPath);

			//Store the mission file into the database so we can keep track of it
			$query = dprepare("UPDATE `uploadids` SET `imagefile` = :finalpath WHERE `id` = :id");
			$query->bind(":finalpath", $finalPath);
			$query->bind(":id", $id);
			$query->execute();

			if (USE_IMAGE_SENDER) {
				//Now make an image sender

				//Grab the interiors
				$query = dprepare("SELECT `interiorfiles` FROM `uploadids` WHERE `id` = :id");
				$query->bind(":id", $id);
				$result = $query->execute();
				$interiorFiles = $result->fetchIdx(0);

				//Should be either NULL or a json array
				if ($interiorFiles == NULL)
					$interiorFiles = array();
				else
					$interiorFiles = json_decode($interiorFiles, true);

				//Replace the template with our image
				$final = pathinfo($finalPath, PATHINFO_FILENAME);

				//We have <template> and 53 chars of whitespace to deal with
				if (strlen($final) < 63) {
					//Create a template for the mission file
					$conts = file_get_contents($basePath . SENDER_SUBPATH . SENDER_TEMPLATE);

					//Need to find this
					$find = "<template>";

					//Add \x00 padding for names
					if (strlen($final) < 10)
						$final .= str_repeat("\x00", 10 - strlen($final));
					else
						$find  .= str_repeat("\x00", strlen($final) - 10);

					//Now splice
					$conts = str_replace($find, $final, $conts);

					//And dump
					$senderPath = $basePath . SENDER_SUBPATH . "send-" . pathinfo($finalPath, PATHINFO_FILENAME) . ".dif";
					file_put_contents($senderPath, $conts);

					//Record
					$interiorFiles[] = $senderPath;

					//Update the database
					$query = dprepare("UPDATE `uploadids` SET `interiorfiles` = :interiorfiles WHERE `id` = :id");
					$query->bind(":interiorfiles", json_encode($interiorFiles));
					$query->bind(":id", $id);
					$query->execute();

					//Modify the mission file
					$conts = file_get_contents($missionFile);

					//Find the last }; and insert the interior before it
					$last = strrpos($conts, "//--- OBJECT WRITE END ---");
					$last = strrpos($conts, "};", -(strlen($conts) - $last));

					$interior = "   new InteriorInstance() {" . "\n" .
									"      position = \"10000000 10000000 10000000\";" . "\n" .
									"      rotation = \"1 0 0 0\";" . "\n" .
									"      scale = \"0 0 0\";" . "\n" .
									"      interiorFile = \"~/data/multiplayer/hunt/custom/.screenshots/send-" . pathinfo($finalPath, PATHINFO_FILENAME) . ".dif\";" . "\n" .
									"      showTerrainInside = \"0\";" . "\n" .
									"   };" . "\n";
					$conts = substr($conts, 0, $last) . $interior . substr($conts, $last);

					//Now write it out
					file_put_contents($missionFile, $conts);
				}
			}

			//Ok we're good here
			die(json_encode(array("success" => true)));
		}

		if ($key == "missionInteriors") {
			//Interiors are slightly more tricky, what do we do if we have an invalid reference? Blow up. Sounds like a good idea

			for ($i = 0; $i < count($file["name"]); $i ++) {
				//Make sure the extension is correct:
				$extension = pathinfo($file["name"][$i], PATHINFO_EXTENSION);

				//Someone might need bmp/tga textures, but those are really huge so we don't need them
				if ($extension != "dif" && $extension != "png" && $extension != "jpg" && $extension != "jpeg") {
					cancelUpload($id);
					die(json_encode(array("success" => false, "error" => "Interior extension has to be .dif, .png, .jpg, or .jpeg!")));
				}

				//Make sure the interior isn't too large
				if ($file["size"][$i] > MAX_INTERIOR_SIZE) {
					cancelUpload($id);
					die(json_encode(array("success" => false, "error" => "Interior file is too large! (Max: " . MAX_INTERIOR_SIZE . " bytes)")));
				}

				//Grab the rest of the interiors
				$query = dprepare("SELECT `interiorfiles`, `interioradjustments` FROM `uploadids` WHERE `id` = :id");
				$query->bind(":id", $id);
				$result = $query->execute();
				list($interiorFiles, $interiorAdjustments) = $result->fetchIdx();

				//Should be either NULL or a json array
				if ($interiorFiles == NULL)
					$interiorFiles = array();
				else
					$interiorFiles = json_decode($interiorFiles, true);

				if ($interiorAdjustments == NULL)
					$interiorAdjustments = array();
				else
					$interiorAdjustments = json_decode($interiorAdjustments, true);

				$totalSize = $file["size"][$i];

				//Get the total size of all interiors
				foreach ($interiorFiles as $interior) {
					$totalSize += filesize($interior);
				}

				//Make sure the total isn't too large
				if ($totalSize > MAX_INTERIOR_TOTAL) {
					cancelUpload($id);
					die(json_encode(array("success" => false, "error" => "Total interior file sizes are too large! (Max: " . MAX_INTERIOR_TOTAL . " bytes)")));
				}

				//Ok now we've figured out that it's not too large, we need to place the interiors
				//Luckily for us, they have to specify where they should go

				//Find location in $_POST
				/*
					$_POST:
					Array
					(
					    [id] => vdaqi42fptaajo0cdgdw1l2ryeny0jd18ckwcuezyjjagc9n2rwaognqtwlcncds
					    [location-0] => platinumbeta/data/multiplayer/interiors/beginner/Bowl.dif
					    [location-1] => platinumbeta/data/multiplayer/interiors/beginner/BowlOuterRing.dif
					    [index-0] => Bowl.dif
					    [index-1] => BowlOuterRing.dif
					)
				*/
				//Search for it
				$key = array_search($file["name"][$i], $_POST);
				//Strip text from "index-0"
				$index = substr($key, 6); //Gets "0"
				//Now find its location from $_POST
				$location = $_POST["location-" . $index];

				//Great, let's make sure it's still within boundaries
				if (strstr($location, "../") !== false || strstr($location, ":") !== false) {
					//Yeah no. Go die in a fire
					cancelUpload($id);
					die(json_encode(array("success" => false, "error" => "Invalid location: \"$location\" for interior \"$index\"!")));
				}

				$extension = pathinfo($location, PATHINFO_EXTENSION);
				//If it doesn't have an extension, then we need to add it
				if ($extension === "") {
					$location = $location . "/" . $file["name"][$i];
				}

				//Check the extension
				if ($extension !== "dif" && $extension !== "png" && $extension !== "jpg" && $extension !== "jpeg") {
					//Yeah no. Go die in a fire
					cancelUpload($id);
					die(json_encode(array("success" => false, "error" => "Invalid extension: \"$location\" for interior \"$index\"")));
				}

				//Should at least have platinumbeta/data/ in it
				if (strstr($location, INTERIOR_SUBPATH) === false) {
					//Yeah no. Go die in a fire
					cancelUpload($id);
					die(json_encode(array("success" => false, "error" => "Invalid directory: \"$location\" for interior \"$index\"")));
				}

				//Make sure it doesn't already exist
				$finalPath = $basePath . $location;

				$adjustment = false;

				//Make sure the file doesn't actually exist!
				if (is_file($finalPath)) {
					$counter = 0;
					do {
						//$finalPath_0, _1, etc
						$newPath = dirname($finalPath) . "/" . basename($finalPath, ".dif") . "_" . $counter . ".dif";
						$counter ++;
					} while (is_file($newPath));

					//Changeover from $finalPath to $newPath, record this shit!
					$finalLocation = substr($newPath, strlen($basePath));
					$interiorAdjustments[$location] = $finalLocation;
					$adjustment = true;

					//Now update
					$finalPath = $newPath;
				}

				move_uploaded_file($file["tmp_name"][$i], $finalPath);
				$interiorFiles[] = $finalPath;

				//Now update the database, pronto!
				$query = dprepare("UPDATE `uploadids` SET `interiorfiles` = :interiorfiles, `interioradjustments` = :interioradjustments WHERE `id` = :id");
				$query->bind(":interiorfiles", json_encode($interiorFiles));
				$query->bind(":interioradjustments", json_encode($interiorAdjustments));
				$query->bind(":id", $id);
				$query->execute();

				//Well now we have to do something with the mission...
				$query = dprepare("SELECT `missionfile` FROM `uploadids` WHERE `id` = :id");
				$query->bind(":id", $id);
				$result = $query->execute();
				$missionFile = $result->fetchIdx(0);

				//If we have a mission, replace all instances of <interior> with <adjusted interior>
				if ($missionFile != NULL && $adjustment) {
					//Simple find+replace
					$conts = file_get_contents($missionFile);

					//WARNING: Ugly code :(

					//Remove any "~/data/" nonsense
					$conts = str_replace("\"~/", "\"" . BASE_DIR . "/", $conts);
					//Remove any "./" nonsense as well
					$conts = str_replace("\"./", "\"" . dirname($missionFile) . "/", $conts);
					//Remove any "../" nonsense as well (platinumbeta/data/multiplayer/hunt/missions/custom/mission)
					//Five levels gets you do platinumbeta/data, the base directory for mission stuff
					$conts = str_replace("\"../../../../../", "\"" . dirname(dirname(dirname(dirname(dirname(dirname($missionFile)))))) . "/", $conts);
					$conts = str_replace("\"../../../../", "\""    . dirname(dirname(dirname(dirname(dirname($missionFile)))))          . "/", $conts);
					$conts = str_replace("\"../../../", "\""       . dirname(dirname(dirname(dirname($missionFile))))                   . "/", $conts);
					$conts = str_replace("\"../../", "\""          . dirname(dirname(dirname($missionFile)))                            . "/", $conts);
					$conts = str_replace("\"../", "\""             . dirname(dirname($missionFile))                                     . "/", $conts);

					//Now actually replace
					$conts = str_replace($location, $finalLocation, $conts);
					file_put_contents($missionFile, $conts);
					//That was easy!
				}

				die(json_encode(array("success" => true)));
			}
		}
	}
}

?>