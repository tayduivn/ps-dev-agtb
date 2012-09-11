#!/bin/bash
platform='unknown'
unamestr=`uname`
if [[ "$unamestr" == 'Linux' ]]; then
   platform='linux'
elif [[ "$unamestr" == 'FreeBSD' ]]; then
   platform='freebsd'
elif [[ "$unamestr" == 'Darwin' ]]; then
   platform='osx'
fi


if [[ "$platform" == 'osx' ]]; then
	cp .htaccess.sample .htaccess
	cp boxoffice/config.sample.php boxoffice/config.php
	cp configs/config.sample.php configs/config.php


	name="$(osascript -e 'Tell application "System Events" to display dialog "Enter the hostname:" default answer "localhost"' -e 'text returned of result' 2>/dev/null)"
	port="$(osascript -e 'Tell application "System Events" to display dialog "Enter the port:" default answer "8888"' -e 'text returned of result' 2>/dev/null)"
	path="$(osascript -e 'Tell application "System Events" to display dialog "Enter the path from the webroot without initial/trailing slashes:" default answer "sugar66"' -e 'text returned of result' 2>/dev/null)"

	sed -i "" "s/\<web_root\>/$path/g" .htaccess
	sed -i "" "s/\<host\>/$name/g" .htaccess
	sed -i "" "s/\<port\>/$port/g" .htaccess
	sed -i "" "s/<web_root>/$path/g" configs/config.php
	sed -i "" "s/<host>/$name/g" configs/config.php
	sed -i "" "s/<port>/$port/g" configs/config.php
	sed -i "" "s/<host>/$name/g" boxoffice/config.php
	sed -i "" "s/<port>/$port/g" boxoffice/config.php
	sed -i "" "s/<web_root>/$path/g" boxoffice/config.php

	echo "All done. Go forth and install the sweet, sweet goodness."
else
	echo "Platform not supported."
fi
