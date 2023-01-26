#!/bin/bash -x

port=$1
server="prox-$1"

#Start the session
dir=`dirname $0`
screen -dmS $server php "$dir/serverproxy.php" $port "localhost:28002"
