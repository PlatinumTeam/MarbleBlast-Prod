#!/bin/bash -x

cd $(dirname $0)
./stopchat.sh $1
./startchat.sh $1

# # Gets the screen session for the port passed in
# # @var $1 Port
# # @return $sess The session process ID
# getSession () {
# 	local server="lbserver-dev"
# 	if [ $1 -eq 28002 ]
# 	then
# 		server="lbserver"
# 	fi

# 	sess=$(ps aux | grep "SCREEN" | grep "$server\$" | sed -E 's/^[^ ]+ +([^ ]+).*/\1/g')
# }

# # Gets the php server ID for the port passed in
# # @var $1 Port
# # @return $pid The php process ID
# getServer () {
# 	pid=$(ps aux | grep "socketserver.php" | grep "$1\$" | sed -E 's/^[^ ]+ +([^ ]+).*/\1/g')
# }

# # Stuffs a command into a screen session, followed by a newline
# # @var $1 Session
# # @var $2 Command
# stuff () {
# 	local session=$1
# 	local cmd=$2

# 	screen -S $session -X eval "stuff \"$cmd
# \""
# }

# #Get the PHP process
# getServer $1

# #Get the screen session for the term
# getSession $1

# #Where are we?
# dir=$(dirname $0)

# #If it's running, do something different
# if [ $pid -gt 0 ]
# then
# 	#And tell it to restart
# 	stuff $sess "restart"

# 	#Give it a second
# 	sleep 1

# 	#Check if it's still running
# 	running=0
# 	kill -0 $pid 2>/dev/null && running=1

# 	#If the server is still going, kill it
# 	if [ $running -eq 1 ]
# 	then
# 		stuff $sess "" #\003 (Ctrl+C)
# 		stuff $sess "sleep 1; php $dir/socketserver.php $1"
# 	fi
# else
# 	stuff $sess "" #\003 (Ctrl+C)

# 	sleep 1

# 	stuff $sess "sleep 1; php $dir/socketserver.php $1"
# fi
