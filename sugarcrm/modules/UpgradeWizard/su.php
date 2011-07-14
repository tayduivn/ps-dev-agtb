<?php
//FILE SUGARCRM flav=int ONLY
/**
 * UpgradeWizardCommon
 *
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

///////////////////////////////////////////////////////////////////////////////
////	UTILITIES THAT MUST BE LOCAL :(
function prepSystemForUpgradeSilent() {
	global $subdirs;
	global $sugar_config;

	// make sure dirs exist
	foreach($subdirs as $subdir) {
		if(!is_dir("upload://upgrades/{$subdir}")) {
	    	mkdir_recursive("upload://upgrades/{$subdir}");
		}
	}
}
////	END UTILITIES THAT MUST BE LOCAL :(
///////////////////////////////////////////////////////////////////////////////


// only run from command line
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	die('This utility may only be run from the command line or command prompt.');
}

///////////////////////////////////////////////////////////////////////////////
////	USAGE
$usage =<<<eoq
Usage: php -f su.php [installZipFile] [logFile] [pathToSugarInstance]

On Command Prompt Change directory to where su.php resides. Then type path to php.exe
followed by -f su.php and the arguments.

Example:
    [C:/Program Files/xampp/php/]php.exe -f su.php \
    /home/joey/upgradefiles/SugarPro-Patch-4.5.0g.zip \
    /home/joey/logs/someLogFile.log \
    /var/www/html/joeytest

Arguments:
    installZipFile      Full path to the zip file, generally
                        ./cache/upload/upgrades/patch/[fileName].zip
    logFile             Full path to an alternate log file.

    pathToSugarInstance (optional) Full path to the instance being upgraded.
                        Will fill with current directory if not found.

eoq;
////	END USAGE
///////////////////////////////////////////////////////////////////////////////

if(count($argv) < 2) {
	echo $usage;
	die();
}

///////////////////////////////////////////////////////////////////////////////
////	HANDLE RUNNING FROM PATH OUTSIDE OF INSTANCE
$cwd = getcwd(); // default to current, assumed to be in a valid SugarCRM root dir.
if(isset($argv[3])) {
	if(is_dir($argv[3])) {
		$cwd = $argv[3];
		chdir($cwd);
	} else {
		echo "*******************************************************************************\n";
		echo "*** ERROR: 3rd parameter must be a valid directory.  Tried to cd to [ {$argv[3]} ].\n";
		echo $usage;
		echo "FAILURE\n";
		die();
	}
}

// make sure we're in a Sugar root dir
if(!is_file("{$cwd}/include/entryPoint.php")) {
	echo "*******************************************************************************\n";
	echo "*** ERROR: Tried to execute in a non-SugarCRM root directory.  Pass a 3rd parameter.\n";
	echo $usage;
	echo "FAILURE\n";
	die();
}


///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
if(!defined('sugarEntry')) define('sugarEntry', true);

$_SESSION = array();
$_SESSION['schema_change'] = 'sugar'; // we force-run all SQL
$_SESSION['silent_upgrade'] = true;
$_SESSION['step'] = 'silent'; // flag to NOT try redirect to 4.5.x upgrade wizard

$_REQUEST = array();
$_REQUEST['addTaskReminder'] = 'remind';


require_once('include/entryPoint.php');

require_once('include/utils/zip_utils.php');



//require_once('modules/UpgradeWizard/uw_utils.php'); // must upgrade UW first
require_once("{$cwd}/sugar_version.php"); // provides $sugar_version & $sugar_flavor


///////////////////////////////////////////////////////////////////////////////
////	CONFIRM NECESSARY ARGS
if(count($argv) < 3) {
	echo "*******************************************************************************\n";
	echo "*** ERROR: Missing required parameters.  Received ".($argc - 1)." argument(s), require 2.\n";
	echo $usage;
	echo "FAILURE\n";
	die();
}

if(!is_file($argv[1])) { // valid zip?
	echo "*******************************************************************************\n";
	echo "*** ERROR: First argument must be a full path to the patch file. Got [ {$argv[1]} ].\n";
	echo $usage;
	echo "FAILURE\n";
	die();
}
////	CONFIRM NECESSARY ARGS
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	PREP LOCALLY USED PASSED-IN VARS & CONSTANTS
$GLOBALS['log']	= LoggerManager::getLogger('SugarCRM');
$patchName		= basename($argv[1]);
$zip_from_dir	= substr($patchName, 0, strlen($patchName) - 4); // patch folder name (minus ".zip")
$path			= $argv[2]; // custom log file, if blank will use ./upgradeWizard.log
$db				= &DBManagerFactory::getInstance();
$UWstrings		= return_module_language('en_us', 'UpgradeWizard');
$adminStrings	= return_module_language('en_us', 'Administration');
$mod_strings	= array_merge($adminStrings, $UWstrings);
$subdirs		= array('full', 'langpack', 'module', 'patch', 'theme', 'temp');

$_REQUEST['zip_from_dir'] = $zip_from_dir;

define('SUGARCRM_PRE_INSTALL_FILE', 'scripts/pre_install.php');
define('SUGARCRM_POST_INSTALL_FILE', 'scripts/post_install.php');
define('SUGARCRM_PRE_UNINSTALL_FILE', 'scripts/pre_uninstall.php');
define('SUGARCRM_POST_UNINSTALL_FILE', 'scripts/post_uninstall.php');
////	END PREP LOCALLY USED PASSED-IN VARS
///////////////////////////////////////////////////////////////////////////////

/////retrieve admin user
global $sugar_config;
$configOptions = $sugar_config['dbconfig'];


$current_user = new User();
$current_user->retrieve(1);
if(isset($current_user) && $current_user->user_name == null){
	echo "No admin user in users table. Create an admin user with id = 1\n";
	die();
}
///////////////////////////////////////////////////////////////////////////////
////	UPGRADE PREP
prepSystemForUpgradeSilent();

$unzip_dir = sugar_cached("upgrades/temp/su_temp");
$install_file = "upload://upgrades/patch/".basename($argv[1]);
mkdir_recursive($unzip_dir);
if(!is_dir($unzip_dir)) {
	die("\nFAILURE\n");
}
unzip($argv[1], $unzip_dir);
// mimic standard UW by copy patch zip to appropriate dir
copy($argv[1], $install_file);
////	END UPGRADE PREP
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	UPGRADE UPGRADEWIZARD
$zipBasePath = "$unzip_dir/{$zip_from_dir}";
$uwFiles = findAllFiles("{$zipBasePath}/modules/UpgradeWizard", array());
$destFiles = array();

foreach($uwFiles as $uwFile) {
	$destFile = str_replace($zipBasePath."/", '', $uwFile);
    copy($uwFile, $destFile);
}
require_once('modules/UpgradeWizard/uw_utils.php'); // must upgrade UW first
logThis("*** SILENT UPGRADE INITIATED.", $path);
logThis("*** UpgradeWizard Upgraded  ", $path);

if($configOptions['db_type'] == 'mysql'){
	//Change the db wait_timeout for this session
	$que ="select @@wait_timeout";
	$result = $db->query($que);
	$tb = $db->fetchByAssoc($result);
	logThis('Wait Timeout before change ***** '.$tb['@@wait_timeout'] , $path);
	$query ="set wait_timeout=28800";
	$db->query($query);
	$result = $db->query($que);
	$ta = $db->fetchByAssoc($result);
	logThis('Wait Timeout after change ***** '.$ta['@@wait_timeout'] , $path);
}

////	END UPGRADE UPGRADEWIZARD
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	MAKE SURE PATCH IS COMPATIBLE
if(is_file("$unzip_dir/manifest.php")) {
	// provides $manifest array
	include("$unzip_dir/manifest.php");
	if(!isset($manifest)) {
		die("\nThe patch did not contain a proper manifest.php file.  Cannot continue.\n\n");
	} else {
		copy("$unzip_dir/manifest.php", "upload://upgrades/patch/{$zip_from_dir}-manifest.php");

		$error = validate_manifest($manifest);
		if(!empty($error)) {
			$error = strip_tags(br2nl($error));
			die("\n{$error}\n\nFAILURE\n");
		}
	}
} else {
	die("\nThe patch did not contain a proper manifest.php file.  Cannot continue.\n\n");
}
////	END MAKE SURE PATCH IS COMPATIBLE
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	RUN SILENT UPGRADE
ob_start();
set_time_limit(0);
if(file_exists('ModuleInstall/PackageManager/PackageManagerDisplay.php')) {
	require_once('ModuleInstall/PackageManager/PackageManagerDisplay.php');
}

	$parserFiles = array();

if(file_exists(clean_path("{$zipBasePath}/include/SugarFields"))) {
	$parserFiles = findAllFiles(clean_path("{$zipBasePath}/include/SugarFields"), $parserFiles);
}

 //$cwd = clean_path(getcwd());
foreach($parserFiles as $file) {
	$srcFile = clean_path($file);
	//$targetFile = clean_path(getcwd() . '/' . $srcFile);
    if (strpos($srcFile,".svn") !== false) {
	  //do nothing
    }
    else{
    $targetFile = str_replace(clean_path($zipBasePath), $cwd, $srcFile);

	if(!is_dir(dirname($targetFile))) {
		mkdir_recursive(dirname($targetFile)); // make sure the directory exists
	}

	if(!file_exists($targetFile))
	 {
		//logThis('Copying file to destination: ' . $targetFile);
		if(!copy($srcFile, $targetFile)) {
			logThis('*** ERROR: could not copy file: ' . $targetFile);
		} else {
			$copiedFiles[] = $targetFile;
		}
	} else {
		//logThis('Skipping file: ' . $targetFile);
		//$skippedFiles[] = $targetFile;
	}
   }
 }

/*
$errors = preflightCheck();
if((count($errors) == 1)) { // only diffs
	logThis('file preflight check passed successfully.');
}
else{
	die("\nThe user doesn't have sufficient permissions to write to database'.\n\n");
}
*/
///////////////////////////////////////////////////////////////////////////////
////	MAKE BACKUPS OF TARGET FILES
$rest_dir = clean_path(remove_file_extension($install_file) . "-restore");
$errors = commitMakeBackupFiles($rest_dir, $install_file, $unzip_dir, $zip_from_dir, array(), $path);

///////////////////////////////////////////////////////////////////////////////
////	HANDLE PREINSTALL SCRIPTS
if(empty($errors)) {
	$file = "{$unzip_dir}/".constant('SUGARCRM_PRE_INSTALL_FILE');

	if(is_file($file)) {
		include($file);
		logThis('Running pre_install()...', $path);
		pre_install();
		logThis('pre_install() done.', $path);
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


///////////////////////////////////////////////////////////////////////////////
////	COPY NEW FILES INTO TARGET INSTANCE
if(empty($errors)) {
	$split = commitCopyNewFiles($unzip_dir, $zip_from_dir, $path);
	$copiedFiles = $split['copiedFiles'];
	$skippedFiles = $split['skippedFiles'];
}
/////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////	HANDLE POSTINSTALL SCRIPTS
if(empty($errors)) {
	logThis('Starting post_install()...', $path);
	$file = "{$unzip_dir}/".constant('SUGARCRM_POST_INSTALL_FILE');

	if(is_file($file)) {
		include($file);
		post_install();
	}
    //clean vardefs
	logThis('Performing UWrebuild()...', $path);
		UWrebuild();
	logThis('UWrebuild() done.', $path);

	require("sugar_version.php");
	if(!rebuildConfigFile($sugar_config, $sugar_version)) {
		logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
		$errors[] = 'Could not write config.php!';
	}
	logThis('post_install() done.', $path);
}

///////////////////////////////////////////////////////////////////////////////
////	REGISTER UPGRADE
if(empty($errors)) {
	logThis('Registering upgrade with UpgradeHistory', $path);
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
	$new_upgrade->save();
}

	ob_start();
	 include('modules/Administration/RebuildRelationship.php');
	 $_REQUEST['upgradeWizard'] = true;
	 include('modules/ACL/install_actions.php');
	ob_end_clean();


///////////////////////////////////////////////////////////////////////////////
////	TAKE OUT TRASH
if(empty($errors)) {
	logThis('Taking out the trash, unlinking temp files.', $path);
	unlinkUWTempFiles();
}

///////////////////////////////////////////////////////////////////////////////
////	HANDLE REMINDERS
if(empty($errors)) {
	commitHandleReminders($skippedFiles, $path);
}

if(file_exists(clean_path(getcwd()).'/original451files')){
	rmdir_recursive(clean_path(getcwd()).'/original451files');
}


///////////////////////////////////////////////////////////////////////////////
////	RECORD ERRORS
$phpErrors = ob_get_contents();
ob_end_clean();
logThis("**** Potential PHP generated error messages: {$phpErrors}", $path);

if(count($errors) > 0) {
	foreach($errors as $error) {
		logThis("****** SilentUpgrade ERROR: {$error}", $path);
	}
	echo "FAILED\n";
} else {
	logThis("***** SilentUpgrade completed successfully.", $path);
	echo "SUCCESS\n";
}

?>