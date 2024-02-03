<?php
defined("SOCKET_SERVER") or die("Invalid Access");

require ("socketserver.spam.php");
require ("socketserver.dedicated.php");
require ("welcome.php");

define("SPAM_THRESHOLD", 1); // Spam index required to be silenced for spamming
define("SPAM_CONSTANT", 30); // Duration of spam time relative to spam index (seconds)
define("DISCONNECT_TIMEOUT", 10); // How long you can be connected without authenticating
define("SPAM_MAX", 86400); //Maximum time you can be muted for

define("FILTER_CAPS", false);
define("ADD_SPAM", false);
define("BLOCK_POLITICS", false);

class ClientConnection extends BaseConnection
{

	/**
	 * Whether the user is on the webchat
	 * 
	 * @var webchat
	 */
	protected $webchat;

	/**
	 * @return bool webchat
	 */
	public function getWebchat() {
		return $this->webchat;
	}

	/**
	 * The client's game session
	 * 
	 * @var session
	 */
	protected $session;

	/**
	 * A saved timestamp of when the user logs in
	 * 
	 * @var logintime
	 */
	protected $logintime;

	/**
	 * The client's "spam index," or how bad they're spamming
	 * 
	 * @var spamIndex
	 */
	protected $spamIndex;

	/**
	 * The ending time (gettimeofday(true)) for the spam-silence
	 * 
	 * @var spamTime
	 */
	protected $spamTime;

	/**
	 * SpamFilter object that can handle all the PQs that it needs to
	 * 
	 * @var spamFilter
	 */
	protected $spamFilter;

	/**
	 * Whether or not the client is muted for spamming
	 * 
	 * @var spamming
	 */
	protected $spamming;

	/**
	 * Whether or not the client is banned
	 * 
	 * @var banned
	 */
	protected $chatBanned;

	/**
	 * Other vars to make PHP shut up
	 */
	protected $restartcheck;
	protected $pbd;
	protected $lastMessage;

	protected $displayName;
	protected $titles;
	protected $color;
	protected $access;
	protected $privilege;


	/**
	 * Gets the user's username
	 * 
	 * @return string
	 */
	function getUsername()
	{
		return $this->username;
	}

	/**
	 * Gets the user's display name
	 * 
	 * @return string
	 */
	function getDisplayName() {
		return $this->displayName;
	}

	/**
	 * Gets the user's titles as an array
	 * 
	 * @return string
	 */
	function getTitles() {
		return $this->titles;
	}

	/**
	 * Gets the user's color
	 *
	 * @return string
	 */
	function getColor() {
		return $this->color;
	}

	/**
	 * Gets the user's access
	 * 
	 * @return int
	 */
	function getAccess() {
		return $this->access;
	}

	/**
	 * Gets if a user is privileged or not (mod/admin)
	 * @note No SJWs allowed
	 * 
	 * @return bool
	 */
	function getPrivilege() {
		return $this->privilege;
	}

	/**
	 * Gets the user's location
	 * 
	 * @return int
	 */
	function getLocation()
	{
		return $this->location;
	}

	/**
	 * Gets which table the user is stored into
	 * 
	 * @return string
	 */
	function getTable()
	{
		if ($this->webchat)
			return "jloggedin";
		else
			return "loggedin";
	}

	public function updateProfile() {
		$user = $this->getUsername();
		$name = getDisplayName($user);
		if (strcasecmp($name, "SERVER") === 0 && strcasecmp($user, "SERVER") !== 0)
			$this->displayName = $user;
		else
			$this->displayName = $name;

		$this->titles = getTitles($user);

		$this->color = getColor($user);

		$this->access = getUserAccess($user);

		if (!$this->loggedin) {
			$this->privilege = - 1;
		} else if ($this->chatBanned) {
			$this->privilege = - 3;
		} else {
			$access = $this->access;
			// Mod/Admin are > 0, guests are 3
			if ($access == 3) {
				$this->privilege = 0;
			} else {
				$this->privilege = $access;
			}
		}
	}

	/**
	 * Whether or not the client can receive messages from broadcast()
	 * 
	 * @return boolean
	 */
	function canReceiveBroadcasts()
	{
		return $this->loggedin;
	}

	/**
	 * Gets if the client is muted for spamming
	 * 
	 * @return boolean
	 */
	function isSpamming()
	{
		return $this->spamming || $this->spamIndex > SPAM_THRESHOLD;
	}

	/**
	 * Gets the clieny's spam index
	 * 
	 * @return float
	 */
	function getSpamIndex()
	{
		return $this->spamIndex;
	}

	/**
	 * Gets the client's session
	 *
	 * @return string
	 */
	function getSession()
	{
		return $this->session;
	}

	/**
	 * Adds to the client's spam index
	 * 
	 * @var int $amount
	 */
	function addSpam($amount = 0)
	{
		echo ("Adding $amount spam for " . $this->getUsername() . "\n");
		$this->spamIndex += $amount;

		if ($this->spamIndex > SPAM_MAX / SPAM_CONSTANT) {
			//Wow. How did you ...
			$this->spamIndex = SPAM_MAX / SPAM_CONSTANT;
		}
		
		if ($this->spamIndex < 0)
			$this->spamIndex = 0;
			
			// If they've passed the limit, kick em off
		if (!$this->spamming && $this->spamIndex > SPAM_THRESHOLD) {
			$this->spamming = true;
			serverChat("You have been muted for spam/offensive chat.", $this);
			serverChat("You can type \"/mute\" to check the time remaining on your mute.", $this);
			
			echo ("Muted " . $this->getUsername() . ", start is " . gettimeofday(true) . "\n");
			
			$this->spamFilter->muteTime += ($this->spamIndex * SPAM_CONSTANT);
			$this->spamFilter->timesMuted ++;
		}
		
		if ($this->isSpamming()) {
			// Update their timeout
			$this->spamTime = gettimeofday(true) + ($this->spamIndex * SPAM_CONSTANT);
			echo ("Updated spamTime for " . $this->getUsername() . ", now is $this->spamTime\n");
		}
		
		echo ("Added spam for " . $this->getUsername() . ", index is now $this->spamIndex\n");

		//Propigate this so we get a relatively accurate number if they login again
		$query = safe_prepare("UPDATE `users` SET `muteIndex` = :idx WHERE `username` = :username");
		$query->bind(":idx", $this->spamIndex);
		$query->bind(":username", $this->getUsername());
		$query->execute();
	}

	/**
	 * Unmute / cancel spam for th e client
	 */
	function cancelSpam() {
		$this->spamIndex = 0;
		$this->spamming = false;
	}

	/**
	 * Sets the user's username
	 * 
	 * @var string $username
	 */
	function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * Sets the user's location
	 * 
	 * @var int $location
	 */
	function setLocation($location)
	{
		$this->location = $location;
		
		// Tell the database
		$query = safe_prepare("UPDATE `" . $this->getTable() . "` SET `location` = :location WHERE `username` = :username");
		$query->bind(":location", $location, PDO::PARAM_STR);
		$query->bind(":username", $this->getUsername());
		$query->execute();
		
		// Notify everyone
		if ($location >= 0)
			$this->notify(true, "setlocation", 0, $location);

		//PQ status
		if ($location == 13) {
			//WHERe flair
			awardTitle($this->getUsername(), 21);
		}
		
		ClientConnection::sendUserlists();
	}

	/**
	 * Sets the user's session
	 * 
	 * @var string $session
	 */
	function setSession($session)
	{
		echo ($this->getUsername() . " Session :: $session\n");
		$this->session = $session;
		
		$query = safe_prepare("UPDATE `" . $this->getTable() . "` SET `loginsess` = :session WHERE `username` = :username");
		$query->bind(":session", $session);
		$query->bind(":username", $this->getUsername());
		$query->execute();
	}

	/**
	 * Resets a client's info
	 */
	function reset()
	{
		$this->setUsername("");
	}

	/**
	 * Called when the user login finishes.
	 * Great for tracking and notifications
	 */
	function onLogin() {
		$query = safe_prepare("SELECT COUNT(*) FROM `{$this->getTable()}` WHERE `username` = :username");
		$query->bind(":username", $this->getUsername());
		$count = $query->execute()->fetchIdx(0);

		if ($count > 3) {
			$this->reset();
			$this->delete();

			return;
		}

		$muteMult = userField($this->getUsername(), "muteMultiplier");

		// Create a spam filter detector
		$this->spamFilter = new SpamFilter($muteMult);

		$time          = getServerTime();
		$this->session = strRand(64);

		$this->loggedin  = true;
		$this->logintime = getServerTime();

		$this->updateProfile();

		if (!$this->relogin) {
			$this->sendSettings();
		}

		// Add us to the logged in table
//		echo ("INSERT INTO `" . $this->getTable() . "` (`username`, `display`, `access`, `location`, `game`, `time`, `logintime`, `loginsess`, `address`) VALUES (:user, :display, :access, :location, :game, :time, :logintime, :session, :address)\n");
		$query = safe_prepare("INSERT INTO `" . $this->getTable() .
		                      "` (`username`, `display`, `access`, `location`, `game`, `time`, `logintime`, `loginsess`, `address`) VALUES (:user, :display, :access, :location, :game, :time, :logintime, :session, :address)");
		$query->bind(":user", $this->getUsername(), PDO::PARAM_STR);
		$query->bind(":display", $this->getDisplayName(), PDO::PARAM_STR);
		$query->bind(":access", $this->getAccess(), PDO::PARAM_INT);
		$query->bind(":location", $this->location, PDO::PARAM_INT);
		$query->bind(":game", $this->game, PDO::PARAM_STR);
		$query->bind(":time", $time, PDO::PARAM_INT);
		$query->bind(":logintime", $time, PDO::PARAM_INT);
		$query->bind(":session", $this->session, PDO::PARAM_STR);
		$query->bind(":address", $this->getAddress(), PDO::PARAM_STR);
		$query->execute();

		if ($this->chatBanned) {
			$this->setCanChat(false);
			serverChat("You have been banned from chatting. You may still play online but are unable to send messages.", $this);
		}

		//Check for TOS accepting
		$accepted = userField($this->getUsername(), "acceptedTos");
		if (!$accepted && !$this->webchat) {
			$this->write("ACCEPTTOS\n");
			$this->setLocation(-2);
			return;
		} else {
			$this->sendLoginData();
		}
	}
	function sendLoginData() {
		if (!isGuest($this->getUsername())) {
			// Give us our event data
			$this->sendEventData();
		}

		// Let us know we're logged in
		$this->write("LOGGED\n");

		if (!$this->relogin) {
			// Send us the previous chats
			$this->sendPreviousMessages();
		}

		// Let everyone else know we're logged in
		if (!$this->chatBanned)
			$this->notify(true, "login", -1, $this->location . " " . $this->game . " " . $this->getAccess());

		// Update userlists
		ClientConnection::sendUserlists();
		
		// If we were spamming, make us pay
		$prevSpam = userField($this->getUsername(), "muteIndex");
		if ($prevSpam > 1) {
			$this->addSpam($prevSpam);
			serverChat("Detected mute from a previous session.", $this);
		}
	}

	/**
	 * Called when the user logs out.
	 * Also good for notifications
	 */
	function onLogout()
	{
		if ($this->getUsername() == "")
			return false;
		
		if ($this->loggedin) {
			// Take us out of the loggedin table
			$query = safe_prepare("DELETE FROM `" . $this->getTable() . "` WHERE `username` = :username LIMIT 1");
			$query->bind(":username", $this->getUsername());
			$query->execute();

			// Let everyone else know we've logged out
			if ($this->status == "chat") {

				if (!$this->chatBanned && $this->location >= 0) {
					$this->notify(true, "logout", -1);
				}
			}
		}
		
		// Let everyone else know we've logged out
		if ($this->status == "chat") {
			
			//Don't track login time if they're on webchat.
			if (!$this->webchat) {
				// How long were they logged in?
				$this->trackLogin();
			}
		}
		
		if (isGuest($this->getUsername())) {
			$query = safe_prepare("DELETE FROM `users` WHERE `username` = :username LIMIT 1");
			$query->bind(":username", $this->getUsername());
			$query->execute();
		} else {
			$query = safe_prepare("UPDATE `users` SET `muteIndex` = :idx WHERE `username` = :username");
			$query->bind(":idx", floatval($this->spamIndex));
			$query->bind(":username", $this->getUsername());
			$query->execute();
		}
		
		echo ("Logout: " . $this->getUsername() . "\n");
		
		// Update everyone's playerlist
		return true;
	}

	/**
	 * Checks for chat commands
	 * 
	 * @param string $pingdata            
	 * @return boolean If the command was intercepted
	 */
	function checkCmd(&$data)
	{
		$words = explode(" ", $data);
		
		$user = escapeName($this->getUsername());
		
		if ($words[0] == "/ping") {
			serverChat("Pong!", $this, true, false);
			return true;
		}
		if (strtolower($data) === "no u") {
			serverChat("https://i.imgur.com/lv7HyG9.png", $this, true, false);
			return true;
		}
		// /mute <player> <time>
		if ($words[0] == "/mute") {
			if (count($words) < 2) {
				serverChat("Current mute status: " . ($this->spamming ? "Muted" : "Unmuted"), $this, true, false);
				if ($this->spamming)
					serverChat("Muted for " . ceil($this->spamIndex * SPAM_CONSTANT) . " seconds.", $this, true, false);
				return true;
			} else if ($this->getPrivilege() > 0) {
				$player = decodeName($words[1]);
				if (count($words) < 3) {
					global $clients;
					foreach ($clients as $client) {
						if ($client->getUsername() == $player) {
							serverChat("Mute status for " . $client->getDisplayName() . ": " . ($this->spamming ? "Muted" : "Unmuted"), $this, true, false);
							if ($client->isSpamming())
								serverChat("Muted for " . ceil($client->getSpamIndex() * SPAM_CONSTANT) . " seconds.", $this, true, false);
						}
					}
				} else {
					$time = $words[2];

					if ($player === "all") {
						//Mute everyone

						global $clients;
						foreach ($clients as $client) {
							if ($client->getPrivilege() < $this->getPrivilege()) {
								$client->addSpam($time / SPAM_CONSTANT);
								$client->spamming = true;
							}
						}

						serverChat("Muted everyone for $time seconds. Congratulations.", $this);
						serverChat("[col:1]Everyone has been [b]muted [c]by " . $this->getDisplayName() . ".");

						return true;
					}

					$message = false;
					global $clients;
					foreach ($clients as $client) {
						if ($client->getUsername() == $player) {
							if ($client->getPrivilege() < $this->getPrivilege() || $this->getPrivilege() == 2) {
								$spam = $time / SPAM_CONSTANT;
								$client->addSpam($spam);
								$client->spamming = true;

								serverChat($this->getDisplayName() . " has muted you for spamming.", $client);

								if (!$message) {
									$message = true;
									serverChat("Muted player \"$player\" for $time seconds.", $this);
									serverChat("Added $spam to their spamindex.", $this);

									foreach ($clients as $other) {
										if ($other->getPrivilege() > 0) {
											serverChat($this->getDisplayName() . " has muted $player.", $other);
										}
									}

//									serverChat("[col:1]" . $client->getDisplayName() . " has been [b]" . ($spam > 0 ? "muted" : "unmuted") . " [c]by " . $this->getDisplayName() . ".");
								}
							} else {
								serverChat("Insufficient Permission.", $this);
							}
						}
					}
				}
				return true;
			}
		}
		if ($words[0] == "/qmute") {
			if ($this->getPrivilege() > 0) {
				if (count($words) < 2) {
					serverChat("Usage: /qmute <player>", $this);
					return true;
				}
				$user = decodeName($words[1]);
				//Find how many qmutes they currently have
				$query = safe_prepare("SELECT COUNT(*) FROM `bans` WHERE `username` = :user AND (UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(`end`)) < MAX(:length, (UNIX_TIMESTAMP(`end`) - UNIX_TIMESTAMP(`start`)))");
				$query->bind(":user", $user);
				$query->bind(":length", 86400); //1 day in seconds is min length of saving
				$result = $query->execute();
				$count = $result->fetchIdx(0);

				//Figure out what they deserve
				$query = safe_prepare("SELECT * FROM `qmutelevels` WHERE `qmutes` = :count");
				$query->bind(":count", $count);
				$result = $query->execute();


				//Give them a recorded qmute

//				$query = safe_prepare("INSERT INTO `bans` SET `username` = :user, `sender`")

			}
		}
		if ($words[0] == "/unmute") {
			if ($this->getPrivilege() > 0) {
				if (count($words) < 2) {
					serverChat("Usage: /unmute <player>", $this);
					return true;
				}
				$player = decodeName($words[1]);
				$client = ClientConnection::find($player);
				if ($client == null) {
					serverChat("Could not find player \"$player\"", $this);
					return true;
				} else {
					$client->cancelSpam();
					serverChat("Unmuted player \"$player\".", $this);
					return true;
				}
			}
		}
		if ($words[0] == "/kick") {
			if ($this->getPrivilege() > 0) {
				if (count($words) < 2) {
					serverChat("Usage: /kick <player>", $this);
					return true;
				}
				$name = decodeName($words[1]);

				$client = ClientConnection::find($name);
				if ($client == null) {
					serverChat("Could not find player \"$name\"", $this);
				} else {
					if ($client->getPrivilege() < $this->getPrivilege() || $this->getPrivilege() == 2) {
						postNotify("kick", $client->getUsername(), 1, $this->getUsername() . " ");

						// Tell them
						serverChat("Kicked $name.", $this);
//						serverChat("[col:1]$name has been [b]kicked [c]by " . $this->getDisplayName() . ".");
					} else {
						serverChat("Insufficient Permission.", $this);
					}
				}
				return true;
			}
		}
		if ($words[0] == "/ban") {
			if ($this->getPrivilege() > 0) {
				if (count($words) < 2) {
					serverChat("Usage: /ban <player>", $this);
					return true;
				}
				$name = decodeName($words[1]);
				$message = $words[2];

				$client = ClientConnection::find($name);
				if ($client == null) {
					serverChat("Could not find player \"$name\"", $this);
				} else {
					if ($client->getPrivilege() < $this->getPrivilege() || $this->getPrivilege() == 2) {
						$user = $client->getUsername();
						$name = $client->getDisplayName();
						$client->notify(false, "ban", -1, $this->getUsername() . " $message");
						$client->delete();

						// Tell them
						serverChat("[col:1]$name has been [b]banned [c]by " . $this->getDisplayName() . ".");
						serverChat("Block-banned $name.", $this);

						$query = safe_prepare("UPDATE `users` SET `access` = -3, `banned` = 2, `banreason` = :message WHERE `username` = :toban");
						$query->bind(":message", $message, PDO::PARAM_STR);
						$query->bind(":toban", $user, PDO::PARAM_STR);
						$result = $query->execute();
					} else {
						serverChat("Insufficient Permission.", $this);
					}
				}
				return true;
			}
		}
		if ($words[0] == "/sban" || $words[0] == "/cban") {
			if ($this->getPrivilege() > 0) {
				if (count($words) < 2) {
					serverChat("Usage: /sban <player>", $this);
					return true;
				}
				$name = decodeName($words[1]);
				$message = $words[2];

				$client = ClientConnection::find($name);
				if ($client == null) {
					serverChat("Could not find player \"$name\"", $this);
				} else {
					if ($client->getPrivilege() < $this->getPrivilege() || $this->getPrivilege() == 2) {
						$user = $client->getUsername();
						$name = $client->getDisplayName();
						$client->chatBanned = true;
						$client->updateProfile();
						$client->setCanChat(false);

						//Tell everyone that they logged out
						$client->notify(true, "logout", -1);
						ClientConnection::sendUserlists();

						// Tell them
//						serverChat("[col:1]$name has been [b]banned [c]by " . $this->getUsername() . ".");
						serverChat("You have been banned from chatting. You may still play online but are unable to send messages.", $client);
						serverChat("Chat-banned $name.", $this);

						global $clients;
						foreach ($clients as $other) {
							if ($other->getPrivilege() > 0) {
								serverChat($this->getDisplayName() . " has chat banned $name.", $other);
							}
						}

						$query = safe_prepare("UPDATE `users` SET `access` = -3, `banned` = 1, `banreason` = :message WHERE `username` = :toban");
						$query->bind(":message", $message, PDO::PARAM_STR);
						$query->bind(":toban", $user, PDO::PARAM_STR);
						$result = $query->execute();
					} else {
						serverChat("Insufficient Permission.", $this);
					}
				}
				return true;
			}
		}
		if ($words[0] == "/ipban") {
			if ($this->getPrivilege() > 0) {
				if (count($words) < 2) {
					serverChat("Usage: /ipban <player>", $this);
					return true;
				}
				$name = decodeName($words[1]);
				$message = $words[2];

				$client = ClientConnection::find($name);
				if ($client == null) {
					serverChat("Could not find player \"$name\"", $this);
				} else {
					if ($client->getPrivilege() < $this->getPrivilege() || $this->getPrivilege() == 2) {
						$user = $client->getUsername();
						$name = $client->getDisplayName();

						//Run the query first
						$query = safe_prepare("UPDATE `users` SET `access` = -3, `banned` = 3, `banreason` = :message WHERE `username` = :toban");
						$query->bind(":message", $message, PDO::PARAM_STR);
						$query->bind(":toban", $user, PDO::PARAM_STR);
						$query->execute();

						$client->notify(false, "ban", -1, $this->getUsername() . " $message");
						$client->updateBannedIPs($this->getUsername());
						$client->delete();

						// Tell them
						serverChat("[col:1]$name has been [b]banned [c]by " . $this->getDisplayName() . ".");
						serverChat("IP-Banned $name.", $this);
					} else {
						serverChat("Insufficient Permission.", $this);
					}
				}
				return true;
			}
		}
		if ($words[0] == "/send") {
			if ($this->getPrivilege() > 1) {
				if (count($words) > 1) {
					serverChat(substr($data, strlen($words[0]) + 1));
				} else {
					serverChat("Usage: /send <text>", $this);
				}
				return true;
			}
		}
		if ($words[0] == "/invisible" && $this->getPrivilege() > 0) {

		}
		if ($words[0] == "/qotd") {
			//lmao hard coded it
			$canDo = ["frostfire"];
			$canQotd = ($this->getPrivilege() > 0) || in_array(strtolower($this->getUsername()), $canDo);
			if (count($words) == 1 || !$canQotd) {
				serverChat("Current QOTD: ", $this);
				$qotd = explode("\n", getQOTDText($canQotd));
				foreach ($qotd as $line) {
					serverChat($line, $this);
				}
				return true;
			}
			if ($canQotd) {
				if (count($words) <= 2) {
					serverChat("Usage: /qotd <sender> <message>", $this);
					return true;
				}
				$user    = $words[1];
				$message = substr($data, strlen($words[0]) + strlen($words[1]) + 2);

				//HiGuy: Deactivate the old qotd
				$query = safe_prepare("UPDATE `qotd` SET `selected` = 0");
				$query->execute();

				//HiGuy: Add the new qotd
				$query = safe_prepare("INSERT INTO `qotd` (`text`, `username`, `selected`, `submitter`) VALUES (:text, :username, 1, :submitter)");
				$query->bind(":text", $message);
				$query->bind(":username", $user);
				$query->bind(":submitter", $this->getUsername()
				);
				$query->execute();

				ClientConnection::notify(true, "qotdupdate", 0, $message);
				return true;
			}
		}
		if ($words[0] == "/ip") {
			if ($this->getPrivilege() == 0) {
				serverChat("Current IP Address: " . $this->getAddress(), $this);
				return true;
			} else {
				if (count($words) < 2) {
					serverChat("Current IP Address: " . $this->getAddress(), $this);
					return true;
				}
				$name = decodeName($words[1]);

				$client = ClientConnection::find($name);
				if ($client == null) {
					serverChat("Could not find player \"$name\"", $this);
				} else {
					serverChat($client->getDisplayName() . "'s IP Address: " . $client->getAddress(), $this);
				}
				return true;
			}
		}
		if ($words[0] == "/random") {
			if (count($words) < 2) {
				$rand = floatval(rand()) / floatval(getrandmax());
				serverChat("Picked a random number between 0 and 1: $rand", $this);
			} else if (count($words) < 3) {
				$top = (int)$words[1];
				$rand = rand(1, $top);
				serverChat("Picked a random number between 1 and $top: $rand", $this);
			} else {
				$bottom = (int)$words[1];
				$top = (int)$words[2];
				$rand = rand($bottom, $top);
				serverChat("Picked a random number between $bottom and $top: $rand", $this);
			}
			return true;
		}
		if ($words[0] == "/joke") {
			$data = "Server, tell me a joke.";
			return false;
		}
		if ($words[0] == "/8ball") {
			$data = "/me shakes the magic 8-ball.";
			return false;
		}

		// All valid commands
		if ($words[0] == "/whisper")
			return false;
		if ($words[0] == "/me")
			return false;
		if ($words[0] == "/slap")
			return false;
		if ($words[0] == "/msg")
			return false;
		if ($words[0] == "/invisible" && $this->getPrivilege() > 0)
			return false;
		if ($words[0] == "/restart" && $this->getPrivilege() > 0)
			return false;
		if ($words[0] == "/mute")
			return false;
			
			// Invalid command
		if ($words[0][0] == "/") {
			serverChat("Invalid command \"$words[0]\".", $this, true, false);
			return true;
		}
		
		return false;
	}

	function filterChat(&$data, $dest) {
		//Obvious choice
		$data = trim($data);

		//Filter zalgo text
		$data = preg_replace("~(?:[\p{M}]{1})([\p{M}])+?~uis","", $data);

		// first things first. strip formatting
		// first round, standard formatting killed.
		static $array = array("[b]", "[i]", "[bi]", "[c]", "[cc]");
		$filteredMessage = str_ireplace($array, "", $data);

		// second round, now do regex on colored text.
		$filteredMessage = preg_replace("/\\[col:\\w+\\]/", "", $filteredMessage);

		//Strip non-printing chars
		$filteredMessage = trim($filteredMessage);
		$filteredMessage = str_replace("\xE2\x80\x8B", "", $filteredMessage);
		$filteredMessage = str_replace("\xE2\x80\x8C", "", $filteredMessage);
		$filteredMessage = str_replace("\xC2\xAD", "", $filteredMessage);

		//Non-mods always get formatting stripped
		if ($this->getPrivilege() < 1)
			$data = $filteredMessage;

		if (FILTER_CAPS) {
			//Minimum length for the caps filter because apparently some people like spamming !PQ
			if (strlen($filteredMessage) < 5) {
				return;
			}

			// check to see if at least 50% of the message was caps.
			// if it was, replace the entire message with lowercase letters!
			$caps  = preg_match_all("/[A-Z]/", $filteredMessage, $matches);
			$lower = preg_match_all("/[a-z]/", $filteredMessage, $matches);
			if ($caps >= $lower) {
				// lowercase the actual message

				//Replace all letters that have a letter before them (in groups), use a callback function to lowercase
				$data = preg_replace_callback("/((?<=[a-zA-Z])\\w+)/", function ($matches) {
					return strtolower($matches[1]);
				}, $data);
			}
		}
	}

	/**
	 * Interprets a message sent from the client
	 * 
	 * @param string $data            
	 * @param string $dest            
	 */
	function chat($data, $dest)
	{
		// Check for commands
		if ($this->checkCmd($data))
			return;

		if ((strpos($data, "/msg") === 0 || strpos($data, "/whisper") === 0) && $dest === "") {
			//Fucking fubar doesn't know how to only send messages to one person
			$parts = explode(" ", $data);
			if (count($parts) > 1)
				$dest = $parts[1];
		}

		if (strlen($data) >= 256) {
			serverChat("Hey, that message is too long. Please split it up into separate messages.", $this);
			return;
		}

		//HiGuy: Filter it
		$this->filterChat($data, $dest);

		//Be nice or shut up
		foreach (getInstaBanRegexes() as $regex) {
			if (preg_match($regex, $data) === 1) {
				$user = $this->getUsername();
				$name = $this->getDisplayName();
				$this->chatBanned = true;
				$this->updateProfile();
				$this->setCanChat(false);

				//Tell everyone that they logged out
				$this->notify(true, "logout", -1);
				ClientConnection::sendUserlists();

				// Tell them
				serverChat("You have been banned from chatting. You may still play online but are unable to send messages.", $this);

				global $clients;
				foreach ($clients as $other) {
					if ($other->getPrivilege() > 0) {
						serverChat("$name has chat banned themselves by having a heated gaming moment (msg: $data)", $other);
					}
				}

				$query = safe_prepare("UPDATE `users` SET `access` = -3, `banned` = 1, `banreason` = :message WHERE `username` = :toban");
				$query->bind(":message", "", PDO::PARAM_STR);
				$query->bind(":toban", $user, PDO::PARAM_STR);
				$query->execute();

				return;
			}
		}

		//Don't let them send the same message twice
		if ($this->lastMessage == $data) {
			return;
		}

		$this->lastMessage = $data;

		//HiGuy: Don't store empty chats
		if (strlen($data) == 0)
			return;

		// HiGuy: Post the chat
		$this->logchat($data, $dest);

		if (ADD_SPAM) {
			if ($dest === "") {
				// Add some to their spam, so if they send too many messages we can kick them out
				$spamlevel = $this->spamFilter->chatSpam($data);
				$this->addSpam($spamlevel);
			} else {
				$spamlevel = 0.02;
			}

		} else {
			$spamlevel = 0;
		}

		// Guests cannot chat
		if (isGuest($this->getUsername()))
			return;

		// Yeah just discard these altogether
		if ($this->isSpamming())
			return;

		if (BLOCK_POLITICS) {
			$politics = $this->spamFilter->detectPolitics($data);
			$this->politics += $politics;

			if ($this->politics > 2) {
				// Jeff really needs to stop
				// Jeff: screw you.
				if ($this->politicswarn) {
					$this->politicswarn = false;
					$this->politics     = 0;
					$this->addSpam(6);
					serverChat("Politics contained.");

					return;
				} else {
					$this->politicswarn = true;
					$this->politics     = 0;
					serverChat("Warning: Politics detected.");
					serverChat("This means you, " . $this->getDisplayName() . ".");
				}
			}
		}

		if ($this->chatBanned) {
			// Send them a message so they don't appear to be shadowbanned
			
			// Their access would normally be -3, this hides that from them :)
			$access = 0;
			$this->write("CHAT " . escapeName($this->getUsername()) . " " . escapeName($this->getDisplayName()) . " $dest $access $data\n");
			return;
		}

		// Yeah don't say it if it's really *that* bad
		if ($spamlevel < SPAM_THRESHOLD) {
			// Make sure they're sending it to either a real person or everybody
			$client = ClientConnection::find($dest);
			if ($client || $dest == "") {
				$access = $this->getAccess();
				$dest = strtolower($dest);
				$data = urlencode($data);
				// Send to all recipients
				if ($dest == "") {
					// If we're invisible, don't send it to everyone!
					if ($this->location >= 0) {
						global $clients;
						foreach ($clients as $client) {
							$client->write("CHAT " . escapeName($this->getUsername()) . " " . escapeName($this->getDisplayName()) . " $dest $access $data\n");
						}
					} else {
						global $clients;
						// Loop through and only show the mods/admins
						foreach ($clients as $client) {
							if ($client->getPrivilege() > 0) {
								$client->write("CHAT " . escapeName($this->getUsername()) . " " . escapeName($this->getDisplayName()) . " $dest $access $data\n");
							}
						}
					}
				} else {
					global $clients;
					// Loop through and send it to all of our sessions
					foreach ($clients as $client) {
						if ($client->getUsername() == $dest) {
							$client->write("CHAT " . escapeName($this->getUsername()) . " " . escapeName($this->getDisplayName()) . " $dest $access $data\n");
						}
					}
				}
			} else {
				//Sending it to someone who is offline!
				echo($this->getUsername() . " sending a message to offline user: $dest\n");

				$query = safe_prepare("INSERT INTO `savedmessages` SET `sender` = :sender, `recipient` = :recipient, `message` = :message");
				$query->bind(":sender", $this->getUsername());
				$query->bind(":recipient", $dest);
				$query->bind(":message", $data);
				$query->execute();
			}
			
			echo ("Chat: \"$data\"\n");

			$decode = urldecode($data);

			// Special things
			// AAYRL
			if (strpos($decode, "Thunderfury") !== false) {
				$decode = str_replace("Thunderfury", "[Thunderfury, Blessed Blade of the Windseeker]" , $decode);
				serverChat("Did somebody say [Thunderfury, Blessed Blade of the Windseeker]?!");
			}
			if ($decode == "PQ WHERe") {
				serverChat("It's here! https://marbleblast.com/index.php/downloads/pq");
			}
			if ($decode == "PQ HERe") {
				serverChat("Hooray!");
			}
			if (strtolower($decode) == strtolower("HELO SERVER")) {
				serverChat("HELO " . $this->getDisplayName());
			}
			if ($decode == "/me shakes the magic 8-ball.") {
				$randball = array("It is certain",
									"It is decidedly so",
									"Without a doubt",
									"Yes definitely",
									"You may rely on it",
									"As I see it, yes",
									"Most likely",
									"Outlook good",
									"Yes",
									"Signs point to yes",
									"Reply hazy try again",
									"Ask again later",
									"Better not tell you now",
									"Cannot predict now",
									"Concentrate and ask again",
									"Don't count on it",
									"My reply is no",
									"My sources say no",
									"Outlook not so good",
									"Very doubtful"
									);
				$decision = $randball[array_rand($randball)];
				serverChat($decision);
			}
			if ($decode == "Server, tell me a joke.") {
				$query = safe_prepare("SELECT `joke` FROM `jokes` ORDER BY RAND() LIMIT 1");
				$result = $query->execute();
				if ($result->rowCount()) {
					$joke = $result->fetchIdx(0);
					serverChat("Your joke is: " . $joke);
				} else {
					serverChat("Your joke is: The database server. Why did it not return anything?");
				}
			}
			// END AAYRL
			if ($decode == "/me hugs the server") {
				serverChat("/me hugs you back <3");
			}
			if ($decode == "Open the pod bay doors, SERVER!") {
				serverChat("I'm sorry, " . $this->getDisplayName() . ". I'm afraid I can't do that.");
				$this->pbd = 1;
			}
			if ($this->pbd == 1 && $decode == "What's the problem?") {
				serverChat("I think you know what the problem is just as well as I do.");
				$this->pbd ++;
			}
			if ($this->pbd == 2 && $decode == "What are you talking about, SERVER?") {
				serverChat("This mission is too important for me to allow you to jeopardize it.");
				$this->pbd ++;
			}
			if ($this->pbd == 3 && $decode == "I don't know what you're talking about, SERVER.") {
				global $clients;
				$possibleNames = $clients;
				unset($possibleNames[array_search($this, $possibleNames)]);

				if (count($possibleNames)) {
					$client = $possibleNames[array_rand($possibleNames)];
					$name2 = $client->getDisplayName();
				} else {
					$name2 = "Frank";
				}
				serverChat("I know that you and $name2 were planning to disconnect me, and I'm afraid that's something I cannot allow to happen.");
				$this->pbd ++;
			}
			if ($this->pbd == 4 && $decode == "Where the hell did you get that idea, SERVER?") {
				serverChat($this->getDisplayName() . ", although you took very thorough precautions in the pod against my hearing you, I could see your lips move.");
				$this->pbd ++;
			}
			if ($this->pbd == 5 && $decode == "Alright, SERVER. I'll go in through the emergency airlock.") {
				serverChat("Without your space helmet, " . $this->getDisplayName() . "? You're going to find that rather difficult.");
				$this->pbd ++;
			}
			if ($this->pbd == 6 && $decode == "SERVER, I won't argue with you anymore! Open the doors!") {
				serverChat($this->getDisplayName() . ", this conversation can serve no purpose anymore. Goodbye.");
				$this->pbd = 0;
				postNotify("kick", $this->getUsername(), 1, "SERVER <Insert dramatic space sequence here>");
			}
		}
	}

	/**
	 * Logs chat to the database
	 * 
	 * @param string $data            
	 * @param string $dest            
	 */
	function logchat($data, $dest)
	{
		global $chatlogging;
		if (!$chatlogging)
			return;
		
		$time = getServerTime();
		// -1 for spamming so it doesn't show up
		$access = ($this->spamming ? -1 : $this->getAccess());
		$dest = strtolower($dest);
		
		// Insert it into the database because we like knowing what people say
		$query = safe_prepare("INSERT INTO `chat` (`username`, `destination`, `message`, `access`, `time`) VALUES (:username, :dest, :message, :access, :time)");
		$query->bind(":username", $this->getUsername());
		$query->bind(":dest", $dest);
		$query->bind(":message", $data);
		$query->bind(":access", $access);
		$query->bind(":time", $time);
		$query->execute();
	}

	/**
	 * Sends a notification coming from this client
	 * 
	 * @param boolean $global            
	 * @param string $type            
	 * @param int $access            
	 * @param string $message            
	 */
	function notify($global, $type, $access = 0, $message = "")
	{
		if ($global) {
			ClientConnection::notifyAll($this->getUsername(), $type, $access, $message);
		} else if ($this->getStatus() == "chat") {
			$this->write("NOTIFY $type $access " . escapeName($this->getUsername()) . " " . escapeName($this->getDisplayName()) . " $message\n");
		}
	}

	static function notifyAll($username, $type, $access = 0, $message = "") {
		global $clients;
		foreach ($clients as $client) {
			if ($client->getPrivilege() >= $access && $client->getStatus() == "chat") {
				$client->write("NOTIFY $type $access " . escapeName($username) . " " . escapeName(getDisplayName($username)) . " $message\n");
			}
		}
	}

	/**
	 * Sends the current userlist so they can display it
	 */
	function sendUserlist()
	{
		//Not chat, no user list
		if ($this->getStatus() != "chat") {
			return;
		}

		$this->write("USER START\n");
		$groups = [
			"-3" => [
				"groupName" => "Banned Users",
				"singleName" => "Banned",
				"privilege" => 0,
				"ordering" => 0
			],
			"0" => [
				"groupName" => "Users",
				"singleName" => "Member",
				"privilege" => 0,
				"ordering" => 2
			],
			"1" => [
				"groupName" => "Moderators",
				"singleName" => "Moderator",
				"privilege" => 1,
				"ordering" => 3
			],
			"2" => [
				"groupName" => "Administrators",
				"singleName" => "Administrator",
				"privilege" => 2,
				"ordering" => 4
			],
			"3" => [
				"groupName" => "Guests",
				"singleName" => "Guest",
				"privilege" => 0,
				"ordering" => 1
			],
			"4" => [
				"groupName" => "Developers",
				"singleName" => "Developer",
				"privilege" => 1,
				"ordering" => 5
			]
		];
		foreach ($groups as $level => $info) {
			$this->write("USER GROUP {$level} {$info["ordering"]} " . encodeName($info["groupName"]) . " " . encodeName($info["singleName"]) . "\n");
		}

		$seen = array();
		
		global $clients;
		foreach ($clients as $client) {
			if ($client->getUsername() == "")
				continue;
	
			if (!$client->canReceiveBroadcasts())
				continue;

			$access = $client->getAccess();
			$location = $client->getLocation();

			if ($client->getUsername() !== $this->getUsername()) {
				if ($this->getPrivilege() < 1 && $client->getLocation() < 0)
					continue;
					
					// Banned
				if ($this->getPrivilege() < 1 && $client->getPrivilege() < 0)
					continue;
					
				//If they're banned and we can see them, show them as banned
				if ($client->getPrivilege() == -3)  {
					$location = -3;
				}
			} else {
				// If they're banned, show them on their userlist only, and fake their status to be not banned
				if ($this->chatBanned) {
					$access = 0;
				}
			}
			
			$clientName = $client->getUsername();
			if (array_key_exists($clientName, $seen)) {
				$data = $seen[$clientName];
				if ($data[0] == 3 && $location != 3) {
					$seen[$clientName][0] = $location;
				}
			} else {
				$seen[$clientName] = array($location, $access, $client);
			}
		}

		//This generates a list of everyone who we've seen
		foreach ($seen as $username => $data) {
			$location = $data[0];
			$access = $data[1];
			$client = $data[2];

			// Get their user colors
			$color = $client->getColor();
			[$flair, $prefix, $suffix] = $client->getTitles();

			$user = escapeName($client->getUsername());
			$display = escapeName($client->getDisplayName());

			$flair = escapeName($flair);
			$prefix = escapeName($prefix);
			$suffix = escapeName($suffix);

			$this->write("USER INFO $user $access $location $display $color $flair $prefix $suffix\n");
		}

		$this->write("USER DONE\n");
	}

	/**
	 * Sends the current userlist to all the clients!
	 */
	static function sendUserlists()
	{
		global $clients;
		foreach ($clients as $client) {
			if ($client->canReceiveBroadcasts())
				$client->sendUserlist();
		}
	}

	/**
	 * Send the server settings, see info.php
	 */
	function sendSettings()
	{
		$debuglogging = getServerPref("debuglogging");

		if ($debuglogging) {
			if ($debuglogging == 2 || $this->getPrivilege() > 0)
				$this->write("INFO LOGGING\n");
		}
		
		// Basic things like your access
		
		$time = getServerTime();
		
		$this->write("INFO ACCESS " . $this->getAccess() . "\n");
		$this->write("INFO DISPLAY " . $this->getDisplayName() . "\n");
		$this->write("INFO SERVERTIME $time\n");

		// Various other settings and informations
		
		$welcome = getWelcomeMessage($this->getPrivilege() > 0, $this->getAccess() === 3);
		if ($this->webchat) {
			$welcome = getWebchatWelcomeMessage($this->getPrivilege() > 0, $this->getAccess() === 3);
		}

		$default = escapeName(getServerPref("defaultname"));
		// Address
		$ip = $this->getAddress();
		
		$this->write("INFO WELCOME $welcome\n");
		$this->write("INFO DEFAULT $default\n");
		$this->write("INFO ADDRESS $ip\n");
		
		// Chat help
		
		$this->write("INFO HELP INFO " . getServerPref("chathelp") . "\n");
		if ($this->getPrivilege() > 0)
			$this->write("INFO HELP FORMAT " . getServerPref("chathelpformat") . "\n");
		$this->write("INFO HELP CMDLIST " . getServerPref("chathelpcmdlist" . ($this->getPrivilege() > 0 ? "mod" : "")) . "\n");
		$this->write("INFO PRIVILEGE " . $this->getPrivilege() . "\n");
		
		// Friends list
		
		$this->sendFriends();
		$this->sendBlocks();
		
		// Status list
		
		$query = safe_prepare("SELECT * FROM `statuses`");
		$result = $query->execute();
		
		if ($result->rowCount()) {
			while ((list ($status, $display) = $result->fetchIdx()) !== false) {
				$this->write("STATUS $status $display\n");
			}
		}
		
		// Colors
		
		$query = safe_prepare("SELECT * FROM `chatcolors`");
		$result = $query->execute();
		
		if ($result->rowCount()) {
			while ((list ($ident, $color) = $result->fetchIdx()) !== false) {
				$this->write("COLOR $ident $color\n");
			}
		}

		// Flairs

			$query = jPrepare("SELECT `title` FROM `bv2xj_user_titles` WHERE `position` = 0");
		$result = $query->execute();
		while (($flair = $result->fetchIdx(0)) !== false) {
			$this->write("FLAIR $flair\n");
		}
	}

	/**
	 * Sends their friends list
	 */
	function sendFriends()
	{
		$query = safe_prepare("SELECT `username` FROM `users` WHERE `id` IN (SELECT `friendid` FROM `friends` WHERE `username` = :username)");
		$query->bind(":username", $this->getUsername());
		$result = $query->execute();
		$this->write("FRIEND START\n");
		if ($result->rowCount()) {
			while (($friend = $result->fetchIdx(0)) !== false) {
				$friend = escapeName($friend);
				$display = escapeName(getDisplayName($friend));
				$this->write("FRIEND NAME $friend $display\n");
			}
		}
		$this->write("FRIEND DONE\n");
	}

	/**
	 * Sends their block list
	 */
	function sendBlocks()
	{
		$query = safe_prepare("SELECT `block` FROM `blocks` WHERE `username` = :username");
		$query->bind(":username", $this->getUsername());
		$result = $query->execute();
		$this->write("BLOCK START\n");
		if ($result->rowCount()) {
			while (($user = $result->fetchIdx(0)) !== false) {
				$user = escapeName($user);
				$display = escapeName(getDisplayName($user));
				$this->write("BLOCK NAME $user $display\n");
			}
		}
		$this->write("BLOCK DONE\n");
	}

	/**
	 * Sends previous messages (20) to the client from the chat table
	 */
	function sendPreviousMessages()
	{
		$query = safe_prepare("SELECT * FROM (SELECT * FROM `chat` WHERE `location` >= 0 AND `access` >= 0 AND `access` < 3 AND `destination` = '' ORDER BY `id` DESC LIMIT 20) AS Q1 ORDER BY `id` ASC");
		$query->bind(":username", $this->getUsername());
		$result = $query->execute();
		while (($row = $result->fetch()) !== false) {
			$dest = $row["destination"];
			if ($dest != "" && $dest != "null")
				continue;
			if ($dest == "null")
				$dest = "";
			$username = escapeName(getDisplayName(getUsername($row["username"])));
			$display = escapeName(getDisplayName(getUsername($row["username"])));
			$message = urlencode($row["message"]);
			$this->write("CHAT [Old]-SPC-$username [Old]-SPC-$display $dest {$row['access']} $message\n");
		}
		serverChat("Previous 20 Chat Messages", $this, false, false);
		serverChat("----------------------------------", $this, false, false);

		$query = safe_prepare("SELECT * FROM `savedmessages` WHERE `recipient` = :username AND `received` = 0");
		$query->bind(":username", $this->getUsername());
		$result = $query->execute();

		if ($result->rowCount() > 0) {
			serverChat("You have " . $result->rowCount() . " unread messages:", $this, false, false);
			while (($row = $result->fetch()) !== false) {
				$username = escapeName(getDisplayName(getUsername($row["sender"])));
				$display = escapeName(getDisplayName(getUsername($row["sender"])));
				$access = getUserAccess(getUsername($row["sender"]));
				$dest = escapeName($this->getUsername());
	
				$message = urlencode($row["message"]);
				$this->write("CHAT $username $display $dest $access $message\n");
			}
			serverChat("----------------------------------", $this, false, false);
			
			$query = safe_prepare("UPDATE `savedmessages` SET `received` = 1 WHERE `recipient` = :username");
			$query->bind(":username", $this->getUsername());
			$query->execute();
		}
	}

	/**
	 * Sends a PING message to the client
	 */
	function ping()
	{
		// Send them PING and some random data
		$this->pingdata = strRand(32);
		$this->pingstart = gettimeofday(true);
		$this->ping = 0;
		$this->pingtime = $this->pingstart;
		$this->pinging = true;
		$this->write("PING " . $this->pingdata . "\n");
		
		// Update their position on the database
		$query = safe_prepare("UPDATE `" . $this->getTable() . "` SET `time` = :time WHERE `username` = :username");
		$query->bind(":time", getServerTime(), PDO::PARAM_STR);
		$query->bind(":username", $this->getUsername());
		$query->execute();
		
		$query = safe_prepare("SELECT COUNT(*) FROM `" . $this->getTable() . "` WHERE `username` = :username");
		$query->bind(":username", $this->getUsername());
		// Not on the list
		if ($query->execute()->fetchIdx(0) == 0) {
			// Add us to the logged in table
			$time = getServerTime();
			$query = safe_prepare("INSERT INTO `" . $this->getTable() . "` (`username`, `display`, `access`, `location`, `game`, `time`, `logintime`, `loginsess`, `address`) VALUES (:user, :display, :access, :location, :game, :time, :logintime, :session, :address)");
			$query->bind(":user", $this->getUsername(), PDO::PARAM_STR);
			$query->bind(":display", $this->getDisplayName(), PDO::PARAM_STR);
			$query->bind(":access", $this->getAccess(), PDO::PARAM_INT);
			$query->bind(":location", $this->location, PDO::PARAM_INT);
			$query->bind(":game", $this->game, PDO::PARAM_STR);
			$query->bind(":time", $time, PDO::PARAM_INT);
			$query->bind(":logintime", $time, PDO::PARAM_INT);
			$query->bind(":session", $this->session, PDO::PARAM_STR);
			$query->bind(":address", $this->getAddress(), PDO::PARAM_STR);
			$query->execute();
		}
	}

	/**
	 * Called on tick (they're not reliable, so don't count on it)
	 */
	function tick()
	{
		if ($this->loggedin) {
			if ($this->getStatus() == "chat") {
				$time = gettimeofday(true);

				// Check for if we need to ping them
				if ($time - $this->pingtime > PING_UPDATE_TIME) {
					$this->updatePing();
					// Give em a PING
					if ($this->pinging) // They disconnected
						$this->delete();
					else
						$this->ping();

					//Propigate this so we get a relatively accurate number if they login again
					$query = safe_prepare("UPDATE `users` SET `muteIndex` = :idx WHERE `username` = :username");
					$query->bind(":idx", $this->spamIndex);
					$query->bind(":username", $this->getUsername());
					$query->execute();
				}

				if ($this->isSpamming()) {
					// Check for spam timeout

					if ($time > $this->spamTime) {
						// Ok you're off the hook
						$this->spamming = false;

						// Let them know
						serverChat("You have been unmuted.", $this);
					} else {
						// Lower their spamidx by the correct rate
						// Suddenly I know how to algebra
						// $this->spamTime = $time + ($this->spamIndex * SPAM_CONSTANT);
						// $this->spamTime - $time = ($this->spamIndex * SPAM_CONSTANT
						$this->spamIndex = ($this->spamTime - $time) / SPAM_CONSTANT;
					}
				} else {
					if ($this->spamIndex > 0) {
						// Something something I check for this
						if (!$this->lastSpamUpdate)
							$this->lastSpamUpdate = $time;

						// Lower their spamIndex
						$delta = $time - $this->lastSpamUpdate;
						// SpamIndex is determined by (K * time), so it should be decremented by (time / K)
						$this->spamIndex -= $delta / SPAM_CONSTANT;
						$this->lastSpamUpdate = $time;

						// Don't let this become negative
						if ($this->spamIndex < 0)
							$this->spamIndex = 0;
					}
				}
			}
		} else {
			$time = gettimeofday(true);
			$dctime = $this->connectTime + DISCONNECT_TIMEOUT;

			if ($time > $dctime) {
				echo("No response from " . $this->getAddress() . ". Disconnecting.\n");
				$this->delete();
				return;
			}
		}
	}

	/**
	 * Parses the raw input lines from the client
	 * 
	 * @param string $data            
	 * @return boolean
	 */
	function parse($data)
	{
		// Check if you're banned
		$this->checkIPBan();

		if ($data == "HEYO") {
			$this->loggedin = true;
			$this->setStatus("listen");
			$this->write("HEYA\n");
			return;
		}

		// Identify yourself!
		if ($this->status == "identify") {
			
			// If we can identify, then we've eaten the data
			if (!$this->identify($data)) {
				$this->write("IDENTIFY CHALLENGE\n");
			}

			if (preg_match('/^RELOGIN\z$/', $data, $matches)) {
				$this->relogin = true;
			}
			
			// If you haven't identified, we're just going to eat your chat and tell noone, kinda like shadowbanning
			// Chat should be sent as CHAT <dest> <chat ...>
			if (preg_match('/^CHAT (\S*) (.*)\z$/', $data, $matches)) {
				$dest = decodeName($matches[1]);
				$chat = $matches[2];
				
				if ($this->status == "chat") {
					// Send the chat
					$this->logchat($chat, $dest);
					return true;
				}
			}
			
			// No chatting if you haven't identified yourself!
			return;
		}
		
		// They should send us a password
		if ($this->status == "verify") {
			
			// Can we interpret their password?
			if ($this->verify($data)) {
				// Let them know they succeeded.
				$this->write("IDENTIFY SUCCESS\n");
				
				// We have logged in
				$this->onLogin();
			}
			
			// No chatting if you haven't verified yourself!
			return;
		}

		//Listen clients don't get to chat
		if ($this->status == "listen") {
			return;
		}
		
		// Let's interpret whatever they said
		if (!$this->interpret($data)) {
			// What?
			$this->write("INVALID\n");
		}
	}

	/**
	 * Interprets sent data for chat or other purposes
	 * 
	 * @param string $data            
	 * @return boolean
	 */
	function interpret($data)
	{
		// Eat blank data, we don't really care
		if ($data == "")
			return true;

		// Chat should be sent as CHAT <dest> <chat ...>
		if (preg_match('/^CHAT (\S*) (.*)\z$/', $data, $matches)) {
			$dest = decodeName($matches[1]);
			$chat = $matches[2];
			
			if ($this->status == "chat") {
				// Send the chat
				$this->chat($chat, $dest);
				return true;
			}
		}
		
		// Location should be sent as LOCATION <location>
		if (preg_match('/^LOCATION ([0-9\-]+)\z$/', $data, $matches)) {
			$location = $matches[1];
			
			if ($this->status == "chat") {
				//Don't allow non-staff to become invisible
				if ($location < 0 && $this->getPrivilege() <= 0)
					return true;

				if ($this->location < 0 && $location >= 0) {
					$this->notify(true, "login", -1, $this->location . " " . $this->game . " " . $this->getAccess());
				} else if ($location < 0 && $this->location >= 0) {
					$this->notify(true, "logout", -1);
				}

				// Easy set the location
				$update = $this->location < 0 || $location < 0;
				$this->setLocation($location);
				if ($update)
					ClientConnection::sendUserlists();
				return true;
			}
		}
		
		// Userlist is easy
		if ($data == "USERLIST") {
			$this->sendUserlist();
			return true;
		}
		
		// PONG request
		if (preg_match('/^PONG (.*)\z$/', $data, $matches)) {
			$pingdata = $matches[1];
			
			// Check the PONG data
			if ($this->pingdata == $pingdata) {
				// They're ok
				$this->pingdata = "";
				$this->pinging = false;
				$this->pingtime = gettimeofday(true);
				$this->ping = $this->pingtime - $this->pingstart;
				$this->pingstart = 0;
				
				$this->write("PINGTIME " . $this->ping . "\n");
				
				return true;
			} else {
				// Invalid ping
			}
		}
		
		// PING request
		if (preg_match('/^PING (.*)\z$/', $data, $matches)) {
			$pingdata = $matches[1];
			
			// They're sending us a PING, send them a PONG immediately
			$this->write("PONG $pingdata\n");
			return true;
		}
		
		if (preg_match('/^SESSION (\S*)\z$/', $data, $matches)) {
			$session = $matches[1];
			$this->setSession($session);
			
			return true;
		}
		
		// Tracking
		if (preg_match('/^TRACK (.*)\z$/', $data, $matches)) {
			$trackinfo = $matches[1];
			$this->track($trackinfo);
			
			return true;
		}
		
		// GUI Tracking
		if (preg_match('/^GUITRACK (.*)\z$/', $data, $matches)) {
			$trackinfo = $matches[1];
			$this->trackGui($trackinfo);
			
			return true;
		}

		// Level up!
		if (preg_match('/^LEVELUP (\S*)\z$/', $data, $matches)) {
			$level = $matches[1];
			$this->notify(true, "levelup", 0, $level);
			
			return true;
		}

		if (preg_match('/^TASKCOMPLETE (\S*)\z$/', $data, $matches)) {
			$task = decodeName($matches[1]);
			$this->notify(true, "taskcomplete", 0, $task);

			return true;
		}
		
		if (preg_match('/^ACHIEVEMENT (\S*)\z$/', $data, $matches)) {
			$ach = decodeName($matches[1]);
			$this->notify(true, "achievement", 0, $ach);

			return true;
		}
		
		if (preg_match('/^MASTERY (\S*)\z$/', $data, $matches)) {
			$data = decodeName($matches[1]);
			$this->notify(true, "mastery", 0, $data);

			return true;
		}
		
		if (preg_match('/^PRESTIGEUP (\S*)\z$/', $data, $matches)) {
			$data = decodeName($matches[1]);
			$this->notify(true, "prestigeup", 0, $data);

			return true;
		}

		if (preg_match('/^FRIEND (.*)\z$/', $data, $matches)) {
			$friend = $matches[1];
			if ($this->addFriend($friend)) {
				$this->write("FRIEND ADDED\n");
				$this->sendFriends();
			} else {
				$this->write("FRIEND FAILED\n");
			}
			
			return true;
		}
		
		if (preg_match('/^FRIENDDEL (.*)\z$/', $data, $matches)) {
			$friend = $matches[1];
			if ($this->deleteFriend($friend)) {
				$this->write("FRIEND DELETED\n");
				$this->sendFriends();
			} else {
				$this->write("FRIEND FAILED\n");
			}
			
			return true;
		}
		
		if (preg_match('/^FRIENDLIST\z$/', $data, $matches)) {
			$this->sendFriends();
			return true;
		}

		if (preg_match('/^BLOCK (.*)\z$/', $data, $matches)) {
			$user = $matches[1];
			if ($this->blockUser($user)) {
				$this->write("BLOCK ADDED\n");
				$this->sendBlocks();
			} else {
				$this->write("BLOCK FAILED\n");
			}
			
			return true;
		}

		if (preg_match('/^UNBLOCK (.*)\z$/', $data, $matches)) {
			$user = $matches[1];
			if ($this->unblockUser($user)) {
				$this->write("BLOCK DELETED\n");
				$this->sendBlocks();
			} else {
				$this->write("BLOCK FAILED\n");
			}
			
			return true;
		}

		if (preg_match('/^ACCEPTTOS\z$/', $data, $matches)) {
			$this->acceptTos();
			return true;
		}

		if (preg_match('/^ICESHARDACHIEVEMENT\z$/', $data, $matches)) {
			$this->iceShardAchievement();
			return true;
		}

		// No clue what they mean
		return false;
	}

	/**
	 * Identifies a client based on the data sent
	 * 
	 * @param string $data            
	 * @return boolean
	 */
	function identify($data)
	{
		// If the line is IDENTIFY <name> then we have a name
		if (preg_match('/^IDENTIFY (.*)\z$/', $data, $matches)) {
			if ($matches[1] == "Guest") {
				// Guest logins, redirect them elsewhere
				return $this->guestLogin();
			}
			
			$username = getUsername($matches[1]);
			// if (ClientConnection::find($username)) {
				// No impersonating people!
			// }
			
			$start = getServerTime() - 60;
			$query = safe_prepare("SELECT * FROM `notify` WHERE `time` > $start AND `type` = 'kick' AND `username` = :username");
			$query->bind(":username", $username);
			$result = $query->execute();
			
			if ($result->rowCount()) {
				$row = $result->fetch();
				// You're kicked right now, no logging in for you :(
				$this->notify(false, "kick", -1, $row["message"]);
				$this->delete();
				return true;
			}
			
			$this->setUsername($username);
			
			$query = safe_prepare("SELECT * FROM `users` WHERE `username` = :username");
			$query->bind(":username", $username, PDO::PARAM_STR);
			$result = $query->execute();
			
			if ($result->rowCount()) {
				// They need to verify themselves
				$this->setStatus("verify");
			} else {
				// If they're a guest, just let them in
				if (isGuest($this->getUsername())) {
					echo ("Auth status: Guest\n");
					return true;
				}
				
				// Maybe they don't have a user yet?
				$user = JFactory::getUser(JUserHelper::getUserId($username));
				if ($user->id != 0 && !$user->guest) {
					checkCreateUser($user);
					
					$this->setStatus("verify");
				} else {
					$this->write("IDENTIFY INVALID\n");
					 $this->delete();
					 return true;
				}
			}
			
			return true;
		}
		return false;
	}

	/**
	 * Verifies that a client has the correct password
	 * 
	 * @param string $data            
	 * @return boolean
	 */
	function verify($data)
	{
		if (preg_match('/^VERIFY (\S+) (.*)\z$/', $data, $matches)) {
			// Well let's find out shall we
			$version = $matches[1];

			if (!$this->checkVersion($version)) {
				$this->notify(false, "kick", -1, "Your client is out of date, please update your game before connecting.");
				$this->write("IDENTIFY OUTOFDATE\n");
				$this->delete();
				return false;
			}

			$password = $matches[2];
			
			echo ("Verifying: " . $this->getUsername() . " :: Garb psw: $password\n");
			
			$password = degarbledeguck($password);
			echo ("Auth from " . $this->getUsername() . "\n");
			
			// Grab their login data from the database
			$query = safe_prepare("SELECT * FROM `users` WHERE `username` = :username");
			$query->bind(":username", $this->getUsername(), PDO::PARAM_STR);
			$result = $query->execute();
			
			// If their account actually exists
			if ($result->rowCount()) {
				$row = $result->fetch();
				
				$test = $this->checkUserPass($password);
				
				if (!$test) {
					// Their password / username is wrong.
					$this->write("IDENTIFY INVALID\n");
					$this->delete();
					return false;
				}

				$this->trackAddress();

				//Check for server offline
				$lockdown = getServerPref("lockdown");
				if ($lockdown && getUserPrivilege($this->getUsername()) < 1) {
					//Server's offline, you should stop
					$this->write("IDENTIFY OFFLINE");
					$this->delete();
					return false;
				}

				//Fucking ugh
				$query = jPrepare("SELECT * FROM bv2xj_users WHERE username = :username");
				$query->bind(":username", $this->getUsername());
				$result = $query->execute();
				$row = $result->fetch();
				if ($row["block"] && $row["activation"] !== "") {
					$this->notify(false, "kick", -1, "Please activate your account first. Check your email for a link with your activation code.");
					$this->write("IDENTIFY NEEDACTIVATION\n");
					$this->delete();
					return false;
				}

				// Make sure they're not banned
				switch (userField($this->getUsername(), "banned")) {
				case 1:
					$this->chatBanned = true;
					$this->setCanChat(false);
					break;
				case 2:
					$reason = encodeName(userField($this->getUsername(), "banreason"));
					$this->write("IDENTIFY BANNED $reason\n");
					$this->delete();
					return false;
				case 3:
					$this->updateBannedIPs();
					$reason = encodeName(userField($this->getUsername(), "banreason"));
					$this->write("IDENTIFY BANNED $reason\n");
					$this->delete();
					return false;
				}

				// Let them chat
				$this->setStatus("chat");
				return true;
			} else {
				// If their account doesn't actually exist, take them off
				echo ("Auth status: 0\n");
				
				$this->write("IDENTIFY INVALID\n");
				 $this->delete();
				 return false;
			}
		} else if (preg_match('/^VERIFY (.*)\z$/', $data, $matches)) {
			//Old version

			$this->notify(false, "kick", -1, "Your client is out of date, please update your game before connecting.");
			$this->write("IDENTIFY OUTOFDATE\n");
			$this->delete();
			return false;
		}

		if (preg_match('/^KEY (.*)\z$/', $data, $matches)) {
			$key = $matches[1];
			
			// Grab their login data from the database
			$query = safe_prepare("SELECT * FROM `users` WHERE `username` = :username");
			$query->bind(":username", $this->getUsername(), PDO::PARAM_STR);
			$result = $query->execute();
			
			// If their account actually exists
			if ($result->rowCount()) {
				$row = $result->fetch();

				$this->trackAddress();

				// Make sure they're not banned
				switch ($row["banned"]) {
				case 1:
					$this->chatBanned = true;
					$this->setCanChat(false);
					break;
				case 2:
					$reason = encodeName(userField($this->getUsername(), "banreason"));
					$this->write("IDENTIFY BANNED $reason\n");
					$this->delete();
					return false;
				case 3:
					$this->updateBannedIPs();
					$reason = encodeName(userField($this->getUsername(), "banreason"));
					$this->write("IDENTIFY BANNED $reason\n");
					$this->delete();
					return false;
				}

				//Check for server offline
				$lockdown = getServerPref("lockdown");
				if ($lockdown && getUserPrivilege($this->getUsername()) < 1) {
					//Server's offline, you should stop
					$this->write("IDENTIFY OFFLINE");
					$this->delete();
					return false;
				}

				// Well then, check their user field for the key
				$query = safe_prepare("SELECT `chatkey` FROM `users` WHERE `username` = :user");
				$query->bind(":user", $this->getUsername());
				$userkey = $query->execute()->fetchIdx(0);
				
				if ($key == $userkey) {
					// They're right!
					echo ("Auth status: 1, Key\n");
					$this->setStatus("chat");
					
					// Webchat
					$this->webchat = true;
					$this->setLocation(3);
					return true;
				} else {
					// Nope!
					echo ("Auth status: 0, Key\n");
					$this->write("IDENTIFY INVALID\n");
					 $this->delete();
					 return false;
				}
			}
		}
		// Not an IDENTIFY line
		return false;
	}

	/**
	 * Checks a password for this user
	 * 
	 * @param string $password            
	 * @return boolean
	 */
	function checkUserPass($password)
	{
		// Grab their login data from the database
		$query = safe_prepare("SELECT * FROM `users` WHERE `username` = :username");
		$query->bind(":username", $this->getUsername(), PDO::PARAM_STR);
		$result = $query->execute();
		
		// If their account actually exists
		if ($result->rowCount()) {
			$row = $result->fetch();
			
			// If they're a guest, just let them in
			if (isGuest($this->getUsername())) {
				echo ("Auth status: Guest\n");
				return true;
			}
			
			// Do joomla authentication checking
			if ($row["joomla"]) {
				$login = getLogin($this->getUsername(), $password);
				
				// Make sure their account is correct
				echo ("Auth status: $login\n");
				if ($login == 7) {
					// Let them chat
					$this->setStatus("chat");
					return true;
				} else if ($login == 19) {
					if (false) {
						// They need to register!
						$this->notify(false, "kick", -1, "Please activate your account before logging into the leaderboards!\n");
						$this->delete();
						echo ("Not activated!\n");
						return false;
					} else {
						//TODO TEMPFIX: This kills the server
						// Let them chat
						$this->setStatus("chat");
						return true;
					}
				} else {
					// What's the error?
					$error = getLoginErr($this->getUsername(), $password);
					echo ("Error is: $error\n");
					return false;
				}
			} else {
				// If they're not through joomla (shame on me, lazy face), then do old authentication
				// Warning: DEPRECATED
				
				$password = salt($password, $row["salt"]);
				$serverPassword = $row["pass"];
				
				if ($password == $serverPassword) {
					echo ("Auth status: 1, deprecated\n");
					return true;
				} else {
					echo ("Auth status: 0, deprecated\n");
					return false;
				}
			}
		} else {
			// If their account doesn't actually exist, take them off
			echo ("Auth status: 0\n");
			return false;
		}
	}

	/**
	 * Login as a guest
	 */
	function guestLogin()
	{
		$this->write("IDENTIFY SUCCESS\n");
		
		$username = "";
		// Generate a random username
		do {
			$username = getServerTime();
			$username = substr(md5($username), 0, 8);
			$username = "Guest_" . $username;
			echo("Trying guest username $username\n");
		} while (getUserLoggedIn($username));
		
		// Let them know who they are
		$this->write("INFO USERNAME $username\n");
		$lower = strToLower($username);
		
		$this->setUsername($lower);
		
		// Add them to the database
		$query = safe_prepare("INSERT INTO `users` (`display`, `username`, `pass`, `salt`, `email`, `showemail`, `secretq`, `secreta`, `rating_mp`, `guest`) VALUES (:display, :username, 'guest', 'guest', '', 0, '', '', -1, 1)");
		$query->bind(":display", $username);
		$query->bind(":username", $lower);
		$result = $query->execute();
		
		$this->setStatus("chat");
		$this->onLogin();
		
		return true;
	}

	function setCanChat($can) {
		$this->chatBanned = !$can;
		$this->write("INFO CANCHAT $can\n");
	}

	/**
	 * Update the user's banned addresses, inserting anything new into bannedips
	 */
	function updateBannedIPs($banner = 'Server') {
		//Find all the ips that they used and put them into bannedips
//		$query = safe_prepare("SELECT `address` FROM `addresses` WHERE `username` = :username");
//		$query->bind(":username", $this->getUsername());
//		$addresses = $query->execute()->fetchAll();

//		echo("Found addresses: \n");
//		foreach ($addresses as $index => $list) {
//			echo($list["address"] . "\n");
//			$query = safe_prepare("INSERT IGNORE INTO `bannedips` SET `address` = :address, `banner` = 'Server'");
//			$query->bind(":address", $list["address"]);
//			$query->execute();
//		}

		$query = safe_prepare("INSERT IGNORE INTO `bannedips` SET `address` = :address, `banner` = :banner");
		$query->bind(":address", $this->getAddress());
		$query->bind(":banner", $banner);
		$query->execute();
	}

	/**
	 * Updates a client's ping on the table
	 */
	function updatePing()
	{
		// HiGuy: If they're pinging, we don't want to log them out!
		$table = $this->getTable();
		$query = safe_prepare("UPDATE `$table` SET `time` = :time, `address` = :address, `display` = :display WHERE `username` = :user");
		$query->bind(":time", getServerTime(), PDO::PARAM_INT);
		$query->bind(":display", getDisplayName($this->getUsername()), PDO::PARAM_STR);
		$query->bind(":user", $this->getUsername(), PDO::PARAM_STR);
		$query->bind(':address', $this->getAddress());
		$query->execute();
		// Update their user as well
		$query = safe_prepare("UPDATE `users` SET `lastaction` = CURRENT_TIMESTAMP WHERE `username` = :user");
		$query->bind(":user", $this->getUsername(), PDO::PARAM_STR);
		$query->execute();
	}

	/**
	 * Checks a client's version
	 * @var int $version
	 * @return boolean
	 */
	function checkVersion($version) {
		$query = safe_prepare("SELECT `version` FROM `versions` ORDER BY `id` DESC LIMIT 1");
		$result = $query->execute();

		$serverversion = $result->fetchIdx(0);
		return ($version >= $serverversion);
	}

	/**
	 * Tracks data about a client
	 * 
	 * @param string $trackinfo            
	 */
	function track($trackinfo)
	{
		// It's a space-separated string
		$data = explode(" ", $trackinfo);
		/*
		 * encodeName(getDesktopResolution()) SPC
		 * encodeName($pref::Video::Resolution) SPC
		 * encodeName($pref::Video::fullScreen) SPC
		 * encodeName($platform) SPC
		 * encodeName($pref::useStencilShadows) SPC
		 * encodeName($pref::Player::defaultFov) SPC
		 * encodeName($ignitionVersion) SPC
		 * encodeName($pref::Video::displayDevice) SPC
		 * encodeName(getFields(getVideoDriverInfo(), 0, 2)) SPC
		 * encodeName($fast)
		 */
		$desktop = decodeName($data[0]);
		$resolution = decodeName($data[1]);
		$fullscreen = decodeName($data[2]);
		$platform = decodeName($data[3]);
		$shadows = decodeName($data[4]);
		$fov = decodeName($data[5]);
		$ignition = decodeName($data[6]);
		$device = decodeName($data[7]);
		$driverInfo = decodeName($data[8]);
		$fast = decodeName($data[9]);
		
		deleteTrackDataType("windowres",  $this->getUsername());
		deleteTrackDataType("screenres",  $this->getUsername());
		deleteTrackDataType("fullscreen", $this->getUsername());
		deleteTrackDataType("shadows",    $this->getUsername());
		deleteTrackDataType("fov",        $this->getUsername());
		deleteTrackDataType("ignition",   $this->getUsername());
		deleteTrackDataType("device",     $this->getUsername());
		deleteTrackDataType("driver",     $this->getUsername());
		deleteTrackDataType("fast",       $this->getUsername());

		trackData("windowres",  $this->getUsername(), $resolution);
		trackData("screenres",  $this->getUsername(), $desktop);
		trackData("fullscreen", $this->getUsername(), $fullscreen);
		trackData("shadows",    $this->getUsername(), $shadows);
		trackData("fov",        $this->getUsername(), $fov);
		trackData("ignition",   $this->getUsername(), $ignition);
		trackData("device",     $this->getUsername(), $device);
		trackData("driver",     $this->getUsername(), $driverInfo);
		trackData("fast",       $this->getUsername(), $fast);
		
		trackData("logins",     $this->getUsername());
		trackData("platform",   $this->getUsername(), $platform);
		
		$this->tracked = true;
	}

	/**
	 * Tracks the user's GUI opening
	 * @var string $trackinfo
	 */
	function trackGui($trackinfo) {
		$data = explode(" ", $trackinfo);
//		%this.send("GUITRACK" SPC encodeName($LBPref::Gui[%i]) SPC encodeName($LBPref::GuiCount[%i]));
		$gui = $data[0];
		$count = $data[1];

		$query = safe_prepare("SELECT `count` FROM `guitracking` WHERE `username` = :username AND `gui` = :gui");
		$query->bind(":username", $this->getUsername());
		$query->bind(":gui", $gui);
		$result = $query->execute();

		if ($result->rowCount() > 0) {
			$query = safe_prepare("UPDATE `guitracking` SET `count` = `count` + :count, `lastopen` = CURRENT_TIMESTAMP WHERE `username` = :username AND `gui` = :gui");
			$query->bind(":username", $this->getUsername());
			$query->bind(":gui", $gui);
			$query->bind(":count", $count);
			$query->execute();
		} else {
			$query = safe_prepare("INSERT INTO `guitracking` SET `username` = :username, `gui` = :gui, `count` = :count, `lastopen` = CURRENT_TIMESTAMP");
			$query->bind(":username", $this->getUsername());
			$query->bind(":gui", $gui);
			$query->bind(":count", $count);
			$query->execute();
		}
	}

	/**
	 * Tracks the user's login and login time
	 */
	function trackLogin()
	{
		if ($this->logintime == 0) {
			return;
		}
		$logintime = $this->logintime;
		$total = getServerTime() - $logintime;

		if ($total > 1000000) {
			return;
		}
		
		trackData("logintime", $this->getUsername(), $total, true);
	}

	/**
	 * Tracks the user's IP address
	 */
	function trackAddress()
	{
		$ip = $this->getAddress();
		$username = $this->getUsername();

		//Just update their user
		$query = pdo_prepare("INSERT INTO `addresses` (`username`, `address`, `hits`, `firstHit`) VALUES (:user, :ip, :hits, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE `hits` = `hits` + 1");
		$query->bind(":user", $username, PDO::PARAM_STR);
		$query->bind(":ip", $ip, PDO::PARAM_STR);
		$query->bind(":hits", 1, PDO::PARAM_INT);
		$query->execute();
	}

	/**
	 * Marks the client as having accepted the terms of service
	 */
	function acceptTos() {
		$query = safe_prepare("UPDATE `users` SET `acceptedTos` = 1 WHERE `username` = :username");
		$query->bind(":username", $this->getUsername());
		$query->execute();

		if (!isGuest($this->getUsername()))
			serverChat("Welcome, " . $this->getDisplayName() . ", to the Leaderboards!", null, false, false);

		$this->sendLoginData();
	}

	/**
	 * Adds someone to their friends list
	 * 
	 * @param string $friend            
	 */
	function addFriend($friend)
	{
		$query = safe_prepare("SELECT `id` FROM `users` WHERE `username` = :friend");
		$query->bind(":friend", $friend, PDO::PARAM_STR);
		$result = $query->execute();
		$userid = 0;
		if ($result->rowCount())
			$userid = $result->fetchIdx(0);
		else
			return false;
			
			// HiGuy: Don't ask. Inserts the row into the table if it doesn't already exist
		$query = safe_prepare("INSERT INTO `friends` (`username`, `friendid`) (SELECT * FROM (SELECT :user, :userid) AS tmp WHERE NOT EXISTS (SELECT * FROM `friends` WHERE `username` = :user AND `friendid` = :userid) LIMIT 1)");
		$query->bind(":user", $this->getUsername());
		$query->bind(":userid", $userid);
		$query->execute();
		
		return true;
	}

	/**
	 * Deletes someone from their friends list
	 * 
	 * @param string $friend            
	 */
	function deleteFriend($friend)
	{
		$query = safe_prepare("SELECT `id` FROM `users` WHERE `username` = :friend");
		$query->bind(":friend", $friend);
		$result = $query->execute();
		$userid = 0;
		if ($result->rowCount() && (list ($userid) = $result->fetchIdx()));
		else
			return false; // User wrong
		
		$query = safe_prepare("DELETE FROM `friends` WHERE `username` = :username AND `friendid` = :userid");
		$query->bind(":username", $this->getUsername());
		$query->bind(":userid", $userid);
		$query->execute();
		
		return true;
	}

	/**
	 * Adds someone to their block list
	 * 
	 * @param string $user            
	 */
	function blockUser($user)
	{
		// HiGuy: Don't ask. Inserts the row into the table if it doesn't already exist
		$query = safe_prepare("INSERT INTO `block` (`username`, `block`) (SELECT * FROM (SELECT :user, :block) AS tmp WHERE NOT EXISTS (SELECT * FROM `blocks` WHERE `username` = :user AND `block` = :block) LIMIT 1)");
		$query->bind(":user", $this->getUsername());
		$query->bind(":block", $user);
		$query->execute();
		
		return true;
	}

	/**
	 * Deletes someone from their block list
	 * 
	 * @param string $user            
	 */
	function unblockUser($user)
	{
		$query = safe_prepare("DELETE FROM `blocks` WHERE `username` = :username AND `block` = :user");
		$query->bind(":username", $this->getUsername());
		$query->bind(":user", $user);
		$query->execute();
		
		return true;
	}

	/**
	 * Sends the client a list of all the current events
	 */
	function sendEventData() {
		if (getServerPref("wintermode")) {
			$this->write("WINTER\n");
		}
		if (getServerPref("spookyevent")) {
			$this->write("2SPOOKY\n");
		}
	}
}

function eatNotifications() {
	global $lastNotify, $mysql_data;
	if (!isset($lastNotify)) {
		$query = safe_prepare("SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = :schema AND `TABLE_NAME` = 'notify' LIMIT 1");
		$query->bind(":schema", $mysql_data);
		$lastNotify = $query->execute()->fetchIdx(0);
	}

	$query = safe_prepare("SELECT * FROM `notify` WHERE `id` >= :lastNotify");
	$query->bind(":lastNotify", $lastNotify);
	$result = $query->execute();
	if ($result->rowCount()) {
		while (($row = $result->fetch()) !== false) {
			handleNotification($row["username"], $row["type"], $row["access"], $row["message"]);
		}
	}

	$query = safe_prepare("SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = :schema AND `TABLE_NAME` = 'notify' LIMIT 1");
	$query->bind(":schema", $mysql_data);
	$lastNotify = $query->execute()->fetchIdx(0);
}

function handleNotification($username, $type, $access, $message) {
	ClientConnection::notifyAll($username, $type, $access, $message);

	switch ($type) {
		case "ban":
			$words = explode(" ", $message);
			$kicker = getDisplayName(decodeName($words[0]));

			$message = implode(" ", array_slice($words, 1));

			$client = ClientConnection::find($username);
			if ($client != null) {
				$client->notify(false, "kick", -1, $message);
				$client->delete();

				serverChat("[col:1]" . $client->getDisplayName() . " has been [b]banned [c]by $kicker.");
			}
			break;
		case "kick":
			$words = explode(" ", $message);
			$kicker = getDisplayName(decodeName($words[0]));

			$message = implode(" ", array_slice($words, 1));

			$client = ClientConnection::find($username);
			if ($client != null) {
				$client->notify(false, "kick", -1, $message);
				$client->delete();
			}
			break;
		case "update":
			//Find out what the latest update is
			$query = safe_prepare("SELECT * FROM versions ORDER BY id DESC LIMIT 1");
			$result = $query->execute();
			$row = $result->fetch();

			serverChat("[col:2]New Update Available: " . $row["title"]);

			//Kick everyone ingame
			global $clients;
			foreach ($clients as $client) {
				if (!$client->getWebchat()) {
					//Ruin their day
					$client->notify(false, "kick", -1, "New Update Available: " . $row["title"]);
					$client->delete();
				}
			}
			break;

		case "serverchat":
			serverChat($message);
			break;

	}
}

/**
 * Handles lines from stdin (see socketserver.php)
 * 
 * @param string $data            
 */
function handleStdin($data)
{
	$inst = explode(" ", strToLower($data));
	$first = array_shift($inst);
	$rest = implode(" ", $inst);
	if ($first === "kick") {
		echo ("Kicking {$rest}\n");
		$name = $rest;
		while (($client = ClientConnection::find($name)) !== null) {
			$client->notify(false, "kick", -1);
			$client->delete();
			
			// Tell them
			// serverChat("[col:1]$name has been [b]kicked [c]by a server administrator.");
		}
	}
	if ($first === "kickip") {
		echo ("Kicking {$rest}\n");
		$name = $rest;
		while (($client = ClientConnection::findIP($name)) !== null) {
			$client->notify(false, "kick", -1);
			$client->delete();
			
			// Tell them
			// serverChat("[col:1]$name has been [b]kicked [c]by a server administrator.");
		}
	}
	if ($first == "userlist") {
		echo ("User list:\n");
		global $clients;
		foreach ($clients as $client) {
			echo ($client->getDisplayName() . " / " . $client->getAddress() . " (" . $client->getUsername() . ", access " . $client->getAccess() . ", location " . $client->getLocation() . "), staus is " . $client->getStatus() . " mute index is " . $client->getSpamIndex() . "\n");
		}
		echo ("\n");
	}
	
	if ($first == "send") {
		// Send a message to everyone
		serverChat($rest);
	}
	
	if ($first == "sendas") {
		// Send a message as someone to everyone
		$user = array_shift($inst);
		$message = implode(" ", $inst);
		$access = getUserAccess($user);
		$display = getDisplayName($user);
		
		broadcast("CHAT " . escapeName($user) . " " . escapeName($display) . "  $access $message\n");
	}

	if ($first == "count") {
		global $connections;
		echo("Total lifetime connections: $connections\n");
	}
	
	if ($first == "restart" || $first == "shutdown") {
		// Shut her down!
		restart();
	}
}

/**
 * Sends a chat from the server
 * 
 * @param string $message
 * @param ClientConnection $player
 * @param boolean $privmsg            
 * @param boolean $record            
 */
function serverChat($message, $player = null, $privmsg = true, $record = true)
{
	// Send either a broadcast or a privmsg
	$encoded = urlencode($message);
	if ($player == null) {
		broadcast("CHAT SERVER SERVER  1 $encoded\n");
	} else {
		// Resolve names
		if (gettype($player) == "string")
			$player = ClientConnection::find($player);
			
			// Get username
		$name = escapeName($player->getUsername());
		
		if ($privmsg) {
			$player->write("CHAT SERVER SERVER $name 1 /whisper $name $encoded\n");
			
			$message = "/whisper $name $encoded";
		} else {
			$player->write("CHAT SERVER SERVER  1 $encoded\n");
		}
	}
	
	global $chatlogging;
	
	// Add it to the DB
	$time = getServerTime();
	$access = 1;
	
	if ($chatlogging && $record) {
		// Insert it into the database because we like knowing what people say
		$query = safe_prepare("INSERT INTO `chat` (`username`, `destination`, `message`, `access`, `time`) VALUES (:username, :destination, :message, :access, :time)");
		$query->bind(":username", "SERVER");
		$query->bind(":destination", ($player ? $player->getUsername() : ""));
		$query->bind(":message", $message);
		$query->bind(":access", $access);
		$query->bind(":time", $time);
		$query->execute();
	}
}
