#!/bin/bash

SHELLDIR="/dianyi/app/ypp1.0/shell"

if [ ! -n "$1" ]
then
    echo "Usage: sh loghandle.sh [start/stop]"
elif [ "$1" = 'start' ]
then
    nohup sh accesslog.sh >> /dianyi/app/ypp1.0/log/app/yeahmonitor/yeahmonitor_accesslog.log &
    nohup sh weblog.sh >> /dianyi/app/ypp1.0/log/app/yeahmonitor/yeahmonitor_weblog.log &
    nohup sh monitorcore.sh > /dev/null &
    nohup sh load_check.sh > /dev/null &
elif [ "$1" = 'stop' ]
then
    kill -9 `cat $SHELLDIR/run/accesslog.pid`
    kill -9 `cat $SHELLDIR/run/weblog.pid`
    kill -9 `cat $SHELLDIR/run/monitorcore.pid`
    kill -9 `cat $SHELLDIR/run/load_check.pid`
else
    echo "Param error"
fi

