<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$showHidden = Login::isPrivilege("pq.test.marbleList");

$result = [];

$query = $db->prepare("
	SELECT id, name, file_base FROM ex82r_marble_categories
	WHERE (disabled = 0) OR :show = 1
	ORDER BY sort ASC
");
$query->bindValue(":show", $showHidden);
$query->execute();
$result["categories"] = $query->fetchAll(PDO::FETCH_ASSOC);

$query = $db->prepare("
	SELECT m.id, category_id, m.name, shape_file, skin, shaderV, shaderF FROM ex82r_marbles m
	JOIN ex82r_marble_categories c on m.category_id = c.id
	WHERE (c.disabled = 0 AND m.disabled = 0) OR :show = 1
	ORDER BY category_id ASC, m.sort ASC
");
$query->bindValue(":show", $showHidden);
$query->execute();
$result["Marbles"] = $query->fetchAll(PDO::FETCH_ASSOC);

techo(json_encode($result));
