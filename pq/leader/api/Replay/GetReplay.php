<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$mission = Mission::getByParams();
if ($mission === null) {
	error("Need mission");
}

$file = BASE_DIR . "/data/Replay/" . $mission->id . ".rrec.zip";
$zipFile = "zip://" . BASE_DIR . "/data/Replay/" . $mission->id . ".rrec.zip#" . $mission->id . ".rrec";

if (is_file($file)) {
	$contents = file_get_contents($zipFile);

	techo(json_encode([
		"contents" => tbase64_encode($contents)
	]));

} else {
	//Not found
	techo(json_encode([
		"error" => "No replay for this mission"
	]));
}

