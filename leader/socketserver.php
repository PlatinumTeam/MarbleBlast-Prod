<?php
/**
 * Ok so I made this super easy to start in case any of you fools try to start it again:
 * Just get a root shell and do this:
 *
 * service lbserver start
 *
 * Look how easy that was!
 */

chdir(dirname($_SERVER["PHP_SELF"]));

define("SOCKET_SERVER", 1);

set_time_limit(0);

file_put_contents("/var/run/marbleblast/lbserver.pid", posix_getpid());

//A la http://www.sanwebe.com/2013/05/chat-using-websocket-php-socket

//---------------------------------------------------------------------------
// Super sockety section
//---------------------------------------------------------------------------

//Ignore all of opendb's checking
$socketserver = true;

//Connect to the database YAY
require("opendb.php");
require("jsupport.php");
require("socketserver.extended.php");

//Host variables
$host = "localhost";
$port = "28002";
$chatlogging = false;

//Counter
$connections = 0;

if ($argc > 1) {
	$port = $argv[1];
}

if ($port == "28002") {
	//Clear the users
	safe_prepare("TRUNCATE TABLE `jloggedin`")->execute();
	safe_prepare("TRUNCATE TABLE `loggedin`")->execute();
	safe_prepare("DELETE FROM `users` WHERE `guest` = 1")->execute();
	$chatlogging = true;
	$dev = 0;
} else {
	$dev = 1;
}


define("MESSAGE_LENGTH", 4096);
define("CHALLENGE_UPDATE_TIME", 3);
define("PING_UPDATE_TIME", 60);
define("KICK_UPDATE_TIME", 1);
define("NOTIFY_UPDATE_TIME", 0.5);
define("DISCONNECT_TIMEOUT", 10); // How long you can be connected without authenticating

$kickchecktime = gettimeofday(true);
$notifychecktime = gettimeofday(true);

$stdin = "";

//Create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

//Give it a reusable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//Bind the socket to the port
socket_bind($socket, 0, $port);

//Listen on the port
socket_listen($socket);

error_reporting(E_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

//Make sure to close it when we shutdown
register_shutdown_function('on_shutdown');
set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
	file_put_contents("/tmp/socketserver-error.txt", print_r(func_get_args(), true));
});
set_exception_handler(function ($exception) {
	file_put_contents("/tmp/socketserver-exception.txt", print_r(func_get_args(), true));
});

//Here's the list of everyone who is connected to us
//Add $socket to this list
$sockets = array($socket);
$clients = array();

echo("Started up the server!\n");

//Always poll all the things
while (true) {
	//Create a copy of $sockets so it doesn't get changed by socket_select
	$read = $sockets;
	$write = null;
	$except = null;

	//Tick all the clients
	foreach ($clients as $client) {
		try {
			$client->tick();
		} catch (Exception $e) {
			echo("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
			$client->delete();
		}
	}

	$input = "";
	if (non_block_read(STDIN, $input)) {
		if ($input == "\n") {
			echo("STDIN: $stdin\n");
			handleStdin($stdin);
			$stdin = "";
		} else {
			$stdin .= $input;
		}
	}

//	if (gettimeofday(true) - KICK_UPDATE_TIME > $kickchecktime) {
//		$kickchecktime = gettimeofday(true);
//		checkKicks();
//	}

	if (gettimeofday(true) - NOTIFY_UPDATE_TIME > $notifychecktime) {
		$notifychecktime = gettimeofday(true);
		eatNotifications();
	}

	//Load all the sockety things
	if (@socket_select($read, $write, $except, 0, 1000) < 1)
		continue;

	//Make sure the SQL server is alive
	checkServer();

	//Did we get a new person?
	if (in_array($socket, $read)) {
		$connections ++;

		//Accept them and add them to the party :D
		$newSocket = socket_accept($socket);
		$sockets[] = $newSocket;

		$client = new ClientConnection($newSocket);
		$clients[] = $client;

		$client->setStatus("handshake");

		echo("Connection from " . $client->getAddress() . "\n");
		echo("Total lifetime connections: $connections\n");

		//Yeah I should have implemented this a while ago
		$client->checkIPBan();

		//Remove the listening socket as we no longer need it
		$key = array_search($socket, $read);
		unset($read[$key]);
	}

	//Make sure the SQL server is alive
	checkServer();

	//Go through all the sockets and update them
	foreach ($read as $sock) {
		$client = ClientConnection::findSock($sock);
		if (!$client) {
			continue;
		}
		try {
			$client->check();
		} catch (Exception $e) {
			echo("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
			$client->delete();
		}
	}
}

//---------------------------------------------------------------------------
// Client class
//---------------------------------------------------------------------------

/**
 * A wrapper class based around a socket for a client. Contains lots of fields
 * and all the means of communications for clients.
 */
class BaseConnection {
	/**
	 * The socket for this client
	 * @var socket
	 */
	protected $socket;

	/**
	 * Whether the socket masks the input/output
	 * @var mask
	 */
	protected $mask;

	/**
	 * How many lines of data has the connection read?
	 * @var linesread
	 */
	protected $linesread;

	/**
	 * The last time the client received a challenge/superchallenge update
	 * @var lastupdate
	 */
	protected $lastupdate;

	/**
	 * The client's current status
	 * @var status
	 */
	protected $status;

	/**
	 * The client's username
	 * @var username
	 */
	protected $username;

	/**
	 * The location (e.g. Level Select) of the client
	 * @var location
	 */
	protected $location;

	/**
	 * Which game the client has logged in from
	 * @var game
	 */
	protected $game;

	/**
	 * Whether the user is logged in (completed validation)
	 * @var loggedin
	 */
	protected $loggedin;

	/**
	 * The data sent for ping, that should be returned by the client
	 * @var pingdata
	 */
	protected $pingdata;

	/**
	 * When their ping request started
	 * @var pingstart
	 */
	protected $pingstart;

	/**
	 * When their last ping was
	 * @var pingtime
	 */
	protected $pingtime;

	/**
	 * What their ping is
	 * @var ping
	 */
	protected $ping;

	/**
	 * Is the client currently pinging
	 * @var pinging
	 */
	protected $pinging;

	/**
	 * When the client connected
	 * @var connectTime
	 */
	protected $connectTime;

	/**
	 * The client's IP address if they're using forwarding
	 * @var string forwardedAddress
	 */
	protected $forwardedAddress;

	protected $buffer;
	protected $handshakedata;

	/**
	 * Construct the connection from a socket
	 * @param socket $socket
	 */
	function __construct($socket) {
		$this->socket = $socket;
		$this->mask = false;
		$this->linesread = 0;
		$this->status = "connecting";
		$this->username = "";
		$this->location = 0;
		$this->lastupdate = 0;
		$this->loggedin = false;
		$this->pinging = false;
		$this->ping = 0;
		$this->pingtime = 0;
		$this->pingstart = 0;
		$this->connectTime = gettimeofday(true);
		$this->forwardedAddress = null;
		$this->buffer = "";
		$this->handshakedata = "";

		echo("Created a Connection\n");
	}

	/**
	 * Deletes the connection and obliterates the socket
	 */
	function delete() {
		echo("Deleted a Connection\n");

		global $clients, $sockets;

		//Notification of logout
		$userlist = $this->onLogout();

		echo("Disconnect: " . $this->getAddress() . "\n");

		//Close the socket
		if (is_resource($this->socket))
			socket_close($this->socket);
		else
			echo("Not a resource!\n");

		//Remove the connection from everywhere
		$socketIdx = array_search($this->socket, $sockets);
		$clientIdx = array_search($this, $clients);

		if ($socketIdx !== FALSE) {
			unset($sockets[$socketIdx]);
		} else {
			echo("Unset unset socket!\n");
		}

		if ($clientIdx !== FALSE) {
			unset($clients[$clientIdx]);
		} else {
			echo("Unset unset client!\n");
		}

		echo("Unset socket index: {$socketIdx} client idx: {$clientIdx}\n");

		//Finally, erase the ClientConnection
		//unset($this);

		//Update everyone's playerlist if we're actually logging someone out
		if ($userlist)
			ClientConnection::sendUserlists();
	}

	/**
	 * Compares if the client's socket is another socket
	 * @var socket $socket
	 * @return boolean
	 */
	function compare($socket) {
		return $this->socket === $socket;
	}

	/**
	 * Gets the connection's IP address
	 * @return string
	 */
	function getAddress() {
		if ($this->forwardedAddress === null) {
			//Get the connecting socket's IP
			socket_getpeername($this->socket, $ip);

			return $ip;
		}
		return $this->forwardedAddress;
	}

	/**
	 * Gets the user's current status
	 * @return string
	 */
	function getStatus() {
		return $this->status;
	}

	/**
	 * Sets the user's current status
	 * @var string $status
	 */
	function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Whether or not the client can receive messages from broadcast()
	 * @return boolean
	 */
	function canReceiveBroadcasts() {
		return true;
	}

	/**
	 * Reads data from the socket
	 * @return string
	 */
	function read() {
		$data = @socket_read($this->socket, MESSAGE_LENGTH);

		if (!$data) {
			echo("Data is false\n");
			return false;
		}

		//Unmask the data if needed
		if ($this->mask)
			$data = $this->unmask($data);

		return $data;
	}

	/**
	 * Reads the socket until all data has been read
	 * @return string
	 */
	function readall() {
		$data = "";

		//Keep reading until the end
		while (@socket_recv($this->socket, $buffer, MESSAGE_LENGTH, 0) >= 1) {
			//Unmask the data if needed
			if ($this->mask)
				$buffer = $this->unmask($buffer);

			//Append the data
			$data .= $buffer;
		}

		return $data;
	}

	/**
	 * Writes data to the socket
	 * @param string $data
	 */
	function write($data) {
		//Mask the data if needed
		if ($this->mask)
			$data = $this->mask($data);

		@socket_write($this->socket, $data, strlen($data));
	}

	/**
	 * Called on tick (they're not reliable, so don't count on it)
	 */
	function tick() {
		throw(new Exception());
	}

	/**
	 * The main "check for data" function
	 */
	function check() {
		//Check for input data
		$data = $this->read();

		//Check for disconnect
		if ($data === false) {
			//No point in trying to do anything fancy if they disconnect
			$this->delete();
			return;
		}

		//Similar to below, if we had any extra data from the last read (we didn't read everything), then
		// prepend it onto the current data so we don't lose anything.
		if ($this->buffer !== false) {
			$data = $this->buffer . $data;
			$this->buffer = false;
		}

		//Split it up into newlines
		$lines = explode("\n", str_replace("\r", "", $data));

		//So sometimes Chrome sends out more data in one packet than $this->read() can take. Because of this,
		// the websocket-key was being read with a newline in it. This saves any extra data at the end and
		// stores it into a buffer so we can append it when we read next.
		$last = $data[strlen($data) - 1];
		if ($last != "\n") {
			$this->buffer = $lines[count($lines) - 1];
//			echo("Read extra data, not EOL. Buffer: {$this->buffer}\n");
			array_pop($lines);
		}

		foreach ($lines as $line) {
			//Echo each line so we know what's going on
//			if ($line != "" && substr($line, 0, 4) != "PONG") //Ignore blank lines and PONGs
//				echo($this->getUsername() . ": ". str_replace("\n", "\\n", str_replace("\r", "\\r", $line)) . "\n");

			//Handshake requires an \r\n
			$line .= "\r\n";

			$this->linesread ++;

			//Try to do a handshake
			if ($this->status == "handshake") {
				//If we can handshake, then we've eaten the data
				if ($this->handshake($line))
					continue;

				echo("Handshake status, masking is " . $this->mask . "\n");

				//Otherwise send them to identification
				$this->setStatus("identify");
			}

			$line = trim($line);

			if ($line == "DISCONNECT") {
				$this->delete();
				return;
			}

			try {
				$this->parse($line);
			} catch (Exception $e) {
				echo("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
				echo("PARSE FOR \"$line\" FAILED\n");
			}
		}
	}

	function parse($data) {
		throw(new Exception());
	}

	/**
	 * Read the connection header and perform a handshake
	 * @param string $data
	 */
	function handshake($data) {
		//echo("Handshake check line " . $this->linesread . "\n");
		if ($this->linesread == 1) {
			//It's the very first line. Should say "GET / HTTP/1.1" or something similar
			if (strstr($data, "GET")) {
				//We're a websocket
				$this->handshaking = true;
//				echo("Started handshaking\n");
			}
		}

		$data = preg_replace('/\r[^\n]/', "\r\n", $data);

		//We have to amass the handshake data dump before we can use it
		if ($this->handshaking) {
			//They send a blank newline to signify the end of the headers
			//If we get more than one newline in a row, then it's the end
			//Otherwise we should store their data and wait
			$this->handshakedata .= $data;

			// echo("Last two: " . str_replace("\n", "\\n", str_replace("\r", "\\r", substr($this->handshakedata, -2))) . "\n");
			// echo("Last four: " . str_replace("\n", "\\n", str_replace("\r", "\\r", substr($this->handshakedata, -4))) . "\n");

			//Check for \n\n and \r\n\r\n because some systems send both
			if (substr($this->handshakedata, -4) == "\r\n\r\n") {
				$this->handshaking = false;
//				echo("Handshake finished...\n");
			} else
				return true;
		} else {
//			echo("No longer handshaking!\n");
			return false;
		}

		$data = $this->handshakedata;

//		echo("Handshake data: " . str_replace("\n", "\\n", str_replace("\r", "\\r", $data)) . "\n");

		$host = "marbleblast.com";
		global $port;

		//Websocket handshaking
		$lines = preg_split("/\n/", str_replace("\r", "", $data));
		$headers = array();

		foreach ($lines as $line) {
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}

		if (!array_key_exists("Sec-WebSocket-Key", $headers)) {
			//Not a websocket
			$this->mask = false;
			return;
		}

		if (array_key_exists("X-Forwarded-For", $headers)) {
			$this->forwardedAddress = $headers["X-Forwarded-For"];
		}

		$secKey = $headers['Sec-WebSocket-Key'];

		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		//Header things that perform handshape, I have no clue
		$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host\r\n" .
		"WebSocket-Location: ws://$host:$port/leader/socketserver.php\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		$this->write($upgrade);
		$this->mask = true;

		$this->handshaking = false;

		//Go to identify mode
		return false;
	}

	function checkIPBan() {
		$address = $this->getAddress();

		$query = pdo_prepare("SELECT * FROM `bannedips` WHERE `address` LIKE :address");
		$query->bind(":address", $address);
		$result = $query->execute();

		if ($result->rowCount()) {
			$row = $result->fetch();

			//Yeah you're banned, GTFO
			$reason = encodeName($row["reason"]);
			$this->write("IDENTIFY BANNED $reason\n");
			$this->delete();
			return;
		}
	}

	/**
	 * Unmask incoming framed message
	 * @param string $text
	 * @return string
	 */
	function unmask($text) {
		$length = ord($text[1]) & 127;
		if ($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} else if ($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); $i ++) {
			$text .= $data[$i] ^ $masks[$i % 4];
		}
		return $text;
	}

	/**
	 * Encode message for transfer to client.
	 * @param string $text
	 * @return string
	 */
	function mask($text) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);

		if ($length <= 125)
			$header = pack('CC', $b1, $length);
		else if ($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		else if ($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header . $text;
	}

	/**
	 * Finds the client with the specified name
	 * @param string $name
	 * @return ClientConnection
	 */
	static function find($name) {
		global $clients;
		foreach ($clients as $client) {
			if (strtolower($client->getUsername()) == strtolower($name))
				return $client;
		}
		return null;
	}

	/**
	 * Finds the client with the specified address
	 * @param string $address
	 * @return ClientConnection
	 */
	static function findIP($address) {
		global $clients;
		foreach ($clients as $client) {
			if ($client->getAddress() == $address)
				return $client;
		}
		return null;
	}

	/**
	 * Finds the client with the specified socket
	 * @param socket $socket
	 * @return ClientConnection
	 */
	static function findSock($socket) {
		global $clients;
		foreach ($clients as $client) {
			if ($client->compare($socket))
				return $client;
		}
		return null;
	}
}

//---------------------------------------------------------------------------
// Functions
//---------------------------------------------------------------------------

//Send something to all the clients
function broadcast($data) {
	global $clients;
	foreach ($clients as $client) {
		if ($client->canReceiveBroadcasts())
			$client->write($data);
	}
	return true;
}

function on_shutdown() {
	global $clients, $port, $socket, $restarting;

	//Disconnect all the clients
	foreach ($clients as $client) {
		if (!$restarting) {
			$client->write("SHUTDOWN\n");
		}

		$client->delete();
	}

	if ($port == "28002") {
		safe_prepare("TRUNCATE TABLE `jloggedin`")->execute();
		safe_prepare("TRUNCATE TABLE `loggedin`")->execute();
	}

	//Close the listening socket
	socket_close($socket);
}

//Makes sure the SQL server is connected and happy
function checkServer() {
	//Try/catch for an SQL error
	try {
		//Just a basic query, if it doesn't work, then the server has disconnected
		global $lb_connection;
		if ($lb_connection)
			$lb_connection->prepare("SELECT * FROM `loggedin`")->execute();
	} catch (Exception $e) {
		echo("Error in checkserver!\n");
		//Server has disconnected or something?

		global $lb_connection, $mysql_data, $mysql_host, $mysql_user, $mysql_pass, $lb_connectionOpen;

		$dsn = "mysql:dbname=" . $mysql_data . ";host=" . $mysql_host;
		//HiGuy: Connect + select
		try {
		   $lb_connection = new SpDatabaseConnection($dsn, $mysql_user, $mysql_pass);
		} catch (SpDatabaseLoginException $e) {
			die("Could not open database connection.\n");
		}
		if ($lb_connection == null) {
			die("Could not connect to database.\n");
		}
		$lb_connectionOpen = true;

		echo("Lost connection to database, but reconnected!\n");
	}
}

function safe_prepare($query) {
	//Make sure the SQL server is alive
	return pdo_prepare($query);
}

//http://stackoverflow.com/a/9711142
function non_block_read($fd, &$data) {
	$read = array($fd);
	$write = array();
	$except = array();
	$result = stream_select($read, $write, $except, 0);
	if($result === false) throw new Exception('stream_select failed');
	if($result === 0) return false;
	$data = stream_get_line($fd, 1);
	return true;
}

?>
