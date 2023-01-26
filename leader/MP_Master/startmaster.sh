#!/bin/bash -x

#Start the session
dir=`dirname $0`
screen -dmS masterserver php "$dir/masterserver.php"
