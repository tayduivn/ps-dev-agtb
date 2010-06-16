<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
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

include_once('config.php');
require_once('include/database/PearDatabase.php');
require_once('include/modules.php');
require_once('include/utils.php');
require_once('log4php/LoggerManager.php');
require_once('modules/Users/User.php');
require_once('modules/ACL/ACLController.php');
require_once('include/Localization');

global $beanList, $beanFiles;

$locale = new Localization();

clean_special_arguments();
// cn: set php.ini settings at entry points
setPhpIniSettings();

$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');

// check for old config format.
if(empty($sugar_config) && isset($dbconfig['db_host_name'])) {
   make_sugar_config($sugar_config);
}
if(!empty($sugar_config['session_dir'])) {
    session_save_path($sugar_config['session_dir']);
}
session_start();

///////////////////////////////////////////////////////////////////////////////
////    HANDLE SESSION SECURITY
$user_unique_key = (isset($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : '';
$server_unique_key = (isset($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : '';

if($user_unique_key != $server_unique_key) {
	session_destroy();
	header("Location: index.php?action=Login&module=Users");
	exit();
} elseif(!isset($_SESSION['authenticated_user_id'])) {
	// TODO change this to a translated string.
	session_destroy();
	die("An active session is required to export content");
}

$current_user = new User();
$result = $current_user->retrieve($_SESSION['authenticated_user_id']);
if($result == null) {
	session_destroy();
	die("An active session is required");
}

if(isset($_REQUEST['module']) && isset($_REQUEST['action']) && isset($_REQUEST['record'])) {
	$currentModule = clean_string($_REQUEST['module']);
	$action = clean_string($_REQUEST['action']);
	$record = clean_string($_REQUEST['record']);
} else {
	die ("module, action, and record id all are required");
}
////    END SECURITY HANDLING
///////////////////////////////////////////////////////////////////////////////

// if the language is not set yet, then set it to the default language.
if(isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
	$current_language = $_SESSION['authenticated_user_language'];
} else {
	$current_language = $sugar_config['default_language'];
}
$GLOBALS['log']->debug('current_language is: '.$current_language);

//set module and application string arrays based upon selected language
$app_strings = return_application_language($current_language);
$app_list_strings = return_app_list_strings_language($current_language);
$mod_strings = return_module_language($current_language, $currentModule);

$entity = $beanList[$currentModule];
require_once($beanFiles[$entity]);
$focus = new $entity();
$focus->retrieve(clean_string($_REQUEST['record']));

include("modules/$currentModule/$action.php");
sugar_cleanup();
exit;
?>
