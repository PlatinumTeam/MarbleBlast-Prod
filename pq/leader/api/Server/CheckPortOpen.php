<?php

//Max timeout for servers
$timeout = 5;

//Don't hang our server!
set_time_limit($timeout);

//Port to listen on
$port     = 50000;
$port_max = 50040;
//Maximum length of a data buffer
$maxlen  = 1024;

register_shutdown_function("on_shutdown");

//Client's address+port
if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {
	$remote_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
	$remote_ip = $_SERVER["REMOTE_ADDR"];
}
$remote_port = (array_key_exists("port", $_GET) ? $_GET["port"] : 28000);

//Create a TCP/IP socket
if (!($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
	socketError();
}

//BS socket option which does jack shit other than making me feel good
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $timeout, "usec" => 0));

//Try to bind to a port... I hope this doesn't use all our ports
while (!@socket_bind($socket, "0.0.0.0", $port) && $port < $port_max) {
	$port ++;
}

//Let em know!
socket_sendto($socket, chr(10), 1, 0, $remote_ip, $remote_port); //GameMasterInfoRequest

//Variables for storage
$buffer = "";
$address = "";
$port = "";

//Don't hang this script forever
socket_set_nonblock($socket);

//Timeouts are important, kids
$start = gettimeofday(true);

//Loop this for a few seconds so if they are laggy, we can accommodate
while (gettimeofday(true) - $start < $timeout) {
	//Sleep to give them a chance
	sleep(1);

	//See if they actually listened to any of our messages
	$r = @socket_recvfrom($socket, $buffer, $maxlen, 0, $address, $port);

	//Did we actually get a response?
	if ($r === false) {
		//Blast them with UDP messages until they croak
		socket_sendto($socket, chr(10), 1, 0, $remote_ip, $remote_port);
	} else {
		//We got back _something_. I super hope it's an iTunes code.
		$cmd = ord($buffer[0]);

		//We're done here, they can play
		echo("PORT SUCCESS\n");
		die();
	}
}

//If they got here, they haven't responded. Proabably timed out.
echo("PORT FAILURE\n");

function socketError() {
	die("ERROR [" . socket_last_error() . "] " . socket_strerror(socket_last_error()) . "\n");
}

function on_shutdown() {
	global $socket;

	//Close the listening socket
	socket_close($socket);
}
