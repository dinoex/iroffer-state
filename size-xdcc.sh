#!/bin/sh
(
	grep -h file mybot.xdcc | sed -e 's=^xx_file ==' |
	while read name
	do
		echo -n "${name}:"
		ls -l "${name}" |
		awk '{ print $5 }'
	done
) > size.data
