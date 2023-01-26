<?php
require("Database.php");

$uid = requireParam("uid");
$screenResolution = param("screenResolution");
$windowResolution = param("windowResolution");
$supportedResolutions = param("supportedResolutions");

$userInfo = getUserInfoByUid($uid);

if ($screenResolution !== null) {
	$parts  = explode(" ", $screenResolution);
	$width  = $parts[0];
	$height = $parts[1];
	$depth  = count($parts) === 3 ? $parts[2] : 32;

	$query = $db->prepare("INSERT IGNORE INTO `@_metrics_screen_resolution` SET `userid` = :userid, `width` = :width, `height` = :height, `color_depth` = :depth");
	$query->bindParam(":userid", $userInfo["id"]);
	$query->bindParam(":width", $width);
	$query->bindParam(":height", $height);
	$query->bindParam(":depth", $depth);
	$query->execute();
}

if ($windowResolution !== null) {
	$parts  = explode(" ", $windowResolution);
	$width  = $parts[0];
	$height = $parts[1];
	$depth  = count($parts) === 3 ? $parts[2] : 32;

	$query = $db->prepare("INSERT IGNORE INTO `@_metrics_window_resolution` SET `userid` = :userid, `width` = :width, `height` = :height, `color_depth` = :depth");
	$query->bindParam(":userid", $userInfo["id"]);
	$query->bindParam(":width", $width);
	$query->bindParam(":height", $height);
	$query->bindParam(":depth", $depth);
	$query->execute();
}

if ($supportedResolutions !== null) {
	foreach ($supportedResolutions as $resolution) {
		$parts  = explode(" ", $resolution);
		$width  = $parts[0];
		$height = $parts[1];
		$depth  = count($parts) === 3 ? $parts[2] : 32;

		$query = $db->prepare("INSERT IGNORE INTO `@_metrics_supported_resolutions` SET `userid` = :userid, `width` = :width, `height` = :height, `color_depth` = :depth");
		$query->bindParam(":userid", $userInfo["id"]);
		$query->bindParam(":width", $width);
		$query->bindParam(":height", $height);
		$query->bindParam(":depth", $depth);
		$query->execute();
	}
}

if ($query->execute()) {
	techo("SUCCESS");
} else {
	techo("FAILURE");
}
