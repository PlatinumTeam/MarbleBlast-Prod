<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > (MINIMUM_ACCESS - 1)) {
	//Restart the server, gogogogogogo
	$port = (int)$_GET["port"];

	//Restart
	$dir = "/var/www/prod/marbleblast.com/public_html/leader/MP_Master/";
	exec("cd $dir && bash ./restartmaster.sh");

	header("Location: admin.php");
}

?>
