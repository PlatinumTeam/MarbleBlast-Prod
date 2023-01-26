<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	$newmotd = getPostValue("motd");
   $submitter = getPostValue("username");

   // Add the user to the database!
   $query = pdo_prepare("INSERT INTO `motd` (`message`, `submitter`) VALUES (:message, :submitter)");
   $query->bind(":message", $newmotd);
   $query->bind(":submitter", $submitter);
	$result = $query->execute();
	if ($result)
		headerDie("Location: motdadd.php?success=1");
	else
		headerDie("Location: motdadd.php?error=0");
}

?>
