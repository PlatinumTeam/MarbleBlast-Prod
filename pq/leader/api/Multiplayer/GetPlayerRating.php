<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$username = requireParam("username");
$user = User::get(JoomlaSupport::getUserId($username));

//Just need their rating
$rating = $user->getRating("rating_mp");
techo("RATING $rating");
