<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > (MINIMUM_ACCESS - 1)) {
   if (checkPostVars("toggle")) {
      $id = getPostValue("toggle");
      $query = pdo_prepare("UPDATE `qotd` SET `selected` = !`selected` WHERE `id` = :id");
      $query->bind(":id", $id);
      $result = $query->execute();
      if ($result)
         headerDie("Location: qotdadd.php?success=1");
      else
         headerDie("Location: qotdadd.php?error=0");
      return;
   }
	$text = getPostValue("qotd");
   $username = getPostValue("user");

   // Deactivate the old qotd
   $query = pdo_prepare("UPDATE `qotd` SET `selected` = 0");
	$result = $query->execute();

   // Add the new qotd
   $query = pdo_prepare("INSERT INTO `qotd` (`text`, `username`, `selected`, `submitter`) VALUES (:text, :username, 1, :submitter)");
   $query->bind(":text", $text);
   $query->bind(":username", $username);
   $query->bind(":submitter", getPostValue("username"));
	$result = $query->execute();
	if ($result)
		headerDie("Location: qotdadd.php?success=1");
	else
		headerDie("Location: qotdadd.php?error=0");
}

?>
