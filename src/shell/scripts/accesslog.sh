#! /bin/bash
echo $$ > /dianyi/app/ypp1.0/shell/run/accesslog.pid
while [ true ]
do
    num=`ps -ef | grep yafClient.php | wc -l`
    if [ $num -lt 6 ];then
        php /dianyi/app/ypp1.0/shell/yafClient.php request_uri="/loghandle/accesslog/start" 
    fi
    sleep 1
done
