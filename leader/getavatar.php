<?php
$allow_nonwebchat = true;
$ignore_keys = true;

// Open the database connection
require("opendb.php");

if (array_key_exists("username", $_GET)) {
	$username = $_GET["username"];

	//Get their id
	$id = JUserHelper::getUserId($username);

	// Grab Avatar
	$kundb = JFactory::getDbo();
	$kunquery = $kundb->getQuery(true);
	$kunquery->select('avatar');
	$kunquery->from($kundb->quoteName('bv2xj_kunena_users'));
	$kunquery->where($kundb->quoteName('userid')." = ".$kundb->quote($id));
	$kundb->setQuery($kunquery);
	// Save this row for use later in this file ..
	$userinfo_kun = $kundb->loadAssoc();
	// Grab user avatar from kunena url
	$avatar = $userinfo_kun['avatar'];
	// 3/3/14 : This shows the default avatar if this user has no selected avatar.
	if ($avatar == "") {
		$avatar = "s_nophoto.jpg";
	}

	//Actual path
	$avatar = "../media/kunena/avatars/$avatar";

	//Make sure it exists
	if (is_file($avatar)) {
		//Get MIME-Type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($avatar);
		finfo_close($finfo);

		//Let em know what it is
		header("HTTP/1.1 200 OK");
		header("Content-Type: $type");
		header("Content-Length: " . filesize($avatar));

		//Read file to stdout
		readfile($avatar);
	} else {
		//Not found
		header("HTTP/1.1 404 Not Found");
	}
}

?>