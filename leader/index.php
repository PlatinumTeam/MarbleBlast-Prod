<?php
//Send them to marbleblast.com/index.php
$newAddress = $_SERVER["REQUEST_URI"];
if (strstr($newAddress, "/leader/") != -1) {
	$newAddress = str_replace("/leader/", "/", $newAddress);
}
header("Location: http://marbleblast.com{$newAddress}");
header("HTTP/1.1 301 Permanently Moved");
?>