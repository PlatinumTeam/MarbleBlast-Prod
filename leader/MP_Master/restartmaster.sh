#!/bin/bash -x

cd $(dirname $0)
./stopmaster.sh $1
./startmaster.sh $1
