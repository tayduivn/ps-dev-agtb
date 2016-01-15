<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
//FILE SUGARCRM flav=int ONLY
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

echo "creating socket:<Br>";
$s = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
echo "binding socket:<Br>";
$sb = socket_bind($s, '127.0.0.1','10010');
echo "socket listening:<Br>";
$sl = socket_listen($s, 5);

echo "socket setoption:<Br>";
socket_set_option($s, SOL_SOCKET,SO_REUSEADDR,1);
echo "socket non-block:<Br>";
socket_set_nonblock($s);

$buf = '';
$count = 0;

echo "starting while loop:<Br>";
while(true) {
//	echo "incrementing count:<Br>";

	$sm = @socket_accept($s);
	if(is_resource($sm)) {
		$msg = "connected!\n";
		socket_write($sm, $msg, strlen($msg));
		
		while(true) {
		
			if(($recv = socket_read($sm, 2048, PHP_NORMAL_READ)) !== false) {
				usleep(500);
				if(socket_last_error($sm) == 11) {
					usleep(500);
					echo '. ';
					continue;
				} elseif($recv != "") {
					usleep(500);
					echo '. ';
					echo $recv;
					break;
				} else {
					usleep(500);
					echo "socket is dead? ".socket_strerror(socket_last_error($s));
					break;
				}
			}
		} // inner while
		
		echo "passed \$sm";
	} else {
		echo '. ';
		
	}

	$count++;
	sleep(1);
	echo $count."<br>";
	if($count > 50) { break; }

}
echo "looped: ".$count;

socket_close($sm);
socket_close($s);
?>
