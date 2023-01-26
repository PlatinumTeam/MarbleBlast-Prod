<?php
$allow_nonwebchat = false;

// Open the database connection
require_once("opendb.php");

// Only send if we are from torque or submitting
if (isTorque() || isSubmitting()) {
   $login = checkPostLogin();
   if ($login != 7)
      sig($login);

   // Check if they sent the right vars
   if (!checkPostVars("start", "bracket"))
      sig(2); //Missing Information

   sig(16); //Starting

   // Send scores
   list($start) = getPostValues("start");

   $thousand = isset($_POST["thousand"]);
   $provisGames = getServerPref("provisGames");

   $random = getServerPref("autofillrandom") == "1";
   $start += 0;

   $query = pdo_prepare("SELECT * FROM `users` WHERE `banned` = 0 AND `guest` = 0 AND `showscores` = 1 AND `rating_mpgames` >= $provisGames ORDER BY `rating_mp` DESC LIMIT 0,2500");
   $result = $query->execute();

   $on = $start;

   if (!$result) {
	   sig(28); //No category
   }

   while (($row = $result->fetch()) !== false) {
      $on ++;
      if ($row["username"] == "")
         $row["username"] = "Nobody";
      if ($row["rating_mp"] == "")
         $row["rating_mp"] = 0;
      $provis = false;
      if ($row["rating_mpgames"] < $provisGames)
         $provis = true;
      $row["username"] = escapeName(getDisplayName($row["username"]));
      if ($provis)
         $row["username"] .= "<bitmap:platinum/client/ui/play/goldscore.png>";

      echo("SCORE {$row['username']} " . $row["rating_mp"] . " $on\n");
   }

   if (!$thousand) {
      $defaultname = escapeName(getServerPref("defaultname"));
      while ($on < $start + 10) {
         $on ++;
         $rating = 0;
         if ($random) {
            $rating = rand(0, 1000000);
         }
         echo("SCORE $defaultname $rating $on\n");
      }
   }
}
?>
