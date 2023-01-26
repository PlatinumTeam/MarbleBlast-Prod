#!/bin/bash -x

# Gets the php server ID for the port passed in
# @var $1 Port
# @return $pid The php process ID
getServer () {
	pid=$(ps aux | grep "masterserver.php" | grep -v "SCREEN" | grep -v "grep" | sed -E 's/^[^ ]+ +([^ ]+).*/\1/g')
}

#Get the screen session for the term
getServer
kill $pid
screen -wipe
