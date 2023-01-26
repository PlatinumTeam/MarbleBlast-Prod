<?php

require_once(dirname(dirname(dirname(__DIR__))) . "/db.php"); //Config

class Database extends \PDO {

	protected $tablePrefixes;

	public function __construct($dbname, $prefixes = []) {
		//Pass params in here
		$dsn = "mysql:dbname=" . \MBDB::getDatabaseName($dbname) . ";host=" . \MBDB::getDatabaseHost($dbname);
		parent::__construct($dsn, \MBDB::getDatabaseUser($dbname), \MBDB::getDatabasePass($dbname), [
			PDO::ATTR_EMULATE_PREPARES => false
		]);
		$this->tablePrefixes = $prefixes;

		$error = $this->errorCode();
		if ($error != "00000") {
			print_r($this->errorInfo());
		}
	}

	public function prepare($statement, /** @noinspection PhpSignatureMismatchDuringInheritanceInspection */ $driver_options = array()) {
		//You can see exactly how lazy I am with this prefix stuff
		foreach ($this->tablePrefixes as $prefix => $replacement) {
			$statement = str_replace($prefix, $replacement, $statement);
		}
		return parent::prepare($statement, $driver_options);
	}

	public function getSetting($key) {
		$query = $this->prepare("SELECT `value` FROM `ex82r_settings` WHERE `key` = :key");
		$query->bindValue(":key", $key);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function setSetting($key, $value) {
		$query = $this->prepare("UPDATE `ex82r_settings` SET `value` = :value WHERE `key` = :key");
		$query->bindValue(":value", $value);
		$query->bindValue(":key", $key);
		$query->execute();
	}
}

$db = new Database("pq", [/* "ex82r_" => "ex82r_", "prod_" => "prod" */]);
$pdb = new Database("platinum", []);
