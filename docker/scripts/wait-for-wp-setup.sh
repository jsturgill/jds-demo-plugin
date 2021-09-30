#!/bin/sh
flag=1

while [ "$flag" = 1 ]
do
	test=$(curl -s -o /dev/null -w "%{http_code}" "http://nginx/call-bash-script.php?script=init-wp.sh")
	if [ "$test" = '200' ]
	then
		echo "wordpress installed"
		flag=0;
	else
		echo "wordpress installation not yet complete ($test)"
		sleep 2;
	fi
done

# execute arguments as a command
echo "Calling command:" "$@"
exec "$@"
