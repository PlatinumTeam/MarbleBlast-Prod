<?php
if (!defined("PQ_RUN")) {
	die("Unauthorized Access.\n");
}

define("BASE_DIR", __DIR__);
define("DATA_DIR", BASE_DIR . "/data");
define("JOOMLA_BASE", dirname(dirname(__DIR__)));

//Base classes
require("Utils.php");
require("Database.php");

//Data classes
require("User.php");
require("Mission.php");

//Account support
require("JoomlaSupport.php");
require("Login.php");
require("Platinum.php");
require("DiscordLink.php");

//Score support
require("ScoreUtils.php");
require("SPRatings.php");
require("MPRatings.php");
