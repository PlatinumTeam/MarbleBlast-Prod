<?php
$allow_nonwebchat = true;
$admin_page = true;

require_once("../opendb.php");
require_once("defaults.php");
require_once("../jsupport.php");

$access = getAccess();

//----------------------------------------------------------------------
// Document start
documentHeader("Chat Log");

   //-------------------------------------------------------------------
   // Navbar start

   navbarCreate("MBP Admin");
   navbarAddSet("default");
   if ($access > 1) {
      navbarAddSet("admin");
      navbarAddItem("Log Out", "../logout.php?admin=true");
      navbarAddItem("Logged in as " . getPostValue("username"));
   } else if ($access == 1) {
      navbarAddSet("mod");
      navbarAddItem("Log Out", "../logout.php?admin=true");
      navbarAddItem("Logged in as " . getPostValue("username"));
   }
   navbarAddItem("Access: " . accessTitle($access));
   navbarEnd();

   // Navbar end
   //------------------------------------––––––––––––––––––––––––––––---


function htmlEscape($str) {
   $str = htmlentities($str);
   $str = stripslashes($str);
   return $str;
}

if ($access > (MINIMUM_ACCESS - 1)) {

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

?>

<style type="text/css">
#content {
   overflow: scroll;
   height: 80%;
   width: 100%;
}
</style>
<div class="container-fluid">
   <div class="row-fluid">
      <?php sidebarCreate(); ?>
      <div class="span9 well">
         <h1 class="text-center">Chat Log</h1>
         <br>
         <br>
         <div class="text-center"><strong>Currently <?php echo(pdo_prepare("SELECT CURRENT_TIMESTAMP")->execute()->fetchIdx(0));?></strong></div>
         <div id="content">
<?php

$limit = (array_key_exists("limit", $_GET) ? (int)$_GET["limit"] : 1000);

if ($limit < 0) {
   if ($access > 1)
      $limit = pdo_prepare("SELECT COUNT(*) FROM `chat`")->execute()->fetchIdx(0);
   else
      $limit = 1000;
}

if ($limit > 20000 && $access < 2)
   $limit = 1000;
if ($limit == -1 && $access == 2) {
   $limit = 1000000;
}


$order = array_key_exists("asc", $_GET) ? "ASC" : "DESC";

if (array_key_exists("start", $_GET)) {
   $start = (int)$_GET["start"];
   $query = pdo_prepare("SELECT * FROM `chat` WHERE (`destination` = '' OR `access` <= :access) ORDER BY `time` $order LIMIT $start,$limit");
} else {
   $query = pdo_prepare("SELECT * FROM `chat` WHERE (`destination` = '' OR `access` <= :access) ORDER BY `time` $order LIMIT $limit");
}
$query->bind(":access", $access);

if ($limit <= 20000 && !array_key_exists("start", $_GET)) {
   $oldest = 0;

   $rows = array();

   $result = $query->execute();
   while (($row = $result->fetch()) !== false) {
      array_push($rows, array("Type" => "Chat", "Data" => $row));
      
      if ($oldest == 0)
         $oldest = $row["time"];
      $oldest = min($oldest, $row["time"]);
   }

   $notifyquery = pdo_prepare("SELECT * FROM `notify` WHERE `time` > :oldest ORDER BY `time` $order");
   $notifyquery->bind(":oldest", $oldest);
   $notifyresult = $notifyquery->execute();

   while (($row = $notifyresult->fetch()) !== false) {
      array_push($rows, array("Type" => "Notify", "Data" => $row));
   }

   usort($rows, function ($a, $b) {
      if ($a["Data"]["time"] < $b["Data"]["time"])
         return 1;
      if ($a["Data"]["time"] > $b["Data"]["time"])
         return -1;
      if ($a["Data"]["time"] == $b["Data"]["time"])
         return 0;
   });

   foreach ($rows as $row) {
      if ($row["Type"] == "Chat") {
         $dest = $row["Data"]["destination"];
         $timestamp = $row["Data"]["timestamp"];
         $time = $row["Data"]["time"];
         $message = htmlspecialchars(urldecode($row["Data"]["message"]));
         if ($dest == "")
      	   echo("$timestamp: <b>{$row['Data']['username']}</b>: $message<br>\n");
      	else
      	   echo("$timestamp: <b>{$row['Data']['username']}</b> to <b>$dest</b>: $message<br>\n");
      } else if ($row["Type"] == "Notify") {
         $ntimestamp = $row["Data"]["timestamp"];
         $ntime = $row["Data"]["time"];
         $nname = $row["Data"]["username"];
         $naccess = $row["Data"]["access"];
         $ntype = $row["Data"]["type"];
         $nmessage = htmlEscape($row["Data"]["message"]);
         echo("$ntimestamp: Notify from <b>$nname</b> (required access $naccess): <b>$ntype</b> with message \"$nmessage\"<br>\n");
      }
   }
} else {
   $result = $query->execute();
   while (($row = $result->fetch()) !== false) {
      $dest = $row["destination"];
      $timestamp = $row["timestamp"];
      $time = $row["time"];
      $message = htmlspecialchars(urldecode($row["message"]));
      if ($dest == "")
         echo("$timestamp: <b>{$row['username']}</b>: $message<br>\n");
      else
         echo("$timestamp: <b>{$row['username']}</b> to <b>$dest</b>: $message<br>\n");
   }
}
?>
         </div>
         <div>
            Show...
            <a href="chatlog.php?limit=2500">2500</a>
            <a href="chatlog.php?limit=5000">5000</a>
            <a href="chatlog.php?limit=10000">10000</a>
            <a href="chatlog.php?limit=20000">20000</a>
            <?php if ($access > 1) { ?>
            <a href="chatlog.php?limit=-1">All</a>
            <?php } ?>
         </div>
      </div>
   </div>   
</div>
   <?php
} else {
   accessDenied();
}

documentFooter();
// Document end
//----------------------------------------------------------------------
?>
