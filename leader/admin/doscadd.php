<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");

$access = getAccess();

if ($access > 0) {
	list($display) = getPostValues("display");
	$name = strtolower($display);
	$name = preg_replace('/[^a-z0-9]/i', '', $name);

   // Check the variables
   if ($name == "")
      headerDie("Location: scadd.php?error=1");
   if ($display == "")
   	headerDie("Location: scadd.php?error=2");

   // Check if the challenge exists by display name
   $query = pdo_prepare("SELECT * FROM `scdata` WHERE `display` = :display");
   $query->bind(":display", $display, PDO::PARAM_STR);
   $result = $query->execute();
   if ($result->rowCount())
      headerDie("Location: scadd.php?error=3");

   // Check if the challenge exists by name
   $basename = $name;
   $counter = 0;
   do {
   	if ($counter > 0)
			$name = $basename . $counter;
		$counter ++;
	   $query = pdo_prepare("SELECT * FROM `scdata` WHERE `name` = :name");
	   $query->bind(":name", $name);
	   $result = $query->execute();
	} while ($result->rowCount());

	// Tons of variables
	$missions         = $_POST["missions"];
	$platinumPercent  = $_POST["platinumPercent"];
	$ultimatePercent  = $_POST["ultimatePercent"];
	$minTimeout       = $_POST["minTimeout"];
	$maxTimeout       = $_POST["maxTimeout"];
	$bitmap           = $_POST["bitmap"];
	$diabled          = $_POST["disabled"];
	$points_2_win     = $_POST["points_2_win"];
	$points_2_tie     = $_POST["points_2_tie"];
	$points_2_lose    = $_POST["points_2_lose"];
	$points_2_forfeit = $_POST["points_2_forfeit"];
	$points_2_plat    = $_POST["points_2_plat"];
	$points_2_ult     = $_POST["points_2_ult"];
	$points_3_win     = $_POST["points_3_win"];
	$points_3_tieall  = $_POST["points_3_tieall"];
	$points_3_tie1    = $_POST["points_3_tie1"];
	$points_3_tie2    = $_POST["points_3_tie2"];
	$points_3_lose2   = $_POST["points_3_lose2"];
	$points_3_lose3   = $_POST["points_3_lose3"];
	$points_3_forfeit = $_POST["points_3_forfeit"];
	$points_3_plat    = $_POST["points_3_plat"];
	$points_3_ult     = $_POST["points_3_ult"];
	$points_4_win     = $_POST["points_4_win"];
	$points_4_tieall  = $_POST["points_4_tieall"];
	$points_4_tie1    = $_POST["points_4_tie1"];
	$points_4_tie2    = $_POST["points_4_tie2"];
	$points_4_tie3    = $_POST["points_4_tie3"];
	$points_4_lose2   = $_POST["points_4_lose2"];
	$points_4_lose3   = $_POST["points_4_lose3"];
	$points_4_lose4   = $_POST["points_4_lose4"];
	$points_4_forfeit = $_POST["points_4_forfeit"];
	$points_4_plat    = $_POST["points_4_plat"];
	$points_4_ult     = $_POST["points_4_ult"];

   // Add the challenge to the database!
   $query = pdo_prepare("INSERT INTO `scdata` (`name`, `display`, `missions`, `platinumPercent`, `ultimatePercent`, `minTimeout`, `maxTimeout`,
                                   `bitmap`, `disabled`, `points_2_win`, `points_2_tie`, `points_2_lose`, `points_2_forfeit`, `points_2_plat`,
                                   `points_2_ult`, `points_3_win`, `points_3_tieall`, `points_3_tie1`, `points_3_tie2`, `points_3_lose2`,
                                   `points_3_lose3`, `points_3_forfeit`, `points_3_plat`, `points_3_ult`, `points_4_win`, `points_4_tieall`,
                                   `points_4_tie1`, `points_4_tie2`, `points_4_tie3`, `points_4_lose2`, `points_4_lose3`, `points_4_lose4`,
                                   `points_4_forfeit`, `points_4_plat`, `points_4_ult`)
												VALUES
									(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	$query->bindParam(1,  $name,             PDO::PARAM_STR);
	$query->bindParam(2,  $display,          PDO::PARAM_STR);
	$query->bindParam(3,  $missions,         PDO::PARAM_STR);
	$query->bindParam(4,  $platinumPercent,  PDO::PARAM_INT);
	$query->bindParam(5,  $ultimatePercent,  PDO::PARAM_INT);
	$query->bindParam(6,  $minTimeout,       PDO::PARAM_INT);
	$query->bindParam(7,  $maxTimeout,       PDO::PARAM_INT);
	$query->bindParam(8,  $bitmap,           PDO::PARAM_STR);
	$query->bindParam(9,  $disabled,         PDO::PARAM_BOOL);
	$query->bindParam(10, $points_2_win,     PDO::PARAM_INT);
	$query->bindParam(11, $points_2_tie,     PDO::PARAM_INT);
	$query->bindParam(12, $points_2_lose,    PDO::PARAM_INT);
	$query->bindParam(13, $points_2_forfeit, PDO::PARAM_INT);
	$query->bindParam(14, $points_2_plat,    PDO::PARAM_INT);
	$query->bindParam(15, $points_2_ult,     PDO::PARAM_INT);
	$query->bindParam(16, $points_3_win,     PDO::PARAM_INT);
	$query->bindParam(17, $points_3_tieall,  PDO::PARAM_INT);
	$query->bindParam(18, $points_3_tie1,    PDO::PARAM_INT);
	$query->bindParam(19, $points_3_tie2,    PDO::PARAM_INT);
	$query->bindParam(20, $points_3_lose2,   PDO::PARAM_INT);
	$query->bindParam(21, $points_3_lose3,   PDO::PARAM_INT);
	$query->bindParam(22, $points_3_forfeit, PDO::PARAM_INT);
	$query->bindParam(23, $points_3_plat,    PDO::PARAM_INT);
	$query->bindParam(24, $points_3_ult,     PDO::PARAM_INT);
	$query->bindParam(25, $points_4_win,     PDO::PARAM_INT);
	$query->bindParam(26, $points_4_tieall,  PDO::PARAM_INT);
	$query->bindParam(27, $points_4_tie1,    PDO::PARAM_INT);
	$query->bindParam(28, $points_4_tie2,    PDO::PARAM_INT);
	$query->bindParam(29, $points_4_tie3,    PDO::PARAM_INT);
	$query->bindParam(30, $points_4_lose2,   PDO::PARAM_INT);
	$query->bindParam(31, $points_4_lose3,   PDO::PARAM_INT);
	$query->bindParam(32, $points_4_lose4,   PDO::PARAM_INT);
	$query->bindParam(33, $points_4_forfeit, PDO::PARAM_INT);
	$query->bindParam(34, $points_4_plat,    PDO::PARAM_INT);
	$query->bindParam(35, $points_4_ult,     PDO::PARAM_INT);

	$result = $query->execute();
	if ($result)
		headerDie("Location: scedit.php?success=2");
	else
		headerDie("Location: scadd.php?error=0");
}

?>
