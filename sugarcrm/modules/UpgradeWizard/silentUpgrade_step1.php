<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//////////////////////////////////////////////////////////////////////////////////////////
//// This is a stand alone file that can be run from the command prompt for upgrading a
//// Sugar Instance. Three parameters are required to be defined in order to execute this file.
//// php.exe -f silentUpgrade.php {Path to Upgrade Package zip} {Path to Log file} {Path to Instance} {Admin User}
//// argv[1] = ZIP file
//// argv[2] = Log file
//// argv[3] = Instance dir
//// argv[4] = Admin user
//// See below the Usage for more details.
//// UPGRADE STEP 1:
//// - Check args
//// - Check preflight settings
//// - Check upgrade validity
//// - Unzip upgrade package
//// - Backup files
//// - Run pre-install
//// - Run 3-way merges
/////////////////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit',-1);
if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
    ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED);
} else {
    ini_set('error_reporting', E_ALL & ~E_STRICT);
}
///////////////////////////////////////////////////////////////////////////////

function verifyArguments($argv,$usage_regular)
{
    if(count($argv) < 5) {
                echo "*******************************************************************************\n";
                echo "*** ERROR: Missing required parameters.  Received ".(count($argv)-1)." argument(s), require 4.\n";
                echo $usage_regular;
                echo "FAILURE\n";
                exit(1);
    }
    $upgradeType = '';
	$cwd = getcwd(); // default to current, assumed to be in a valid SugarCRM root dir.
	if(isset($argv[3]) && is_dir($argv[3])) {
			$cwd = $argv[3];
			chdir($cwd);
	} else {
			echo "*******************************************************************************\n";
			echo "*** ERROR: 3rd parameter must be a valid directory. \n";
			exit(1);
	}

	if(is_file("{$cwd}/include/entryPoint.php")) {
    		//this should be a regular sugar install
    		$upgradeType = constant('SUGARCRM_INSTALL');
    		//check if this is a valid zip file
	    	if(!is_file($argv[1])) { // valid zip?
		        echo "*******************************************************************************\n";
                echo "*** ERROR: First argument must be a full path to the patch file. Got [ {$argv[1]} ].\n";
                echo $usage_regular;
                echo "FAILURE\n";
                exit(1);
		    }
     } else {
            //this should be a regular sugar install
            echo "*******************************************************************************\n";
            echo "*** ERROR: Tried to execute in a non-SugarCRM root directory.\n";
            exit(1);
    }

    return $upgradeType;
}

function prepSystemForUpgradeSilent()
{
	global $subdirs;
	global $cwd;
	global $sugar_config;

	// make sure dirs exist
	foreach($subdirs as $subdir) {
		if(!is_dir($sugar_config['upload_dir']."/upgrades/{$subdir}")) {
			mkdir_recursive($sugar_config['upload_dir']."/upgrades/{$subdir}");
		}
	}
	$base_tmp_upgrade_dir = sugar_cached("upgrades/temp");

	if(file_exists($base_tmp_upgrade_dir.'/upgrade_progress.php')) {
	    unlink($base_tmp_upgrade_dir.'/upgrade_progress.php');
	}
}



//Bug 52872. Dies if the request does not come from CLI.
$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}
//End of #52872

// only run from command line
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	fwrite(STDERR,'This utility may only be run from the command line or command prompt.');
	exit(1);
}
//Clean_string cleans out any file  passed in as a parameter
$_SERVER['PHP_SELF'] = 'silentUpgrade.php';

$usage_regular =<<<eoq2
Usage: php.exe -f silentUpgrade.php [upgradeZipFile] [logFile] [pathToSugarInstance] [admin-user]

On Command Prompt Change directory to where silentUpgrade.php resides. Then type path to
php.exe followed by -f silentUpgrade.php and the arguments.

Example:
    [path-to-PHP/]php.exe -f silentUpgrade.php [path-to-upgrade-package/]SugarEnt-Upgrade-5.2.0-to-5.5.0.zip [path-to-log-file/]silentupgrade.log  [path-to-sugar-instance/] admin

Arguments:
    upgradeZipFile                       : Upgrade package file.
    logFile                              : Silent Upgarde log file.
    pathToSugarInstance                  : Sugar Instance instance being upgraded.
    admin-user                           : admin user performing the upgrade
eoq2;
////	END USAGE
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
if(!defined('sugarEntry')) define('sugarEntry', true);

$_SESSION = array();
$_SESSION['schema_change'] = 'sugar'; // we force-run all SQL
$_SESSION['silent_upgrade'] = true;
$_SESSION['step'] = 'silent'; // flag to NOT try redirect to 4.5.x upgrade wizard

$_REQUEST = array();

define('SUGARCRM_INSTALL', 'SugarCRM_Install');
define('SUGARCRM_PRE_INSTALL_FILE', 'scripts/pre_install.php');

global $cwd;
$cwd = getcwd(); // default to current, assumed to be in a valid SugarCRM root dir.
touch($argv[2]);
$path			= realpath($argv[2]); // custom log file, if blank will use ./upgradeWizard.log

$upgradeType = verifyArguments($argv,$usage_regular);

///////////////////////////////////////////////////////////////////////////////
//////  Verify that all the arguments are appropriately placed////////////////
global $sugar_config;
$errors = array();

require_once('include/entryPoint.php');
require_once('include/utils/zip_utils.php');
$cwd = $argv[3];

$GLOBALS['log']	= LoggerManager::getLogger('SugarCRM');
$patchName		= basename($argv[1]);
$zip_from_dir	= substr($patchName, 0, strlen($patchName) - 4); // patch folder name (minus ".zip")

$db				= DBManagerFactory::getInstance();
$UWstrings		= return_module_language('en_us', 'UpgradeWizard');
$adminStrings	= return_module_language('en_us', 'Administration');
$app_list_strings = return_app_list_strings_language('en_us');
$mod_strings	= array_merge($adminStrings, $UWstrings);
$subdirs		= array('full', 'langpack', 'module', 'patch', 'theme', 'temp');

global $unzip_dir;

//////////////////////////////////////////////////////////////////////////////
//Adding admin user to the silent upgrade

$current_user = new User();
if(isset($argv[4])) {
    //if being used for internal upgrades avoid admin user verification
	$user_name = $argv[4];
	$q = "select id from users where user_name = '" . $user_name . "' and is_admin=1";
	$result = $db->query($q, false);
	$logged_user = $db->fetchByAssoc($result);
/////retrieve admin user
	if(empty($logged_user['id']) && $logged_user['id'] != null){
	   	echo "FAILURE: Not an admin user in users table. Please provide an admin user\n";
		exit(1);
	}
} else {
		echo "*******************************************************************************\n";
		echo "*** ERROR: 4th parameter must be a valid admin user.\n";
		echo $usage;
		echo "FAILURE\n";
		exit(1);
}


global $sugar_config;
$configOptions = $sugar_config['dbconfig'];

echo "\n";
echo "********************************************************************\n";
echo "************ This Upgrade process may take some time ***************\n";
echo "********************************************************************\n";
echo "\n";

///////////////////////////////////////////////////////////////////////////////
////	UPGRADE PREP
prepSystemForUpgradeSilent();

$unzip_dir = sugar_cached("upgrades/temp");
$install_file = $sugar_config['upload_dir']."/upgrades/patch/".basename($argv[1]);

$_SESSION['unzip_dir'] = $unzip_dir;
$_SESSION['install_file'] = $install_file;
$_SESSION['zip_from_dir'] = $zip_from_dir;
if(is_dir($unzip_dir.'/scripts'))
{
	rmdir_recursive($unzip_dir.'/scripts');
}
if(is_file($unzip_dir.'/manifest.php'))
{
	rmdir_recursive($unzip_dir.'/manifest.php');
}
mkdir_recursive($unzip_dir);
if(!is_dir($unzip_dir)) {
	echo "\n{$unzip_dir} is not an available directory\nFAILURE\n";
	exit(1);
}
unzip($argv[1], $unzip_dir);
// check that data was unpacked
$zipBasePath = "$unzip_dir/{$zip_from_dir}";
if(!is_file("$zipBasePath/sugar_version.php")) {
    echo "\n$cwd/{$zipBasePath}/sugar_version.php was not extracted\nFAILURE\n";
    exit(1);
}
// mimic standard UW by copy patch zip to appropriate dir
copy($argv[1], $install_file);
////	END UPGRADE PREP
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	MAKE SURE PATCH IS COMPATIBLE
if(is_file("$unzip_dir/manifest.php")) {

    //Check if uw_utils.php exists in zip package, fall back to existing file if not found (needed for flavor conversions)
    if(file_exists("{$zipBasePath}/modules/UpgradeWizard/uw_utils.php")) {
        require_once("{$zipBasePath}/modules/UpgradeWizard/uw_utils.php");
    } else {
        require_once("modules/UpgradeWizard/uw_utils.php");
    }

    // provides $manifest array
	include("$unzip_dir/manifest.php");
	if(!isset($manifest)) {
		echo "\nThe patch did not contain a proper manifest.php file.  Cannot continue.\nFAILURE\n";
		exit(1);
	} else {
		copy("$unzip_dir/manifest.php", $sugar_config['upload_dir']."/upgrades/patch/{$zip_from_dir}-manifest.php");
		$error = validate_manifest($manifest);
		if(!empty($error)) {
			$error = strip_tags(br2nl($error));
			echo "\n{$error}\n\nFAILURE\n";
			exit(1);
		}
	}
} else {
	echo "\nThe patch did not contain a proper manifest.php file.  Cannot continue.\nFAILURE\n";
	exit(1);
}

logThis("**** Upgrade checks passed", $path);
///// DONE WITH CHECKS
///////////////////////////////////////////////////////////////////////////////
////  BACKUP FILES
$rest_dir = remove_file_extension($install_file) . "-restore";
$errors = commitMakeBackupFiles($rest_dir, $install_file, $unzip_dir, $zip_from_dir, array(), $path);
logThis("**** Backup complete", $path);

///////////////////////////////////////////////////////////////////////////////
<<<<<<< HEAD
////	HANDLE PREINSTALL SCRIPTS
=======
////	RUN SILENT UPGRADE
ob_start();
set_time_limit(0);
if(file_exists('ModuleInstall/PackageManager/PackageManagerDisplay.php')) {
	require_once('ModuleInstall/PackageManager/PackageManagerDisplay.php');
}


	//copy minimum required files including sugar_file_utils.php
	if(file_exists("{$zipBasePath}/include/utils/sugar_file_utils.php")){
		$destFile = clean_path(str_replace($zipBasePath, $cwd, "{$zipBasePath}/include/utils/sugar_file_utils.php"));
		copy("{$zipBasePath}/include/utils/sugar_file_utils.php", $destFile);
	}
	if(file_exists('include/utils/sugar_file_utils.php')){
    	require_once('include/utils/sugar_file_utils.php');
    }

/*
$errors = preflightCheck();
if((count($errors) == 1)) { // only diffs
	logThis('file preflight check passed successfully.', $path);
}
else{
	fwrite(STDERR,"\nThe user doesn't have sufficient permissions to write to database'.\n\n");
	exit(1);
}
*/
//If version less than 500 then look for modules to be upgraded
if(function_exists('set_upgrade_vars')){
	set_upgrade_vars();
}
//Initialize the session variables. If upgrade_progress.php is already created
//look for session vars there and restore them
if(function_exists('initialize_session_vars')){
	initialize_session_vars();
}

if(!didThisStepRunBefore('preflight')){
	set_upgrade_progress('preflight','in_progress');
	//Quickcreatedefs on the basis of editviewdefs
    updateQuickCreateDefs();
	set_upgrade_progress('preflight','done');
}
////////////////COMMIT PROCESS BEGINS///////////////////////////////////////////////////////////////
////	MAKE BACKUPS OF TARGET FILES

if(!didThisStepRunBefore('commit')){
	set_upgrade_progress('commit','in_progress','commit','in_progress');
	if(!didThisStepRunBefore('commit','commitMakeBackupFiles')){
		set_upgrade_progress('commit','in_progress','commitMakeBackupFiles','in_progress');
		$errors = commitMakeBackupFiles($rest_dir, $install_file, $unzip_dir, $zip_from_dir, array());
		set_upgrade_progress('commit','in_progress','commitMakeBackupFiles','done');
	}

	//Need to make sure we have the matching copy of SetValueAction for static/instance method matching
    if(file_exists("include/Expressions/Actions/SetValueAction.php")){
        require_once("include/Expressions/Actions/SetValueAction.php");
    }

	///////////////////////////////////////////////////////////////////////////////
	////	HANDLE PREINSTALL SCRIPTS
	if(empty($errors)) {
		$file = "{$unzip_dir}/".constant('SUGARCRM_PRE_INSTALL_FILE');

		if(is_file($file)) {
			include($file);
			if(!didThisStepRunBefore('commit','pre_install')){
				set_upgrade_progress('commit','in_progress','pre_install','in_progress');
				pre_install();
				set_upgrade_progress('commit','in_progress','pre_install','done');
			}
		}
	}

	//Clean smarty from cache
	$cachedir = sugar_cached('smarty');
	if(is_dir($cachedir)){
		$allModFiles = array();
		$allModFiles = findAllFiles($cachedir,$allModFiles);
	   foreach($allModFiles as $file){
	       	//$file_md5_ref = str_replace(clean_path(getcwd()),'',$file);
	       	if(file_exists($file)){
				unlink($file);
	       	}
	   }
	}

		//Also add the three-way merge here. The idea is after the 451 html files have
		//been converted run the 3-way merge. If 500 then just run the 3-way merge
		if(file_exists('modules/UpgradeWizard/SugarMerge/SugarMerge.php')){
		    set_upgrade_progress('end','in_progress','threewaymerge','in_progress');
		    require_once('modules/UpgradeWizard/SugarMerge/SugarMerge.php');
		    $merger = new SugarMerge($zipBasePath);
		    $merger->mergeAll();
		    set_upgrade_progress('end','in_progress','threewaymerge','done');
		}
	///////////////////////////////////////////////////////////////////////////////
	////	COPY NEW FILES INTO TARGET INSTANCE

     if(!didThisStepRunBefore('commit','commitCopyNewFiles')){
			set_upgrade_progress('commit','in_progress','commitCopyNewFiles','in_progress');
			$split = commitCopyNewFiles($unzip_dir, $zip_from_dir);
	 		$copiedFiles = $split['copiedFiles'];
	 		$skippedFiles = $split['skippedFiles'];
			set_upgrade_progress('commit','in_progress','commitCopyNewFiles','done');
	 }
    
	require_once(clean_path($unzip_dir.'/scripts/upgrade_utils.php'));
	$new_sugar_version = getUpgradeVersion();
    $siv_varset_1 = setSilentUpgradeVar('origVersion', $sugar_version);
    $siv_varset_2 = setSilentUpgradeVar('destVersion', $new_sugar_version);
    $siv_write    = writeSilentUpgradeVars();
    if(!$siv_varset_1 || !$siv_varset_2 || !$siv_write){
        logThis("Error with silent upgrade variables: origVersion write success is ({$siv_varset_1}) ".
        		"-- destVersion write success is ({$siv_varset_2}) -- ".
        		"writeSilentUpgradeVars success is ({$siv_write}) -- ".
        		"path to cache dir is ({$GLOBALS['sugar_config']['cache_dir']})", $path);
    }
     require_once('modules/DynamicFields/templates/Fields/TemplateText.php');
	///////////////////////////////////////////////////////////////////////////////
    ///    RELOAD NEW DEFINITIONS
    global $ACLActions, $beanList, $beanFiles;
    include('modules/ACLActions/actiondefs.php');
    include('include/modules.php');
	/////////////////////////////////////////////

    if (!function_exists("inDeveloperMode")) {
        //this function was introduced from tokyo in the file include/utils.php, so when upgrading from 5.1x and 5.2x we should declare the this function
        function inDeveloperMode()
        {
            return isset($GLOBALS['sugar_config']['developerMode']) && $GLOBALS['sugar_config']['developerMode'];
        }
    }
	///////////////////////////////////////////////////////////////////////////////
	////	HANDLE POSTINSTALL SCRIPTS
	if(empty($errors)) {
		logThis('Starting post_install()...', $path);

		$trackerManager = TrackerManager::getInstance();
        $trackerManager->pause();
        $trackerManager->unsetMonitors();

		if(!didThisStepRunBefore('commit','post_install')){
			$file = "$unzip_dir/" . constant('SUGARCRM_POST_INSTALL_FILE');
			if(is_file($file)) {
				//set_upgrade_progress('commit','in_progress','post_install','in_progress');
				$progArray['post_install']='in_progress';
				post_install_progress($progArray,'set');
				    global $moduleList;
					include($file);
					post_install();
				// cn: only run conversion if admin selects "Sugar runs SQL"
				if(!empty($_SESSION['allTables']) && $_SESSION['schema_change'] == 'sugar')
					executeConvertTablesSql($_SESSION['allTables']);
				//set process to done
				$progArray['post_install']='done';
				//set_upgrade_progress('commit','in_progress','post_install','done');
				post_install_progress($progArray,'set');
			}
		}
	    //clean vardefs
		logThis('Performing UWrebuild()...', $path);
		ob_start();
			@UWrebuild();
		ob_end_clean();
		logThis('UWrebuild() done.', $path);

		logThis('begin check default permissions .', $path);
	    	checkConfigForPermissions();
	    logThis('end check default permissions .', $path);

	    logThis('begin check logger settings .', $path);
	    	checkLoggerSettings();
	    logThis('begin check logger settings .', $path);

            logThis('begin check lead conversion settings .', $path);
            checkLeadConversionSettings();
	    logThis('end check lead conversion settings .', $path);

	    logThis('begin check resource settings .', $path);
			checkResourceSettings();
		logThis('begin check resource settings .', $path);


		require("sugar_version.php");
		require('config.php');
		global $sugar_config;

		if($ce_to_pro_ent){
			if(isset($sugar_config['sugarbeet']))
			{
			    //$sugar_config['sugarbeet'] is only set in COMM
			    unset($sugar_config['sugarbeet']);
			}
		    if(isset($sugar_config['disable_team_access_check']))
			{
			    //$sugar_config['disable_team_access_check'] is a runtime configration,
			    //no need to write to config.php
			    unset($sugar_config['disable_team_access_check']);
			}
			if(!merge_passwordsetting($sugar_config, $sugar_version)) {
				logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
				$errors[] = 'Could not write config.php!';
			}

		}

		logThis('Set default_theme to Sugar', $path);
		$sugar_config['default_theme'] = 'Sugar';

		if( !write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ) {
            logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
            $errors[] = 'Could not write config.php!';
        }

        logThis('Set default_max_tabs to 7', $path);
		$sugar_config['default_max_tabs'] = '7';

		if( !write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ) {
            logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
            $errors[] = 'Could not write config.php!';
        }

        if (version_compare($new_sugar_version, $sugar_version, '='))
        {
            require('config.php');
        }
        //upgrade the sugar version prior to writing config file.
        logThis('Upgrade the sugar_version', $path);
        $sugar_config['sugar_version'] = $sugar_version;

        if( !write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ) {
            logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
            $errors[] = 'Could not write config.php!';
        }

		logThis('post_install() done.', $path);
	}

	///////////////////////////////////////////////////////////////////////////////
	////	REGISTER UPGRADE
	if(empty($errors)) {
		logThis('Registering upgrade with UpgradeHistory', $path);
		if(!didThisStepRunBefore('commit','upgradeHistory')){
			set_upgrade_progress('commit','in_progress','upgradeHistory','in_progress');
			$file_action = "copied";
			// if error was encountered, script should have died before now
			$new_upgrade = new UpgradeHistory();
			$new_upgrade->filename = $install_file;
			$new_upgrade->md5sum = md5_file($install_file);
			$new_upgrade->name = $zip_from_dir;
			$new_upgrade->description = $manifest['description'];
			$new_upgrade->type = 'patch';
			$new_upgrade->version = $sugar_version;
			$new_upgrade->status = "installed";
			$new_upgrade->manifest = (!empty($_SESSION['install_manifest']) ? $_SESSION['install_manifest'] : '');

			if($new_upgrade->description == null){
				$new_upgrade->description = "Silent Upgrade was used to upgrade the instance";
			}
			else{
				$new_upgrade->description = $new_upgrade->description." Silent Upgrade was used to upgrade the instance.";
			}
		   $new_upgrade->save();
		   set_upgrade_progress('commit','in_progress','upgradeHistory','done');
		   set_upgrade_progress('commit','done','commit','done');
		}
	  }

	//Clean modules from cache
	    $cachedir = sugar_cached('smarty');
		if(is_dir($cachedir)){
			$allModFiles = array();
			$allModFiles = findAllFiles($cachedir,$allModFiles);
		   foreach($allModFiles as $file){
		       	//$file_md5_ref = str_replace(clean_path(getcwd()),'',$file);
		       	if(file_exists($file)){
					unlink($file);
		       	}
		   }
		}
   //delete cache/modules before rebuilding the relations
   	//Clean modules from cache
   	    $cachedir = sugar_cached('modules');
		if(is_dir($cachedir)){
			$allModFiles = array();
			$allModFiles = findAllFiles($cachedir,$allModFiles);
		   foreach($allModFiles as $file){
		       	//$file_md5_ref = str_replace(clean_path(getcwd()),'',$file);
		       	if(file_exists($file)){
					unlink($file);
		       	}
		   }
		}

		//delete cache/themes
		$cachedir = sugar_cached('themes');
		if(is_dir($cachedir)){
			$allModFiles = array();
			$allModFiles = findAllFiles($cachedir,$allModFiles);
		   foreach($allModFiles as $file){
		       	//$file_md5_ref = str_replace(clean_path(getcwd()),'',$file);
		       	if(file_exists($file)){
					unlink($file);
		       	}
		   }
		}
	ob_start();
	if(!isset($_REQUEST['silent'])){
		$_REQUEST['silent'] = true;
	}
	else if(isset($_REQUEST['silent']) && $_REQUEST['silent'] != true){
		$_REQUEST['silent'] = true;
	}

	 //logThis('Checking for leads_assigned_user relationship and if not found then create.', $path);
	@createMissingRels();
	 //logThis('Checked for leads_assigned_user relationship.', $path);
	ob_end_clean();
}

set_upgrade_progress('end','in_progress','end','in_progress');
/////////////////////////Old Logger settings///////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

if(function_exists('deleteCache')){
	set_upgrade_progress('end','in_progress','deleteCache','in_progress');
	@deleteCache();
	set_upgrade_progress('end','in_progress','deleteCache','done');
}

///////////////////////////////////////////////////////////////////////////////
////	HANDLE REMINDERS
>>>>>>> 6_6_2
if(empty($errors)) {
    logThis("**** Pre-install scripts ", $path);
    $file = "{$unzip_dir}/".constant('SUGARCRM_PRE_INSTALL_FILE');

<<<<<<< HEAD
	if(is_file($file)) {
		require($file);
		pre_install();
=======
        //new modules list now has left over modules which are new to this install, so lets add them to the system tabs
        logThis('new modules to add are '.var_export($newModuleList,true),$path);

        //grab the existing system tabs
        $tabs = $newTB->get_system_tabs();

        //add the new tabs to the array
        foreach($newModuleList as $nm ){
          $tabs[$nm] = $nm;
        }

        //now assign the modules to system tabs
        $newTB->set_system_tabs($tabs);
        logThis('module tabs updated',$path);
}

//Also set the tracker settings if  flavor conversion ce->pro or ce->ent
if(isset($_SESSION['current_db_version']) && isset($_SESSION['target_db_version'])){
    if (version_compare($_SESSION['current_db_version'], $_SESSION['target_db_version'], '='))
    {
	    $_REQUEST['upgradeWizard'] = true;
	    ob_start();
			include('include/Smarty/internals/core.write_file.php');
		ob_end_clean();
	 	$db =& DBManagerFactory::getInstance();
		if($ce_to_pro_ent){
	        //Also set license information
	        $admin = new Administration();
			$category = 'license';
			$value = 0;
			$admin->saveSetting($category, 'users', $value);
			$key = array('num_lic_oc','key','expire_date');
			$value = '';
			foreach($key as $k){
				$admin->saveSetting($category, $k, $value);
			}
		}
	}
}

	$phpErrors = ob_get_contents();
	ob_end_clean();
	logThis("**** Potential PHP generated error messages: {$phpErrors}", $path);

	if(count($errors) > 0) {
		foreach($errors as $error) {
			logThis("****** SilentUpgrade ERROR: {$error}", $path);
		}
		echo "FAILED\n";
	}


}


/**
 * repairTableDictionaryExtFile
 *
 * There were some scenarios in 6.0.x whereby the files loaded in the extension tabledictionary.ext.php file
 * did not exist.  This would cause warnings to appear during the upgrade.  As a result, this
 * function scans the contents of tabledictionary.ext.php and then remove entries where the file does exist.
 */
function repairTableDictionaryExtFile()
{
	$tableDictionaryExtDirs = array('custom/Extension/application/Ext/TableDictionary', 'custom/application/Ext/TableDictionary');

	foreach($tableDictionaryExtDirs as $tableDictionaryExt)
	{

		if(is_dir($tableDictionaryExt) && is_writable($tableDictionaryExt)){
			$dir = dir($tableDictionaryExt);
			while(($entry = $dir->read()) !== false)
			{
				$entry = $tableDictionaryExt . '/' . $entry;
				if(is_file($entry) && preg_match('/\.php$/i', $entry) && is_writeable($entry))
				{

						if(function_exists('sugar_fopen'))
						{
							$fp = @sugar_fopen($entry, 'r');
						} else {
							$fp = fopen($entry, 'r');
						}


					    if($fp)
				        {
				             $altered = false;
				             $contents = '';

				             while($line = fgets($fp))
						     {
						    	if(preg_match('/\s*include\s*\(\s*[\'|\"](.*?)[\"|\']\s*\)\s*;/', $line, $match))
						    	{
						    	   if(!file_exists($match[1]))
						    	   {
						    	      $altered = true;
						    	   } else {
						    	   	  $contents .= $line;
						    	   }
						    	} else {
						    	   $contents .= $line;
						    	}
						     }

						     fclose($fp);
				        }


					    if($altered)
					    {
							if(function_exists('sugar_fopen'))
							{
								$fp = @sugar_fopen($entry, 'w');
							} else {
								$fp = fopen($entry, 'w');
							}

							if($fp && fwrite($fp, $contents))
							{
								fclose($fp);
							}
					    }
				} //if
			} //while
		} //if
>>>>>>> 6_6_2
	}
    logThis("**** Pre-install complete", $path);
}
<<<<<<< HEAD

//Also add the three-way merge here.
logThis("**** Merge started ", $path);
require_once('modules/UpgradeWizard/SugarMerge/SugarMerge.php');
$merger = new SugarMerge($zipBasePath);
$merger->mergeAll();
logThis("**** Merge finished ", $path);
=======
>>>>>>> 6_6_2
