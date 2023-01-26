<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	// Tons of variables
	$display          = ($_POST["display"]);
	$missions         = ($_POST["missions"]);
	$bitmap           = ($_POST["bitmap"]);
	$disabled         = ($_POST["disabled"]);
	$platinumPercent  = floatval($_POST["platinumPercent"]);
	$ultimatePercent  = floatval($_POST["ultimatePercent"]);
	$minTimeout       = intval($_POST["minTimeout"]);
	$maxTimeout       = intval($_POST["maxTimeout"]);
	$points_2_win     = intval($_POST["points_2_win"]);
	$points_2_tie     = intval($_POST["points_2_tie"]);
	$points_2_lose    = intval($_POST["points_2_lose"]);
	$points_2_forfeit = intval($_POST["points_2_forfeit"]);
	$points_2_plat    = intval($_POST["points_2_plat"]);
	$points_2_ult     = intval($_POST["points_2_ult"]);
	$points_3_win     = intval($_POST["points_3_win"]);
	$points_3_tieall  = intval($_POST["points_3_tieall"]);
	$points_3_tie1    = intval($_POST["points_3_tie1"]);
	$points_3_tie2    = intval($_POST["points_3_tie2"]);
	$points_3_lose2   = intval($_POST["points_3_lose2"]);
	$points_3_lose3   = intval($_POST["points_3_lose3"]);
	$points_3_forfeit = intval($_POST["points_3_forfeit"]);
	$points_3_plat    = intval($_POST["points_3_plat"]);
	$points_3_ult     = intval($_POST["points_3_ult"]);
	$points_4_win     = intval($_POST["points_4_win"]);
	$points_4_tieall  = intval($_POST["points_4_tieall"]);
	$points_4_tie1    = intval($_POST["points_4_tie1"]);
	$points_4_tie2    = intval($_POST["points_4_tie2"]);
	$points_4_tie3    = intval($_POST["points_4_tie3"]);
	$points_4_lose2   = intval($_POST["points_4_lose2"]);
	$points_4_lose3   = intval($_POST["points_4_lose3"]);
	$points_4_lose4   = intval($_POST["points_4_lose4"]);
	$points_4_forfeit = intval($_POST["points_4_forfeit"]);
	$points_4_plat    = intval($_POST["points_4_plat"]);
	$points_4_ult     = intval($_POST["points_4_ult"]);
	$id               = intval($_POST["id"]);

   // Add the challenge to the database!
   $query = pdo_prepare("UPDATE `scdata` SET `display` = ?, `missions` = ?, `platinumPercent` = ?, `ultimatePercent` = ?,
   			`minTimeout` = ?, `maxTimeout` = ?, `bitmap` = ?, `disabled` = ?, `points_2_win` = ?,
   			`points_2_tie` = ?, `points_2_lose` = ?, `points_2_forfeit` = ?,
   			`points_2_plat` = ?, `points_2_ult` = ?, `points_3_win` = ?,
   			`points_3_tieall` = ?, `points_3_tie1` = ?, `points_3_tie2` = ?,
				`points_3_lose2` = ?, `points_3_lose3` = ?, `points_3_forfeit` = ?,
				`points_3_plat` = ?, `points_3_ult` = ?, `points_4_win` = ?, `points_4_tieall` = ?,
   			`points_4_tie1` = ?, `points_4_tie2` = ?, `points_4_tie3` = ?,
   			`points_4_lose2` = ?, `points_4_lose3` = ?, `points_4_lose4` = ?,
   			`points_4_forfeit` = ?, `points_4_plat` = ?, `points_4_ult` = ?
   			WHERE `id` = ?");

   $query->bindParam(1,  $display,          PDO::PARAM_STR);
   $query->bindParam(2,  $missions,         PDO::PARAM_STR);
	$query->bindParam(3,  $platinumPercent,  PDO::PARAM_INT);
	$query->bindParam(4,  $ultimatePercent,  PDO::PARAM_INT);
	$query->bindParam(5,  $minTimeout,       PDO::PARAM_INT);
	$query->bindParam(6,  $maxTimeout,       PDO::PARAM_INT);
	$query->bindParam(7,  $bitmap,           PDO::PARAM_STR);
	$query->bindParam(8,  $disabled,         PDO::PARAM_BOOL);
	$query->bindParam(9,  $points_2_win,     PDO::PARAM_INT);
	$query->bindParam(10, $points_2_tie,     PDO::PARAM_INT);
	$query->bindParam(11, $points_2_lose,    PDO::PARAM_INT);
	$query->bindParam(12, $points_2_forfeit, PDO::PARAM_INT);
	$query->bindParam(13, $points_2_plat,    PDO::PARAM_INT);
	$query->bindParam(14, $points_2_ult,     PDO::PARAM_INT);
	$query->bindParam(15, $points_3_win,     PDO::PARAM_INT);
	$query->bindParam(16, $points_3_tieall,  PDO::PARAM_INT);
	$query->bindParam(17, $points_3_tie1,    PDO::PARAM_INT);
	$query->bindParam(18, $points_3_tie2,    PDO::PARAM_INT);
	$query->bindParam(19, $points_3_lose2,   PDO::PARAM_INT);
	$query->bindParam(20, $points_3_lose3,   PDO::PARAM_INT);
	$query->bindParam(21, $points_3_forfeit, PDO::PARAM_INT);
	$query->bindParam(22, $points_3_plat,    PDO::PARAM_INT);
	$query->bindParam(23, $points_3_ult,     PDO::PARAM_INT);
	$query->bindParam(24, $points_4_win,     PDO::PARAM_INT);
	$query->bindParam(25, $points_4_tieall,  PDO::PARAM_INT);
	$query->bindParam(26, $points_4_tie1,    PDO::PARAM_INT);
	$query->bindParam(27, $points_4_tie2,    PDO::PARAM_INT);
	$query->bindParam(28, $points_4_tie3,    PDO::PARAM_INT);
	$query->bindParam(29, $points_4_lose2,   PDO::PARAM_INT);
	$query->bindParam(30, $points_4_lose3,   PDO::PARAM_INT);
	$query->bindParam(31, $points_4_lose4,   PDO::PARAM_INT);
	$query->bindParam(32, $points_4_forfeit, PDO::PARAM_INT);
	$query->bindParam(33, $points_4_plat,    PDO::PARAM_INT);
	$query->bindParam(34, $points_4_ult,     PDO::PARAM_INT);
	$query->bindParam(35, $id,               PDO::PARAM_INT);

	$result = $query->execute();
	if ($result)
		headerDie("Location: scedit.php?success=3");
	else
	echo($query);
//		headerDie("Location: scedit.php?error=1");
}

?>
