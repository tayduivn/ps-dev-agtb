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
ob_implicit_flush();
ignore_user_abort(true);// keep processing if browser is closed
set_time_limit(0);// no time out

if(empty($GLOBALS['log'])) {
	
	$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM'); 	
}
require_once('modules/Schedulers/SchedulerDaemon.php');



$GLOBALS['log']->debug('---->SDLauncher.php touched');

echo '.'; // for the curl_exec() to return something other than '';

$user = new User();
$user->retrieve('1', true);

$daemon = new SchedulerDaemon();
$daemon->runAsUserName = $user->user_name;
//$daemon->runAsUserPassword = $user->user_password;

$GLOBALS['log']->debug("----->Daemon setting socketAddress: (".$daemon->socketAddressDaemon.") and socketPort: (".$daemon->socketPortDaemon.")");
$daemon->socketAddress = $daemon->socketAddressDaemon;
$daemon->socketPort = $daemon->socketPortDaemon;

$GLOBALS['log']->debug("----->Daemon creating listener");
if($daemon->createListener()) {
	$GLOBALS['log']->debug("----->Daemon listener created successfully!");
} else {
	$GLOBALS['log']->debug("----->Daemon FAILURE listener NOT created!");
}

$daemon->watch();


?>
