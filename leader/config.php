<?php
// You are not allowed to view this file
header("HTTP/1.1 404 Not Found");

// Signals
require_once("sig.php");
require_once(dirname(dirname(__DIR__)) . "/db.php");

/**
 * @var boolean Allow web access of scripts via non-webchat means
 * @version 0.1
 * @package leader
 * @access public
 */
$allow_web_testing = false;
/**
 * @var string The hostname for the MySQL connection
 * @version 0.1
 * @package leader
 * @access public
 */
$mysql_host = MBDB::getDatabaseHost("platinum");
/**
 * @var string The username for the MySQL connection
 * @version 0.1
 * @package leader
 * @access public
 */
$mysql_user = MBDB::getDatabaseUser("platinum");
/**
 * @var string The password for the MySQL connection
 * @version 0.1
 * @package leader
 * @access public
 */
$mysql_pass = MBDB::getDatabasePass("platinum");
/**
 * @var string The database for the MySQL connection
 * @version 0.1
 * @package leader
 * @access public
 */
$mysql_data = MBDB::getDatabaseName("platinum");
/**
 * @var string The phpMyAdmin server for displaying on the admin panel (optional)
 * @version 0.1
 * @package leader
 * @access public
 */
$pma_url = "XXXXXXXXXXXXXXXXXXXXXXX";
?>
