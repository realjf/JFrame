#!/bin/bash
SCRIPT=$(dirname $0)
if [ "$SCRIPT" != "." ];then
    cd $SCRIPT
fi
BASE_DIR=$(dirname $(dirname $(dirname $(pwd))))

# PHP执行命令
if [ -x /usr/local/php/bin/php ] && [ -f /usr/local/php/etc/php.ini.local ]; then
    # 线上环境
    PHP_BIN='/usr/local/php/bin/php -c /usr/local/php/etc/php.ini.local'
elif [ -x /usr/local/php/bin/php ]; then
    # 线上环境
    PHP_BIN='/usr/local/php/bin/php'
else
    # 开发环境
    PHP_BIN=`which php`
fi

if [ "$1" == "" ];then
    echo "输入要执行的文件名"
    exit;
fi
PHP_FILE=$1
LOG_FILE=$(echo $1|sed 's#/#_#g')
shift

if [ `whoami` == "nobody" ]; then
	$PHP_BIN "$PHP_FILE" $BASE_DIR "$@"
else
	# 日志目录
	LOG_DIR=/var/logs/manager.4399.cn/console/$(date +%F)
	mkdir -p $LOG_DIR

	# 执行
	$PHP_BIN "$PHP_FILE" $BASE_DIR "$@" |tee -a $LOG_DIR/youpai.${LOG_FILE}${LOG_FILE_APPEND}.log 2>&1
fi