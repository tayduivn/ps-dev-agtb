<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
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
