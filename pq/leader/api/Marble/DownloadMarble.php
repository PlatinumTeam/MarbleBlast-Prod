<?php
define("PQ_RUN", true);
require_once("../../Framework.php");

$id = requireParam("id");

$result = [];

$query = $db->prepare("
	SELECT `id`, `category_id`, `name`, `shape_file`, `skin`, `shaderV`, `shaderF` FROM `ex82r_marbles`
	WHERE id = :id
");
$query->bindValue(":id", $id);
$query->execute();

if ($query->rowCount() === 0) {
	error("Unknown marble id");
}

$row = $query->fetch(PDO::FETCH_ASSOC);

$choices = [
	"shape_file",
	"skin",
	"shaderV",
	"shaderF",
];

$choice = requireParam("choice");

if (!in_array($choice, $choices)) {
	error("Unknown file choice");
}

//Find the marble's data
$base = DATA_DIR . "/Marbles/" . $id . "/";
$file = pathinfo($row[$choice], PATHINFO_BASENAME);
if ($choice === "skin") {
	if (is_file($base . $file . ".png")) {
		$file .= ".png";
	} else if (is_file($base . $file . ".jpg")) {
		$file .= ".jpg";
	}
}

if (!is_file($file)) {
	error("Cannot find file");
}

