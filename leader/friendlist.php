<?php
$allow_nonwebchat = false;
$ignore_keys = true;

// Open the database connection
require_once("opendb.php");

// Only add if we are from torque or submitting
if (isTorque() || isSubmitting()) {
   $login = checkPostLogin();
   if ($login != 7)
      sig($login);

   sig(16); //List start

   list($username) = getPostValues("username");

   $query = pdo_prepare("SELECT `friendid` FROM `friends` WHERE `username` = :username");
   $query->bind(":username", $username);
   $result = $query->execute();

   if ($result->rowCount()) {
      while (($row = $result->fetch()) !== false) {
         $userid = $row["friendid"];
         $query = pdo_prepare("SELECT * FROM `users` WHERE `id` = :userid");
         $query->bind(":userid", $userid);
         list($friend) = $query->execute()->fetchIdx();
         $friend = escapeName($friend);

         echo("FRIEND $friend\n");
      }
   }
}
?>
