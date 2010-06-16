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
if (version_compare(phpversion(),'5.2.1') < 0) {
    $msg = 'Minimum PHP version required is 5.2.1.  You are using PHP version  '. phpversion();
    die($msg);
}
session_start();
require_once('log4php/LoggerManager.php');
require_once('sugar_version.php');
require_once('include/utils.php');
require_once('install/install_utils.php');
require_once('include/TimeDate.php');
$timedate = new TimeDate();
// cn: set php.ini settings at entry points
setPhpIniSettings();

//_ppd($_REQUEST);
if(get_magic_quotes_gpc() == 1){
   $_REQUEST = array_map("stripslashes_checkstrings", $_REQUEST);
   $_POST = array_map("stripslashes_checkstrings", $_POST);
   $_GET = array_map("stripslashes_checkstrings", $_GET);
}


$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
$setup_sugar_version = $sugar_version;
$install_script = true;

///////////////////////////////////////////////////////////////////////////////
////	INSTALLER LANGUAGE

$supportedLanguages = array(
	'ch_sm'	=> 'Chinese Simplified - 简体中文',
	'ch_tr'	=> 'Chinese Traditional - 正體中文',
	'en_us'	=> 'English (US)',
	'en_uk'	=> 'English (UK)',
	'ja'	=> 'Japanese - 日本語',
	'fr_fr'	=> 'French - Français',
	'ge_ge'	=> 'German - Deutche',
	'pt_br'	=> 'Portuguese (Brazil)',
	'pt_pt'	=> 'Portuguese (Portugal)',
	'sp_sp'	=> 'Spanish (Spain) - Español',
	'sp_la'	=> 'Spanish (Latin America) - Español',
);


// after install language is selected, use that pack
$default_lang = 'en_us';
if(!isset($_POST['language']) && (!isset($_SESSION['language']) && empty($_SESSION['language']))) {
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$lang = parseAcceptLanguage();
		if(isset($supportedLanguages[$lang])) {
			$_POST['language'] = $lang;
		} else {
			$_POST['language'] = $default_lang;
		}	
	}
}
if(isset($_POST['language'])) {
	$_SESSION['language'] = strtolower(str_replace('-','_',$_POST['language']));
}
$current_language = isset($_SESSION['language']) ? $_SESSION['language'] : $default_lang;
if(file_exists("install/language/{$current_language}.lang.php")) {
	require_once("install/language/{$current_language}.lang.php");
} else {
	require_once("install/language/{$default_lang}.lang.php");
}

if($current_language != 'en_us') {
	$my_mod_strings = $mod_strings;
	include('install/language/en_us.lang.php');
	$mod_strings = sugarArrayMerge($mod_strings, $my_mod_strings);
}

////	END INSTALLER LANGUAGE
///////////////////////////////////////////////////////////////////////////////

//if this license print, then redirect and exit,
if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'licensePrint')
{
    include('install/licensePrint.php');
    exit ();
}

// always perform
clean_special_arguments();
print_debug_comment();

$next_clicked = false;
$next_step = 0;

//check if this is an offline client installation
$step6 = 'licenseKey.php';
if(file_exists('config.php')){
    global $sugar_config;
    require_once('config.php');
    if(isset($sugar_config['disc_client']) && $sugar_config['disc_client'] == true){
        $step6 = 'oc_install.php';   
        $_SESSION['oc_install'] = true; 
    }else{
        $_SESSION['oc_install'] = false;    
    }
}

// use a simple array to map out the steps of the installer page flow
$workflow = array(
	'welcome.php',
	'license.php',
	'checkSystem.php',
	'siteConfig.php',
	'confirmSettings.php',
	'performSetup.php',
    'finalInstructions.php',
);

// increment/decrement the workflow pointer
if(!empty($_REQUEST['goto'])){
    switch($_REQUEST['goto']){
        case $mod_strings['LBL_CHECKSYS_RECHECK']:
            $next_step = $_REQUEST['current_step'];
            break;
        case $mod_strings['LBL_BACK']:
            $next_step = $_REQUEST['current_step'] - 1;
            break;
        case $mod_strings['LBL_NEXT']:
        case $mod_strings['LBL_START']:
            $next_step = $_REQUEST['current_step'] + 1;
            $next_clicked = true;
            break;
        case 'SilentInstall':
            $next_step = 9999;
            break;
        case 'oc_convert':
            $next_step = 9191;
            break;
    }
}

$validation_errors = array();

// process the data posted
if($next_clicked){
    // store the submitted data because the 'Next' button was clicked
    switch($workflow[trim($_REQUEST['current_step'])]){
        case 'welcome.php':
            // eventually default all vars here, with overrides from config.php
            if( is_readable('config.php') ) {
                include_once('config.php');
            }

            $default_db_type = 'mysql';
            //BEGIN SUGARCRM flav=int ONLY 
            $default_db_type = 'mysql';
            //END SUGARCRM flav=int ONLY 

            if( !isset($_SESSION['setup_db_type']) ){
                $_SESSION['setup_db_type'] = empty($sugar_config['dbconfig']['db_type']) ? $default_db_type : $sugar_config['dbconfig']['db_type'];
            }
            break;
        case 'license.php':
            $_SESSION['setup_license_accept']   = get_boolean_from_request( 'setup_license_accept' );
            $_SESSION['license_submitted']      = true;
            break;
        case 'siteConfig.php':
            $_SESSION['setup_site_url']                     = $_REQUEST['setup_site_url'];
            $_SESSION['parent_setup_site_url']              = $_REQUEST['parent_setup_site_url'];
            $_SESSION['setup_site_portal_username']         = $_REQUEST['setup_site_portal_username'];
            $_SESSION['setup_site_portal_password']         = $_REQUEST['setup_site_portal_password'];
            $_SESSION['setup_site_defaults']                = get_boolean_from_request( 'setup_site_defaults' );
            $_SESSION['setup_site_custom_session_path']     = get_boolean_from_request( 'setup_site_custom_session_path' );
            $_SESSION['setup_site_session_path']            = $_REQUEST['setup_site_session_path'];
            $_SESSION['setup_site_custom_log_dir']          = get_boolean_from_request( 'setup_site_custom_log_dir' );
            $_SESSION['setup_site_log_dir']                 = $_REQUEST['setup_site_log_dir'];
            $_SESSION['setup_site_specify_guid']            = get_boolean_from_request( 'setup_site_specify_guid' );
            $_SESSION['setup_site_guid']                    = $_REQUEST['setup_site_guid'];
            $_SESSION['siteConfig_submitted']               = true;

            $validation_errors = validate_siteConfig();
            if(count($validation_errors) > 0) {
                $next_step--;
            }
            break;
        break;
    }
}


if( $next_step == 9999 ){
    $the_file = 'SilentInstall';
}else if($next_step == 9191){ 
    $the_file = 'oc_convert.php';
}
else {
    $the_file = $workflow[$next_step];
}

switch( $the_file ){
    case 'welcome.php':
        // check to see if installer has been disabled
        if( is_readable('config.php') && (filesize('config.php') > 0) ) {
            include_once('config.php');

// BEGIN SUGARCRM flav=int ONLY 
// Internally, disable the installer_locked flag.
$sugar_config['installer_locked'] = false;
// END SUGARCRM flav=int ONLY 

            if( !isset($sugar_config['installer_locked']) || $sugar_config['installer_locked'] == true ){
                $the_file = 'installDisabled.php';
                //if this is an offline client installation but the conversion did not succeed,
                //then try to convert again
                if(isset($sugar_config['disc_client']) && $sugar_config['disc_client'] == true && isset($sugar_config['oc_converted']) && $sugar_config['oc_converted'] == false){
                    $the_file = 'oc_convert.php'; 
                    $_SESSION['oc_server_url'] = (isset($sugar_config['sync_site_url']) ? $sugar_config['sync_site_url'] : "http://");
                    $_SESSION['oc_username'] = (isset($sugar_config['oc_username']) ? $sugar_config['oc_username'] : "");
                    $_SESSION['oc_password'] = (isset($sugar_config['oc_password']) ? $sugar_config['oc_password'] : ""); 
                    $_SESSION['oc_install'] = true;
                    $_SESSION['is_oc_conversion'] = true;
                }
            }
        }
        break;
    case 'finalInstructions':
    	session_unset();
    	break;        
    case 'SilentInstall':
        pullSilentInstallVarsIntoSession();
        $validation_errors = validate_dbConfig();
        if( count($validation_errors) > 0 ){
            $the_file = 'dbConfig.php';
        }
        else {
            $validation_errors = validate_siteConfig();
            if( count($validation_errors) > 0 ){
                $the_file = 'siteConfig.php';
            }
            else {
                $the_file = 'performSetup.php';
            }
        }
        //since this is a SilentInstall we still need to make sure that 
        //the appropriate files are writable
        // config.php
        make_writable('./config.php');
        
        // custom dir
        make_writable('./custom');
                 
        // modules dir
        recursive_make_writable('./modules');
        
        // data dir
        make_writable('./data');
        make_writable('./data/upload');
        
        // cache dir
        make_writable('./cache/custom_fields');
        make_writable('./cache/dyn_lay');
        make_writable('./cache/images');
        make_writable('./cache/import');
        make_writable('./cache/layout');
        make_writable('./cache/pdf');
        make_writable('./cache/upload');
        make_writable('./cache/xml');
        
        // check whether we're getting this request from a command line tool
        // we want to output brief messages if we're outputting to a command line tool
        $cli_mode = false;
        if( isset($_REQUEST['cli']) && ($_REQUEST['cli'] == 'true') ){
            $_SESSION['cli'] = true;
            // if we have errors, just shoot them back now
            if( count($validation_errors) > 0 ){
                foreach( $validation_errors as $error ){
                    print( $mod_strings['ERR_ERROR_GENERAL']."\n" );
                    print( "    " . $error . "\n" );
                    print( "Exit 1\n" );
                    exit( 1 );
                }
            }
        }
        //BEGIN SUGARCRM flav=pro ONLY 
        $offline_client_install = false;
        if( isset($_REQUEST['oc_install']) && ($_REQUEST['oc_install'] == 'true') ){
            $_SESSION['oc_install'] = true;
        }
        else
        {
        	$_SESSION['oc_install'] = false;
        }
        //END SUGARCRM flav=pro ONLY 
        break;
}

$the_file = clean_string($the_file, 'FILE');

// change to require to get a good file load error message if the file is not available.
require('install/' . $the_file);

//print_debug_comment(); // do this twice?
sugar_cleanup();
?>