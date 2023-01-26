<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > (MINIMUM_ACCESS - 1)) {
	//Restart the server, gogogogogogo
	$port = (int)$_GET["port"];

	$dir = escapeshellarg(dirname(dirname(__FILE__)));

	// echo("cd $dir && sh ./restartchat.sh $port");

	//Restart
	if ($port == 28003) {
		$dir = "/var/www/prod/marbleblast.com/public_html/leader/";
	}
	if ($port == 39002) {
		$dir = "/var/www/dev/marbleblast.com/newchat/";
	}
	exec("cd $dir && bash ./restartchat.sh $port");

	header("Location: admin.php");
}

?>
