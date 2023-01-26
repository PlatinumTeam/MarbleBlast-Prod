<?php

if (defined("TIME_LOGGING")) {
	function utime() {
		return (float)(vsprintf('%d.%06d', gettimeofday()));
	}
	$start = utime();
	register_shutdown_function(function () {
		global $start;
		echo("Took " . (utime() - $start) . " s\n");
	});
}

error_reporting(E_ALL & ~E_NOTICE);

/**
 * Echo a string to script, with file prefix and a newline appended
 * @param string $contents String to echo
 */
function techo($contents) {
	if ($_SERVER["HTTP_USER_AGENT"] === "Torque 1.0") {
		$script = basename($_SERVER["PHP_SELF"], ".php");
		$id = param("req") ?? $script;
		$start = "pq " . $id . " ";
		$contents = str_replace("\n", "\n" . $start, $contents);
		echo($start . $contents . "\n");
	} else {
		echo($contents . "\n");
	}
}

/**
 * Get the value of a cookie/request parameter
 * @param string $name Parameter name
 * @return mixed|null Parameter value or null if none exists
 */
function param($name) {
	if (array_key_exists($name, $_COOKIE))
		return $_COOKIE[$name];
	if (array_key_exists($name, $_REQUEST))
		return $_REQUEST[$name];
	return null;
}

/**
 * Get a parameter value, and die with an error if it doesn't exist
 * @param string $name Parameter name
 * @return string
 */
function requireParam($name) {
	$value = param($name);
	if ($value === null)
		error("ARGUMENT {$name}");
	return $value;
}

/**
 * Kills the script outputting the given text
 * @param string $text Error message
 * @noreturn
 */
function error($text) {
	techo($text);
	die();
}

/**
 * Fetch results of a PDO query in an associative array, with sub arrays for each table
 * that is a part of the result set.
 * @param PDOStatement $query The query to fetch from
 * @return array|bool
 */
function fetchTableAssociative(PDOStatement $query) {
	//Get info about all the columns in the query
	$meta = [];
	for ($i = 0; $i < $query->columnCount(); $i ++) {
		$meta[] = $query->getColumnMeta($i);
	}

	//Fetch it numerically, the columns line up with the meta
	$result = $query->fetch(PDO::FETCH_NUM);
	if ($result === false) {
		return false;
	}

	//Create an array that holds all the tables associatively
	$structured = [];
	for ($i = 0; $i < count($meta); $i ++) {
		//Get each column in the query for filling $structured
		$column = $meta[$i];
		$table = $column["table"];
		$name = $column["name"];

		//If we don't have a root array for this table yet, create one
		if (!array_key_exists($table, $structured)) {
			$structured[$table] = [];
		}
		//If this key has already been used in this table, just ignore it
		if (array_key_exists($name, $structured[$table])) {
			continue;
		}
		//Assign it to the table in the structure
		$structured[$table][$name] = $result[$i];
	}
	return $structured;
}

/**
 * Fetch all results of a query in associative arrays, with sub arrays for each table
 * that is a part of the result set.
 * @param PDOStatement $query Query to fetch from
 * @return array
 */
function fetchAllTableAssociative(PDOStatement $query) {
	$results = [];
	while (($result = fetchTableAssociative($query)) !== false) {
		$results[] = $result;
	}
	return $results;
}

/**
 * Fetch a 2-column query result as an associative array of firstCol => secondCol
 * @param PDOStatement $query
 * @return array|bool Result array or false on failure
 */
function fetchQueryAssociative(PDOStatement $query) {
	$result = $query->fetchAll();
	if ($result === FALSE)
		return FALSE;
	$array = [];
	foreach ($result as list($key, $value)) {
		$array[$key] = $value;
	}
	return $array;
}

/**
 * Execute a query or die if it fails
 * @param PDOStatement $query
 */
function requireExecute(PDOStatement $query) {
	if (!$query->execute()) {
		techo("FAILURE");
		techo("Error code: " . $query->errorCode());
		techo(json_encode($query->errorInfo()));
		die();
	}
}

/**
 * Flatten a POST request array of arrays into an array of objects
 * @param array $array
 * @return array
 */
function flattenPOSTArray($array) {
	$result = [];

	//Find max count so we know how many objects to construct
	$maxCount = 0;
	foreach ($array as $name => $values) {
		$maxCount = max($maxCount, count($values));
	}

	//Iterate over the items in the arrays and flatten them into objects
	for ($i = 0; $i < $maxCount; $i ++) {
		$object = [];
		foreach ($array as $name => $values) {
			if (count($values) > $i) {
				$object[$name] = $values[$i];
			}
		}
		$result[] = $object;
	}
	return $result;
}

/**
 * Strip a level's basename to the old leaderboards' format of lowercase alphanum
 * @param string $name Level name
 * @return string
 */
function stripLevel($name) {
	$stripped = strtolower($name);
	$stripped = preg_replace('/[^a-z0-9]/s', '', $stripped);
	return $stripped;
}

/**
 * Get a single space-separated word from a string
 * @param string $string
 * @param int $index
 * @return string
 */
function getWord($string, $index) {
	$words = explode(" ", $string);
	return $words[$index];
}

/**
 * Get a subset of space-separated words in a string
 * @param string $string
 * @param int $start Starting word index
 * @param int $end Ending word index
 * @return array
 */
function getWords($string, $start, $end) {
	$words = explode(" ", $string);
	return array_slice($words, $start, 1 + ($end - $start));
}

/**
 * Number of space-separated words in a string
 * @param string $string
 * @return int
 */
function getWordCount($string) {
	//Edge case when the string is empty
	if (strlen($string) == 0)
		return 0;

	$words = explode(" ", $string);
	return count($words);
}

/**
 * First space-separated of a string
 * @param string $string
 * @return string
 */
function firstWord($string) {
	return getWord($string, 0);
}

/**
 * Formats a time, in milliseconds, to xx:xx.xxx format. Zero is considered 99:59.999
 * @param int  $time      Time in ms
 * @param bool $timeBonus If true, don't report zero as 99:59.999
 * @return string Formatted time
 */
function formatTime($time, $timeBonus = false) {
	if ($time == 0 || $time == 5998999) {
		if ($timeBonus && $time == 0)
			return "00:00.000";
		return "99:59.999";
	}
	$neg = $time < 0;
	$time = abs($time);
	$ms = $time % 1000;
	$time = ($time - $ms) / 1000;
	$s  = $time % 60;
	$m  = ($time - $s) / 60;
	return ($neg ? "-" : "") . str_pad($m, 2, "0", STR_PAD_LEFT) . ":" . str_pad($s, 2, "0", STR_PAD_LEFT) . "." . str_pad($ms, 3, "0", STR_PAD_LEFT);
}

/**
 * Generates a random string of alphanumeric characters
 * @var int $length The length of the string
 * @return string The randomly generated string
 */
function strRand($length = 64) {
	$chars = "abcdefghijklmnopqrstuvwqyz0123456789";

	//Get random seed from microtime
	list($usec, $sec) = explode(" ", microtime());
	//Do some cool maths
	$seed = (float) $sec + ((float) $usec * 100000);
	//And set the seed
	mt_srand($seed);

	//Generate
	$str = "";
	$charc = strlen($chars);

	for ($i = 0; $length > $i; $i ++) {
		$str .= $chars[mt_rand(0, $charc - 1)];
	}

	return $str;
}

/**
 * Remove some special chars from users' display names
 * @param string $display The unfiltered display name
 * @return string A sanitized, game-renderable display name
 */
function sanitizeDisplayName($display) {
	return substr(str_replace(array("[", "]"), "", mb_convert_encoding($display, "ASCII")), 0, 24);
}

/**
 * Encodes a string with base64, except splitting it up into chunks because
 * Torque likes to crash when you give it long strings
 * @param string $data      Input data to encode
 * @param int    $blockSize Maximum substring "block" size (default 1024)
 * @return array Array of "block"s of base64-encoded data
 */
function tbase64_encode($data, $blockSize = 1024) {
	$output = [];
	//Because Torque gets whiny when you try to write a lot of data
	for ($i = 0; $i < strlen($data); $i += $blockSize) {
		$chars = substr($data, $i, $blockSize);
		$output[] = base64_encode($chars);
	}
	return $output;
}
