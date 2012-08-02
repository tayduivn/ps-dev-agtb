<?php
if(!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');

}
/**
 * This script executes after the files are copied during the install.
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

require_once(clean_path($unzip_dir.'/scripts/upgrade_utils.php'));



// BEGIN SUGARCRM flav=pro ONLY
/**
 * repair the workflow sessions
 */
function do_repair_workflow_conditions() {
	require_once('modules/WorkFlow/WorkFlow.php');
	require_once('modules/WorkFlowTriggerShells/WorkFlowTriggerShell.php');
	require_once('include/workflow/glue.php');
    $db = DBManagerFactory::getInstance();
    // grab all workflows that are time based and have not been deleted
    $query = "SELECT workflow_triggershells.id trigger_id FROM workflow LEFT JOIN workflow_triggershells ON workflow_triggershells.parent_id = workflow.id WHERE workflow.deleted = 0 AND workflow.type = 'Time' AND workflow_triggershells.type = 'compare_any_time'";
    $data = $db->query($query);
	if($db->checkError()){
	    //put in the array to use later on
	    $_SESSION['sqlSkippedQueries'][] = $query;
    }
    while($row = $db->fetchByAssoc($data)) {
			$shell = new WorkFlowTriggerShell();
			$glue_object = new WorkFlowGlue();
			$shell->retrieve($row['trigger_id']);
			$shell->eval = $glue_object->glue_normal_compare_any_time($shell);
			$shell->save();
    }
	//call repair workflow
	$workflow_object = new WorkFlow();
	$workflow_object->repair_workflow();
}
// END SUGARCRM flav=pro ONLY

function add_EZ_PDF() {
	$cust_file =  "<?php\n";
	$cust_file .= '$sugarpdf_default["PDF_CLASS"] = "EZPDF";'."\n";
	$cust_file .= '$sugarpdf_default["PDF_ENABLE_EZPDF"] = "1";'."\n";
	$cust_file .= "?>\n";
	$file = 'custom/include/Sugarpdf/sugarpdf_default.php';
	if(!file_exists('custom/include/Sugarpdf')) {
		mkdir_recursive('custom/include/Sugarpdf'); // make sure the directory exists
	}

	file_put_contents($file,$cust_file);
}


function rebuild_dashlets(){
    if(is_file('cache/dashlets/dashlets.php')) {
        unlink('cache/dashlets/dashlets.php');
    }

    global $sugar_version;
    if($sugar_version < '5.5.0') {
        require_once('include/SugarTheme/SugarTheme.php');
    }

    require_once('include/Dashlets/DashletCacheBuilder.php');

    $dc = new DashletCacheBuilder();
    $dc->buildCache();
}
// BEGIN SUGARCRM flav=pro ONLY
function rebuild_teams(){
	global $sugar_version;
    if($sugar_version < '5.5.0') {
    	require_once('modules/Teams/TeamMembership.php');
    	require_once('modules/Teams/Team.php');
    }
    require_once('modules/Administration/RepairTeams.php');

    process_team_access(false, false,true,'1');
}
// END SUGARCRM flav=pro ONLY
function rebuild_roles(){
  $_REQUEST['upgradeWizard'] = true;
  require_once("data/SugarBean.php");
  global $ACLActions, $beanList, $beanFiles;
  include('modules/ACLActions/actiondefs.php');
  include('include/modules.php');
  global $sugar_version;
  if($sugar_version < '5.5.0') {
  	require_once('include/ListView/ListView.php');
  }
  include("modules/ACL/install_actions.php");
}

function upgrade_LDAP(){
	require_once('modules/Administration/Administration.php');
	$focus = new Administration();
	$focus->retrieveSettings('ldap', true);
	if(isset($focus->settings['ldap_admin_user']) && !empty($focus->settings['ldap_admin_user']))
	{
		$focus->saveSetting('ldap', 'authentication', '1');
	}else if(isset($focus->settings['ldap_admin_user'])) {
		$focus->saveSetting('ldap', 'authentication', '0');
	}
}
function runSqlFiles($origVersion,$destVersion,$queryType,$resumeFromQuery=''){
	global $sugar_config;
	global $unzip_dir;
	global $sugar_config;
	global $sugar_version;
	global $path;
	global $_SESSION;
	$self_dir = "$unzip_dir/scripts";

	// This flag is determined by the preflight check in the installer
	if(!isset($_SESSION['schema_change']) || /* pre-4.5.0 upgrade wizard */
		$_SESSION['schema_change'] == 'sugar') {
		_logThis("Upgrading the database from {$origVersion} to version {$destVersion}", $path);
		$origVersion = substr($origVersion, 0, 2) . 'x';
		$destVersion = substr($destVersion, 0, 2) . 'x';

		$schemaFileName = $origVersion."_to_".$destVersion;

		switch($sugar_config['dbconfig']['db_type']) {
			case 'mysql':
				$schemaFileName = $schemaFileName . '_mysql.sql';
				break;
			case 'mssql':
			    $schemaFileName = $schemaFileName . '_mssql.sql';
				break;
			case 'oci8':
				$schemaFileName = $schemaFileName . '_oracle.sql';
				break;
            case 'ibm_db2':
                $schemaFileName = $schemaFileName . '_ibm_db2.sql';
                break;				
		}


		$schemaFile = $_SESSION['unzip_dir'].'/scripts/'.$schemaFileName;
		_logThis("Running SQL file $schemaFile", $path);
		if(is_file($schemaFile)) {
			//$sql_run_result = _run_sql_file($schemaFile);
			ob_start();
			@parseAndExecuteSqlFile($schemaFile,$queryType,$resumeFromQuery);
			ob_end_clean();
        } else if(strcmp($origVersion, $destVersion) == 0){
            _logThis("*** Skipping schema upgrade for point release.", $path);
        } else {
            _logThis("*** ERROR: Schema change script [{$schemaFile}] could not be found!", $path);
        }

	} else {
		_logThis('*** Skipping Schema Change Scripts - Admin opted to run queries manually and should have done so by now.', $path);
	}
}

function clearSugarImages(){
    $skipFiles = array('ACLRoles.gif','close.gif','delete.gif','delete_inline.gif','plus_inline.gif','sugar-yui-sprites-green.png',
             'sugar-yui-sprites-purple.png','sugar-yui-sprites-red.png','sugar-yui-sprites.png','themePreview.png');
    $themePath = clean_path(getcwd() . '/themes/Sugar/images');
    $allFiles = array();
    $allFiles = findSugarImages($themePath, $allFiles, $skipFiles);

    foreach( $allFiles as $the_file ){
        if( is_file( $the_file ) ){
            unlink( $the_file );
            _logThis("Deleted file: $the_file", $path);
        }
    }
}

function findSugarImages($the_dir, $the_array, $skipFiles){
    if(!is_dir($the_dir)) {
        return $the_array;
    }
    $skipFiles = array_flip($skipFiles);
    $d = dir($the_dir);
    while (false !== ($f = $d->read())) {
        if($f == "." || $f == ".." ){
            continue;
        }
        if( is_file( "$the_dir/$f" ) && !isset($skipFiles[$f]) ){
            array_push( $the_array, "$the_dir/$f" );
        }
    }
    return( $the_array );
}

function findCompanyLogo($the_dir, $the_array){
    if(!is_dir($the_dir)) {
        return $the_array;
    }
    $d = dir($the_dir);
    while (false !== ($f = $d->read())) {
        if($f == "." || $f == ".." || $f == 'default'){
            continue;
        }
        if( is_file( "$the_dir/$f/images/company_logo.png" ) ){
            array_push( $the_array, "$the_dir/$f/images/company_logo.png" );
        }
    }
    return( $the_array );
}

function clearCompanyLogo(){
    $themePath = clean_path(getcwd() . '/themes');
    $allFiles = array();
    $allFiles = findCompanyLogo($themePath,$allFiles);

    foreach( $allFiles as $the_file ){
        if( is_file( $the_file ) ){
            unlink( $the_file );
            _logThis("Deleted file: $the_file", $path);
        }
    }
}



function genericFunctions(){
	$server_software = $_SERVER["SERVER_SOFTWARE"];
	if(strpos($server_software,'Microsoft-IIS') !== true)
	{
		///////////////////////////////////////////////////////////////////////////
        ////    FILESYSTEM SECURITY FIX (Bug 9365)
	    _logThis("Applying .htaccess update security fix.", $path);
        include_once("modules/Administration/UpgradeAccess.php");
	}

	///////////////////////////////////////////////////////////////////////////
    ////    CLEAR SUGARLOGIC CACHE
	_logThis("Rebuilding SugarLogic Cache", $path);
	clear_SugarLogic_cache();

	///////////////////////////////////////////////////////////////////////////
	////	PRO/ENT ONLY FINAL TOUCHES

	//BEGIN SUGARCRM flav=pro ONLY
	///////////////////////////////////////////////////////////////////////////
	////	WORKFLOW REPAIR
	_logThis("Repairing WorkFlows", $path);
	do_repair_workflow_conditions();
	//END SUGARCRM flav=pro ONLY

		///////////////////////////////////////////////////////////////////////////
	////	REBUILD JS LANG
	_logThis("Rebuilding JS Langauages", $path);
	rebuild_js_lang();

	///////////////////////////////////////////////////////////////////////////
	////	REBUILD DASHLETS
	_logThis("Rebuilding Dashlets", $path);
	rebuild_dashlets();
}

function status_post_install_action($action){
	$currProg = post_install_progress();
	$currPostInstallStep = '';
	$postInstallQuery = '';
	if(is_array($currProg) && $currProg != null){
		foreach($currProg as $key=>$val){
			if($key==$action){
				return $val;
			}
		}
	}
	return '';
}



function post_install() {
	global $unzip_dir;
	global $sugar_config;
	global $sugar_version;
	global $path;
	global $_SESSION;
	if(!isset($_SESSION['sqlSkippedQueries'])){
	 	$_SESSION['sqlSkippedQueries'] = array();
	 }
	initialize_session_vars();
	if(!isset($unzip_dir) || $unzip_dir == null){
		$unzip_dir = $_SESSION['unzip_dir'];
	}
	_logThis('Entered post_install function.', $path);
	$self_dir = "$unzip_dir/scripts";

	///////////////////////////////////////////////////////////////////////////
	////	PUT DATABASE UPGRADE SCRIPT HANDLING HERE
	$new_sugar_version = getUpgradeVersion();
	$origVersion = substr(preg_replace("/[^0-9]/", "", $sugar_version),0,3);
	$destVersion = substr(preg_replace("/[^0-9]/", "", $new_sugar_version),0,3);

    $post_action = status_post_install_action('sql_query');
	if($post_action != null){
	   if($post_action != 'done'){
			//continue from where left in previous run
			runSqlFiles($origVersion,$destVersion,'sql_query',$post_action);
		  	$currProg['sql_query'] = 'done';
		  	post_install_progress($currProg,'set');
		}
	 }
	 else{
		//never ran before
		runSqlFiles($origVersion,$destVersion,'sql_query');
	  	$currProg['sql_query'] = 'done';
	  	post_install_progress($currProg,'set');
	  }

	//if upgrading from 50GA we only need to do the version update.
	if ($origVersion>'500') {
		genericFunctions();

		//BEGIN SUGARCRM flav=pro ONLY
		// add User field in Role
		include_once("modules/ACLActions/ACLAction.php");
		ACLAction::addActions('Users', 'module');
		//END SUGARCRM flav=pro ONLY
		upgradeDbAndFileVersion($new_sugar_version);
	}

	//Set the chart engine
	if ($origVersion < '620') {
		_logThis('Set chartEngine in config.php to JS Charts', $path);
		$sugar_config['chartEngine'] = 'Jit';
	}
    // Bug 51075 JennyG - We increased the upload_maxsize in 6.4.	
    if ($origVersion < '642') {
        _logThis('Set upload_maxsize to the new limit that was introduced in 6.4', $path);
        $sugar_config['upload_maxsize'] = 30000000;
    }
	// Bug 40044 JennyG - We removed modules/Administration/SaveTabs.php in 6.1. and we need to remove it
	// for upgraded instances.  We need to go through the controller for the Administration module (action_savetabs).
    if(file_exists('modules/Administration/SaveTabs.php'))
        unlink('modules/Administration/SaveTabs.php');
	// End Bug 40044 //////////////////

    // Bug 40119 - JennyG - The location of this file changed since 60RC.  So we need to remove it for
    // old instances that have this file.
    if(file_exists('include/Expressions/Expression/Enum/IndexValueExpression.php'))
        unlink('include/Expressions/Expression/Enum/IndexValueExpression.php');
    // End Bug 40119///////////////////

    // Bug 40382 - JennyG - This file was removed in 6.0.
    if(file_exists('modules/Leads/ConvertLead.php'))
        unlink('modules/Leads/ConvertLead.php');
    // End Bug 40382///////////////////

    // Bug 40458 - JennyG - This file was removed in 6.1. and we need to remove it for upgraded instances.
    if(file_exists('modules/Reports/add_schedule.php'))
        unlink('modules/Reports/add_schedule.php');
    // End Bug 40458///////////////////

    upgradeGroupInboundEmailAccounts();

	//BEGIN SUGARCRM flav=pro ONLY
	//add language pack config information to config.php
   	if(is_file('install/lang.config.php')){
   	    global $sugar_config;
		_logThis('install/lang.config.php exists lets import the file/array insto sugar_config/config.php', $path);
		require_once('install/lang.config.php');

		foreach($config['languages'] as $k=>$v){
			$sugar_config['languages'][$k] = $v;
		}

		if( !write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ) {
	        _logThis('*** ERROR: could not write language config information to config.php!!', $path);
	    }else{
			_logThis('sugar_config array in config.php has been updated with language config contents', $path);
		}
    }else{
    	_logThis('*** ERROR: install/lang.config.php was not found and writen to config.php!!', $path);
    }
	//END SUGARCRM flav=pro ONLY

    //Remove jssource/src_files sub-directories if they still exist
    $jssource_dirs = array('jssource/src_files/include/javascript/ext-2.0',
    					   'jssource/src_files/include/javascript/ext-1.1.1',
    					   'jssource/src_files/include/javascript/yui'
                          );

    foreach($jssource_dirs as $js_dir)
    {
	    if(file_exists($js_dir))
	    {
	       _logThis("Remove {$js_dir} directory");
	       rmdir_recursive($js_dir);
	       _logThis("Finished removing {$js_dir} directory");
	    }
    }

    // move blowfish dir
    if(file_exists($sugar_config['cache_dir']."blowfish") && !file_exists("custom/blowfish")) {
           _logThis('Renaming cache/blowfish');
            rename($sugar_config['cache_dir']."blowfish", "custom/blowfish");
           _logThis('Renamed cache/blowfish to custom/blowfish');
    }

    if($origVersion < '650') {
       // move uploads dir
       if($sugar_config['upload_dir'] == $sugar_config['cache_dir'].'upload/') {

           $sugar_config['upload_dir'] = 'upload/';

           if(file_exists('upload'))
           {
               _logThis("Renaming existing upload directory to upload_backup");
               if(file_exists($sugar_config['cache_dir'].'upload/upgrades')) {
                   //Somehow the upgrade script has been stop completely, the dump /upload path possibly exists.
                   $ext = '';
                   while(file_exists('upload/upgrades_backup'.$ext)) {
                       $ext = empty($ext) ? 1 : $ext + 1;
                   }
                   rename('upload', 'upload_backup'.$ext);
               } else {
                   rename('upload', 'upload_backup');
               }
           }

           _logThis("Renaming {$sugar_config['cache_dir']}/upload directory to upload");
           rename($sugar_config['cache_dir'].'upload', 'upload');

           if(!file_exists('upload/index.html') && file_exists('upload_backup/index.html'))
           {
              rename('upload_backup/index.html', 'upload/index.html');
           }

           if(!write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ) {
              _logThis('*** ERROR: could not write upload config information to config.php!!', $path);
           }else{
              _logThis('sugar_config array in config.php has been updated with upload config contents', $path);
           }

           mkdir($sugar_config['cache_dir'].'upgrades', 0755, true);
           //Bug#53276: If upgrade patches exists, the move back to the its original path
           if(file_exists('upload/upgrades/temp')) {
               if(file_exists($sugar_config['cache_dir'].'upload/upgrades')) {
                   //Somehow the upgrade script has been stop completely, the daump cache/upload path possibly exists.
                   $ext = '';
                   if(file_exists($sugar_config['cache_dir'].'upload/upgrades')) {
                       while(file_exists($sugar_config['cache_dir'].'upload/upgrades_backup'.$ext)) {
                           $ext = empty($ext) ? 1 : $ext + 1;
                       }
                       rename($sugar_config['cache_dir'].'upload/upgrades', $sugar_config['cache_dir'].'upload/upgrades_backup'.$ext);
                   }
               } else {
                   mkdir($sugar_config['cache_dir'].'upload/upgrades', 0755, true);
               }
               rename('upload/upgrades/temp', $sugar_config['cache_dir'].'upload/upgrades/temp');
           }
       }
    }
}


/**
 * Group Inbound Email accounts should have the allow outbound selection enabled by default.
 *
 */
function upgradeGroupInboundEmailAccounts() {
    global $path;
    _logThis("Begining to upgrade group inbound email accounts", $path);
    $query = "SELECT id, stored_options FROM inbound_email WHERE mailbox_type='pick' AND deleted=0 AND is_personal=0 AND groupfolder_id != ''";
	$result = $GLOBALS['db']->query($query);
	$updateIE = array();
	while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
	   $storedOptionsEncoded = $row['stored_options'];
	   if(empty($storedOptionsEncoded))
	       continue;

	   $storedOptions = unserialize(base64_decode($storedOptionsEncoded));
	   $storedOptions['allow_outbound_group_usage'] = 1;
	   $updateIE[$row['id']] = base64_encode(serialize($storedOptions));
	}
	foreach ($updateIE as $id => $options)
	{
	    _logThis("Upgrading stored options for IE: $id", $path);
	    $updateQuery = "UPDATE inbound_email SET stored_options = '$options' WHERE id = '$id' ";
	    $GLOBALS['db']->query($updateQuery);
	}
	_logThis("Finished upgrade group inbound email accounts", $path);
}

function upgradeOutboundSetting(){
	$query = "select count(*) as count from outbound_email where name='system' and mail_sendtype='sendmail' ";
	$result = $GLOBALS['db']->query($query);
	$row = $GLOBALS['db']->fetchByAssoc($result);

	if($row['count']>0) {
		require_once('modules/Configurator/Configurator.php');
		$configurator = new Configurator();
		$configurator->config['allow_sendmail_outbound'] = true;
		$configurator->handleOverride();
	}
}

/**
 * write_to_modules_ext_php
 * Writes the given module, class and path values to custom/Extensions/application/Include directory
 * for the module
 * @param $class String value of the class name of the module
 * @param $module String value of the name of the module entry
 * @param $path String value of the path of the module class file
 * @param $show Boolean value to determine whether or not entry should be added to moduleList or modInvisList Array
 */
function write_to_modules_ext_php($class, $module, $path, $show=false) {
	include('include/modules.php');
	global $beanList, $beanFiles;
	if(!isset($beanFiles[$class])) {
		$str = "<?php \n //WARNING: The contents of this file are auto-generated\n";

			if(!empty($module) && !empty($class) && !empty($path)){
				$str .= "\$beanList['$module'] = '$class';\n";
				$str .= "\$beanFiles['$class'] = '$path';\n";
				if($show){
					$str .= "\$moduleList[] = '$module';\n";
				}else{
					$str .= "\$modules_exempt_from_availability_check['$module'] = '$module';\n";
					$str .= "\$modInvisList[] = '$module';\n";
				}
			}

		$str.= "\n?>";
		if(!file_exists("custom/Extension/application/Ext/Include")){
			mkdir_recursive("custom/Extension/application/Ext/Include", true);
		}
		$out = sugar_fopen("custom/Extension/application/Ext/Include/{$module}.php", 'w');
		fwrite($out,$str);
		fclose($out);

		require_once('ModuleInstall/ModuleInstaller.php');
  		$moduleInstaller = new ModuleInstaller();
		$moduleInstaller->merge_files('Ext/Include', 'modules.ext.php', '', true);
	}

}
?>