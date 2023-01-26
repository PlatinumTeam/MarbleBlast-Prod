<?php
$allow_nonwebchat = true;
$ignore_keys = true;

// Open the database connection
require("opendb.php");

$code = 10;
if (array_key_exists("e", $_GET))
   $code = $_GET["e"];

// Check login (see opendb.php)
$login = checkPostLogin();
if ($login == 7) { // 7 is success
   $username = getPostValue("username");

   if (array_key_exists("admin", $_GET)) {
      setCookie("username", "", time() - 60*60*24*7);
      setCookie("password", "", time() - 60*60*24*7);
      headerDie("Location: admin/admin.php");
   }
   if (!getUserLoggedIn($username)) {
      if (isTorque())
         sig(9); //Not logged in
      else {
         setCookie("username", "", time() - 60*60*24*7);
         setCookie("password", "", time() - 60*60*24*7);
         headerDie("Location: login.php?e=$code");
      }
   }
   // Notify the server of our logout
   if (getUserBanned($username) == 0)
	   postNotify("logout", $username, -1);

   $query = pdo_prepare("DELETE FROM `loggedin` WHERE `username` = :username LIMIT 1");
   $query->bind(":username", $username);
   $query->execute();

   if (isGuest($username)) {
      $query = pdo_prepare("DELETE FROM `users` WHERE `username` = :username LIMIT 1");
      $query->bind(":username", $username);
      $query->execute();
   }

   $query = pdo_prepare("DELETE FROM `usedkeys` WHERE `username` = :username  LIMIT 1");
   $query->bind(":username", $username);
   $query->execute();

   // Track their login time
   $loginTime = getTrackData("lastlogin", $username);

   if ($loginTime) {
      // Clean up
      deleteTrackDataType("lastlogin", $username);

      $totalTime = getServerTime() - $loginTime;
      trackData("logintime", $username, $totalTime);
   }

   // Send a success if we're in Torque, otherwise redirect to login
   if (isTorque())
      sig(10); //Logout Successful
   else {
      setCookie("username", "", time() - 60*60*24*7);
      setCookie("password", "", time() - 60*60*24*7);
      header("Location: login.php?e=$code");
   }
} else { // Die
   if (isTorque())
      sig($login);
   else {
      setCookie("username", "", time() - 60*60*24*7);
      setCookie("password", "", time() - 60*60*24*7);
	   if (array_key_exists("admin", $_GET)) {
	   	if ($login == 27)
	   		headerDie("Location: admin/admin.php?banned=true");
	   	else
	   		headerDie("Location: admin/admin.php");
	   } else
	      header("Location: login.php?e=$login");
   }
}
?>
