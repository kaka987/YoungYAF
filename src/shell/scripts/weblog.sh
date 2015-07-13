#!/bin/bash
echo $$ > /dianyi/app/ypp1.0/shell/run/weblog.pid

while [ true ]
do
    php /dianyi/app/ypp1.0/shell/yafClient.php request_uri="/loghandle/weblog/start" &
    
    sleep 1
done
