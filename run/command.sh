#!/bin/bash
#应用命令入口

php=php
php_file=$1
lock_file=/tmp/runphp_lock.`echo $1$2$3 | sed 's/\//_/g' | sed 's/^\.*//'`.lock

#echo "locking file: $lock_file"
cmd_line="$php $php_file ${@:2}"

if [ -f /usr/bin/flock ]; then
	flock -x -w 50 $lock_file -c "$cmd_line"
else
	lockfile -r 50 -1 $lock_file
	if [ "$?" -eq "0" ]; then
		$cmd_line
		rm -f $lock_file
	else
		# try to cleanup
		run=`ps aux | grep $php_file | grep -vw 'grep\|sh' | head -n 1 | awk '{print $2}'`
		if [ -n "$run" ]; then
			:
		else
			echo "$php_file not running, cleanup!"
			rm -f $lock_file
		fi
	fi
fi