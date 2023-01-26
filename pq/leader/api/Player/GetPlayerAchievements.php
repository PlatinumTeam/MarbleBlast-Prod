<?php

define("PQ_RUN", true);
require_once("../../Framework.php");

include("../Achievement/UpdateAchievements.php");

Login::requireLogin();
$username = param("user") ?? Login::getCurrentUsername();
$userId = JoomlaSupport::getUserId($username);
$user = User::get($userId);
updateAchievements($user);

techo(json_encode([
	"username" => $username,
	"achievements" => $user->achievements
]));
