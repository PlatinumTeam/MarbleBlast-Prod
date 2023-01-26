<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$base = requireParam("flair");

//Actual path
$flair = JOOMLA_BASE . "/webchat/assets/flair/$base.png";

//Make sure it exists
if (is_file($flair)) {
	//Spit it out
	$contents = file_get_contents($flair);

	$hash = hash("sha256", $contents);

	$output = [
		"filename" => "{$base}.png",
		"hash"     => $hash,
		"contents" => tbase64_encode($contents)
	];

	techo(json_encode($output));
} else {
	//Not found
	techo(json_encode([
		"error" => "No flair"
	]));
}
