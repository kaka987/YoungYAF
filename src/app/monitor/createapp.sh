# !/bin/sh
#拷贝app目录结构到需要的机器上
#   1. scp yaf目录结构到远端机器
#
#@author    changbo<prince.chang@yeahmobi.com>
#@date      2014-03-17
#@version   0.1.0
#运行环境app所在目录(目标机器app目录路径，所有的机器统一采用此规范路径)

if [ ! -n "$1" ]
then
    echo "Usage: sh createapp.sh <appname> [domain]"
    exit
fi

YPP_PATH=/dianyi/app/ypp1.0
APP_NAME="$1"
#服务器创建新目录使用，开发机飘过
#demo: make app APPNAME=yeahmobi
mkdir -p "$YPP_PATH"/code/"$APP_NAME"
mkdir -p "$YPP_PATH"/conf/app/"$APP_NAME"
mkdir -p "$YPP_PATH"/webroot/"$APP_NAME"
mkdir -p "$YPP_PATH"/log/app/"$APP_NAME"
cp -r ./* "$YPP_PATH"/code/"$APP_NAME"
cp ./index.php "$YPP_PATH"/webroot/"$APP_NAME"
sed -i 's/appname/'$APP_NAME'/g' "$YPP_PATH"/webroot/"$APP_NAME"/index.php
