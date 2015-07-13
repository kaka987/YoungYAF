#!/bin/bash
while [ ture ]
do
    R=$(($RANDOM%5))
    /usr/local/apache2/bin/ab -c 5 -n $(($R+5)) http://172.30.10.62/yeahmonitor/ > /dev/null &
    /usr/local/apache2/bin/ab -c 5 -n $(($R+5)) http://yeahping.dy/ > /dev/null &

    sleep 1
done
