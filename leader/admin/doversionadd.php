<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	list($title, $version, $url, $desc) =
		getPostValues("title", "version", "url", "desc");

	// Check the variables
	if ($title == "" || $version == "" || $url == "")
		headerDie("Location: versionadd.php?error=2"); //Missing Info (die)

	// Check if the version exists
	$query = pdo_prepare("SELECT * FROM `versions` WHERE `version` = :version");
	$query->bind(":version", $version);
	$result = $query->execute();
	if ($result->rowCount())
		headerDie("Location: versionadd.php?error=3"); //Username exists (die)

	// More variables
	$submitter = getPostValue("username");

	// Add the user to the database!
	$query = pdo_prepare("INSERT INTO `versions` (`version`, `title`, `desc`, `url`, `submitter`) VALUES (:version, :title, :desc, :url, :submitter)");
	$query->bind(":version", $version);
	$query->bind(":title", $title);
	$query->bind(":desc", $desc);
	$query->bind(":url", $url);
	$query->bind(":submitter", $submitter);
	$result = $query->execute();
	if (!$result)
		headerDie("Location: versionadd.php?error=0");

	$id = $lb_connection->lastInsertId();

	//Now switch the config
	$oldConfigFile = JPATH_ROOT . "/pq/config/config.json";

	$newConfig = getPostValue("config");
	$newConfigFile = JPATH_ROOT . "/pq/config/config-$newConfig.json";
	$success = copy($newConfigFile, $oldConfigFile);

	//Add to PQ db as well
	define("PQ_RUN", 1);
	require(JPATH_ROOT . "/pq/leader/Database.php");
	require(JPATH_ROOT . "/pq/leader/DiscordLink.php");
	$query = $db->prepare("INSERT INTO ex82r_versions (`version`, `title`, `desc`, `url`, `id`) VALUES (:version, :title, :desc, :url, :id)");
	$query->bindValue(":version", $version);
	$query->bindValue(":title", $title);
	$query->bindValue(":desc", $desc);
	$query->bindValue(":url", $url);
	$query->bindValue(":id", $id);
	$query->execute();

	if ($success) {
		postNotify("update", $submitter);
		$msgData = DiscordLink::getInstance()->sendMessage("346037354516971523", "**Update {$title} Available:**\n{$desc}");
		if (array_key_exists("id", $msgData)) {
			DiscordLink::getInstance()->addReaction("346037354516971523", $msgData["id"], "pq:562546075983151104");
		}
	}

	if ($success) {
		headerDie("Location: versionadd.php?success=1");
	} else {
		headerDie("Location: versionadd.php?error=0");
	}
}

?>
