#!/bin/bash -x

# Gets the screen session name for the port passed in
# @var $1 Port
# @return $server The session name
getSession () {
	server="lbserver-dev"
	if [ $1 -eq 28002 ]
	then
		server="lbserver"
	fi
}

#Get the session's name
getSession $1

#Start the session
dir=`dirname $0`
screen -dmS $server php "$dir/socketserver.php" $1
