<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
/*********************************************************************************
 * $Id: Logout.php 51719 2009-10-22 17:18:00Z mitani $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/



// record the last theme the user used
$current_user->setPreference('lastTheme',$theme);
$GLOBALS['current_user']->call_custom_logic('before_logout');

// submitted by Tim Scott from SugarCRM forums
foreach($_SESSION as $key => $val) {
	$_SESSION[$key] = ''; // cannot just overwrite session data, causes segfaults in some versions of PHP	
}
if(isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time()-42000, '/');
}

//Update the tracker_sessions table
//BEGIN SUGARCRM flav=pro ONLY
$trackerManager = TrackerManager::getInstance();
if($monitor = $trackerManager->getMonitor('tracker_sessions')){ 
	$monitor->setValue('user_id', $GLOBALS['current_user']->id);
	$monitor->setValue('date_end', TimeDate::getInstance()->nowDb());
	$seconds = strtotime($monitor->date_end) - strtotime($monitor->date_start);
	$monitor->setValue('seconds', $seconds);
	$monitor->setValue('active', 0);
	$trackerManager->saveMonitor($monitor);
}
//END SUGARCRM flav=pro ONLY
// clear out the authenticating flag
session_destroy();

LogicHook::initialize();
$GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');

/** @var AuthenticationController $authController */
$authController->authController->logout();
