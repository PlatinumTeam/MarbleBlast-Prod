<?php
// You are not allowed to view this file
header("HTTP/1.1 404 Not Found");

/**
 * Signal names for each signal num
 * @access public
 * @version 0.1
 * @var Array int=>string with signal names for each signal number
 */
$SigName[0]  = "SERVERERROR";     // Server error
$SigName[1]  = "FINISH";          // Script finished
$SigName[2]  = "MISSINGINFO";     // Missing information
$SigName[3]  = "USERNAMEEXISTS";  // Username exists (register)
$SigName[4]  = "EMAILEXISTS";     // Email exists (register)
$SigName[5]  = "USERADDED";       // User added (register)
$SigName[6]  = "USERPASSWRONG";   // Username does not exist or password is wrong
$SigName[7]  = "LOGGEDIN";        // Login successful (login)
$SigName[8]  = "ALREADYLOGGEDIN"; // Already logged in (login)
$SigName[9]  = "NOTLOGGEDIN";     // Not logged in
$SigName[10] = "LOGGEDOUT";       // Logout successful (logout)
$SigName[11] = "NOMISSION";       // Could not find mission
$SigName[12] = "BADSCORE";        // Score is too high (ratings)
$SigName[13] = "CHATSENT";        // Chat was sent (sendchat)
$SigName[14] = "BADPASSCODE";     // Passcode is incorrect (sendscore)
$SigName[15] = "SCORESENT";       // Score was sent (sendscore)
$SigName[16] = "STARTING";        // Starting list
$SigName[17] = "ALREADYFOUND";    // Already found EE
$SigName[18] = "SENTMESSAGE";     // Sent Message to Server (report)
$SigName[19] = "NOTAUTHORIZED";   // User is not authorized to perform a task
$SigName[20] = "LISTFINISH";      // List finished
$SigName[21] = "CRCGOOD";         // Good CRC
$SigName[22] = "CRCBAD";          // Bad CRC
$SigName[23] = "SERVERID";        // Server id of row in database (challenge)
$SigName[24] = "NOKEY";           // No "key" parameter found
$SigName[25] = "BADKEY";          // "key" parameter has recently been used
$SigName[26] = "GOODKEY";         // "Key" parameter was accepted
$SigName[27] = "BANNED";          // You are banned and cannot connect
$SigName[28] = "NOCATEGORY";      // Total scores category does not exist (totalscores)
$SigName[29] = "LOCKDOWN";        // Server is under lock down
$SigName[30] = "UPDATENEEDED";    // Client update needed

/**
 * If the script should die after a signal
 * @access public
 * @version 0.1
 * @var Array int=>boolean True if the script should terminate after receiving
 * a signal with the given int
 */
$SigDie[0]  = true;
$SigDie[1]  = false;
$SigDie[2]  = true;
$SigDie[3]  = true;
$SigDie[4]  = true;
$SigDie[5]  = false;
$SigDie[6]  = true;
$SigDie[7]  = false;
$SigDie[8]  = true;
$SigDie[9]  = true;
$SigDie[10] = false;
$SigDie[11] = true;
$SigDie[12] = true;
$SigDie[13] = false;
$SigDie[14] = true;
$SigDie[15] = false;
$SigDie[16] = false;
$SigDie[17] = true;
$SigDie[18] = false;
$SigDie[19] = true;
$SigDie[20] = false;
$SigDie[21] = false;
$SigDie[22] = false;
$SigDie[23] = false;
$SigDie[24] = false;
$SigDie[25] = true;
$SigDie[26] = false;
$SigDie[27] = true;
$SigDie[28] = true;
$SigDie[29] = true;
$SigDie[30] = true;

/**
 * Sends a signal to the output in the form "SIG <num> <name>", and dies if
 * specified by $SigDie[]
 * @access public
 * @param int $num The signal number to send
 * @version 0.1
 */
function sig($num = 0, $override = false, $message = "") {
   global $SigName;
   global $SigDie;
   if (isTorque() || array_key_exists("webchat", $_POST)) {
      echo("SIG $num " . $SigName[$num] . ($message == "" ? "" : " $message ") . "\n");
      flush();
   }
   if (!isset($socketserver) && ($SigDie[$num] || $override)) {
      if (isTorque())
         die();
      else {
         if (!array_key_exists("noridir", $_POST))
            die();
         $self = $_SERVER["PHP_SELF"];
         $file = substr($self, strrpos($self, "/") + 1, strlen($self));
         global $sig_redirect;
         if (!empty($sig_redirect))
            $file = $sig_redirect;
         header("Location: $file?e=$num");
         die();
      }
   }
}
?>
