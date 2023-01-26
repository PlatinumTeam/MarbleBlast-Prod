<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	list($display) = getPostValues("name");
	$name = strtolower($display);
	$name = preg_replace('/[^a-z0-9]/i', '', $name);

   // Check the variables
   if ($name == "")
      headerDie("Location: categoryadd.php?error=1");
   if ($display == "")
   	headerDie("Location: categoryadd.php?error=2");

   // Check if the category exists by display name
   $query = pdo_prepare("SELECT * FROM `categories` WHERE `display` = :display");
   $query->bind(":display", $display, PDO::PARAM_STR);
   $result = $query->execute();
   if ($result->rowCount())
      headerDie("Location: categoryadd.php?error=3");

   // Check if the category exists by name
   $basename = $name;
   $counter = 0;
   do {
   	if ($counter > 0)
			$name = $basename . $counter;
		$counter ++;
	   $query = pdo_prepare("SELECT * FROM `categories` WHERE `name` = :name");
	   $query->bind(":name", $name, PDO::PARAM_STR);
	   $result = $query->execute();
	} while ($result->rowCount());

   // Add the user to the database!
   $query = pdo_prepare("INSERT INTO `categories` (`name`, `display`) VALUES (:name, :display)");
   $query->bind(":name", $name, PDO::PARAM_STR);
   $query->bind(":display", $display, PDO::PARAM_STR);
	$result = $query->execute();
	if ($result)
		headerDie("Location: categories.php?success=2");
	else
		headerDie("Location: categoryadd.php?error=0");
}

?>
