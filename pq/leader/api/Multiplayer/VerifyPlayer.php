<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$username = requireParam("username");
$session = requireParam("session");

$user = User::get(JoomlaSupport::getUserId($username));

//Just need their rating
$rating = $user->getRating("rating_mp");

$result = [
	"id" => $user->id,
	"username" => $user->username,
	"display" => $user->joomla["name"],
	"rating" => $rating,
	"verification" => getUserVerification($username, $session)
];

techo(json_encode($result));

function getUserVerification($username, $session) {
	global $pdb;

	$query = $pdb->prepare("SELECT `loginsess` FROM `loggedin` WHERE `username` = :username");
	$query->bindValue(":username", $username);
	$query->execute();

	if ($query->rowCount()) {
		//Session they logged in with, for checking
		$loginSession = $query->fetchColumn(0);

		if ($loginSession === $session) {
			// Success!
			return "SUCCESS";
		} else {
			// Invalid session, probably trying to impersonate someone
			return "BADSESSION";
		}
	} else {
		// No account, don't let them play with rated games
		return "FAIL";
	}
}