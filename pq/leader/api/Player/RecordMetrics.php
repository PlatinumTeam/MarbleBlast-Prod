<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

Login::requireLogin();

$user                 = Login::getCurrentUser();
$screenResolution     = param("screenResolution");
$windowResolution     = param("windowResolution");
$supportedResolutions = param("supportedResolutions");

if ($screenResolution !== null) {
	$parts  = explode(" ", $screenResolution);
	$width  = $parts[0];
	$height = $parts[1];
	$depth  = count($parts) === 3 ? $parts[2] : 32;

	$query = $db->prepare("INSERT IGNORE INTO `ex82r_metrics_screen_resolution` SET `user_id` = :user_id, `width` = :width, `height` = :height, `color_depth` = :depth");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":width", $width);
	$query->bindValue(":height", $height);
	$query->bindValue(":depth", $depth);
	$query->execute();
}

if ($windowResolution !== null) {
	$parts  = explode(" ", $windowResolution);
	$width  = $parts[0];
	$height = $parts[1];
	$depth  = count($parts) === 3 ? $parts[2] : 32;

	$query = $db->prepare("INSERT IGNORE INTO `ex82r_metrics_window_resolution` SET `user_id` = :user_id, `width` = :width, `height` = :height, `color_depth` = :depth");
	$query->bindValue(":user_id", $user->id);
	$query->bindValue(":width", $width);
	$query->bindValue(":height", $height);
	$query->bindValue(":depth", $depth);
	$query->execute();
}

if ($supportedResolutions !== null) {
	foreach ($supportedResolutions as $resolution) {
		$parts  = explode(" ", $resolution);
		$width  = $parts[0];
		$height = $parts[1];
		$depth  = count($parts) === 3 ? $parts[2] : 32;

		$query = $db->prepare("INSERT IGNORE INTO `ex82r_metrics_supported_resolutions` SET `user_id` = :user_id, `width` = :width, `height` = :height, `color_depth` = :depth");
		$query->bindValue(":user_id", $user->id);
		$query->bindValue(":width", $width);
		$query->bindValue(":height", $height);
		$query->bindValue(":depth", $depth);
		$query->execute();
	}
}

techo("SUCCESS");
