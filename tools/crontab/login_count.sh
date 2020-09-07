#!/bin/bash

SCRIPT=$0
TOOLS_DIR=$(dirname $0)/..

source $TOOLS_DIR/crontab/utils.sh
checkpid $SCRIPT


for i in {1..1000}
do
    cd $TOOLS_DIR
    sleep 1s
    bash  run.sh loginCount.php > /dev/null 2>&1 &
done
