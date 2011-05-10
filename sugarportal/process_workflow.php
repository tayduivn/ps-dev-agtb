<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: process_workflow.php,v 1.19 2006/06/06 17:58:54 majed Exp $
// FILE SUGARCRM flav=pro ONLY 
require_once('include/modules.php');
require_once('config.php');
require_once('modules/Users/User.php');
require_once('modules/Administration/Administration.php');
require_once('log4php/LoggerManager.php');
require_once('modules/ACL/ACLController.php');
require_once('include/utils.php');

clean_special_arguments();
// cn: set php.ini settings at entry points
setPhpIniSettings();

$GLOBALS['log'] = LoggerManager::getLogger('process_workflow');

$app_list_strings = return_app_list_strings_language('en_us');
$app_strings = return_application_language('en_us');

require_once('modules/WorkFlow/WorkFlowSchedule.php');
$mod_strings = return_module_language('en_us', 'WorkFlow');
$current_language = 'en_us';
global $current_language;

//run as admin
	$user = new User();
	$user->retrieve("1");
	global $current_user;
	$current_user = $user;
	


$process_object = new WorkFlowSchedule();
$process_object->process_scheduled();
unset($process_object);


//sugar_cleanup(); // moved to cron.php
?>