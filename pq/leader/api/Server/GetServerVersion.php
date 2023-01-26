<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$query = $db->prepare("SELECT * FROM `ex82r_versions` ORDER BY `id` DESC LIMIT 5");
$query->execute();

$versions = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($versions as &$row) {
	$row["time"] = date("Y-m-d h:i T", strtotime($row["timestamp"]));
}
unset($row);
techo(json_encode($versions));
