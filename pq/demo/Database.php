<?php

require(dirname(dirname(dirname(__DIR__))) . "/db.php"); //Config
require("Utils.php");

class Database extends \PDO {

	protected $tablePrefix;

	public function __construct($dbname, $prefix) {
		//Pass params in here
		$dsn = "mysql:dbname=" . \MBDB::getDatabaseName($dbname) . ";host=" . \MBDB::getDatabaseHost($dbname);
		parent::__construct($dsn, \MBDB::getDatabaseUser($dbname), \MBDB::getDatabasePass($dbname));
		$this->tablePrefix = $prefix;

		if ($this->errorCode()) {
			print_r($this->errorInfo());
		}
	}

	public function prepare($statement, $driver_options = array()) {
		$statement = str_replace("@_", $this->tablePrefix, $statement);
		return parent::prepare($statement, $driver_options);
	}

	public function getSetting($key) {
		$query = $this->prepare("SELECT `value` FROM `@_settings` WHERE `key` = :key");
		$query->bindParam(":key", $key);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function setSetting($key, $value) {
		$query = $this->prepare("UPDATE `@_settings` SET `value` = :value WHERE `key` = :key");
		$query->bindParam(":value", $value);
		$query->bindParam(":key", $key);
		$query->execute();
	}
}

$dbname = (param("full") === null ? "pqdemo" : "pq");

$db = new Database($dbname, "lw3qp_");
