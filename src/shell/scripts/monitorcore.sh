#!/bin/sh

#################################################
# Yeahmobi data looker script
#
# It just get data and parse data
#
# @author      zhangy<Young@yeahmobi.com>
# @package     Yeahmobi
# @since       Version 1.0.1 @20140408
# @copyright   Copyright (c) 2014, Yeahmobi, Inc.
#
#################################################

echo $$ > /dianyi/app/ypp1.0/shell/run/monitorcore.pid
sleep_time=1

php /dianyi/app/ypp1.0/shell/yafClient.php 'request_uri=/alarm/monitoralarm/reload'
while :
do
    /bin/php /dianyi/app/ypp1.0/shell/yafClient.php "request_uri=/alarm/monitoralarm/monitord" >/dev/null 2>&1 &
    /bin/sleep $sleep_time
done
