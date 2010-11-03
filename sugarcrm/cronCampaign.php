<?php
if(!defined('sugarEntry'))define('sugarEntry', true);

// FILE SUGARCRM INT ONLY
require_once('include/utils.php');
require_once('include/dir_inc.php');
require_once('include/utils/file_utils.php');
require_once('include/modules.php'); // cn: bug 5920
require_once('modules/Schedulers/_AddJobsHere.php');
require_once('include/entryPoint.php');

// BEGIN jostrow customization: script failing after db instantiation moved to another file
require_once("config.php");
require_once('include/database/PearDatabase.php');
$db = PearDatabase::getInstance();
// END jostrow customization

clean_special_arguments();
// cn: set php.ini settings at entry points

if(empty($GLOBALS['log'])) { 
	require_once('log4php/LoggerManager.php'); 
	$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
}

if(empty($sugar_config)) {
	require_once('config.php');
}

if(empty($current_language)) {
	$current_language = $sugar_config['default_language'];
}

if(!isset($current_user) || empty($current_user)) {
	require_once('modules/Users/User.php');
	$current_user = new User();
	$current_user->retrieve('1');	
}


///////////////////////////////////////////////////////////////////////////////
////	PREP FOR SCHEDULER PID
$GLOBALS['log']->debug('--------------------------------------------> at cronCampaign.php <--------------------------------------------');
////	END PREP FOR SCHEDULER PID
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	EXECUTE IF VALID TIME (NOT DDOS)

runMassEmailCampaign();

sugar_cleanup(true);
?>
