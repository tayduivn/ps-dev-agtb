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
    $db =& PearDatabase::getInstance();
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
		}
		

		$schemaFile = $_SESSION['unzip_dir'].'/scripts/'.$schemaFileName;
		_logThis("Running SQL file $schemaFile", $path);
		if(is_file($schemaFile)) {
			//$sql_run_result = _run_sql_file($schemaFile);
			ob_start();
			@parseAndExecuteSqlFile($schemaFile,$queryType,$resumeFromQuery);
			ob_end_clean();
		} else {
			logThis("*** ERROR: Schema change script [{$schemaFile}] could not be found!", $path);
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
	if(strpos($server_software,'Microsoft-IIS') !== false)
	{
		if($sugar_version < '5.5.0'){
		    _logThis("Rebuild web.config.", $path);
		    include_once("modules/Administration/UpgradeIISAccess.php");
		}
	} else {
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

    //BEGIN SUGARCRM flav=pro ONLY 
    ///////////////////////////////////////////////////////////////////////////
    ////    REBUILD TEAMS, REWORK IMPLICIT TEAM RELATIONSHIP
    _logThis("Rebuilding Teams", $path);
    rebuild_teams();
    //END SUGARCRM flav=pro ONLY 

  	global $sugar_version;
    if($sugar_version < '5.5.0') {
        _logThis("Begin Upgrade LDAP authentication", $path);
        upgrade_LDAP();
        _logThis("End Upgrade LDAP authentication", $path);
        
        _logThis("BEGIN CLEAR COMPANY LOGO", $path);
        clearCompanyLogo();
        _logThis("END CLEAR COMPANY LOGO", $path);
        
        _logThis("BEGIN CLEAR IMAGES IN THEME SUGAR", $path);
        clearSugarImages();
        _logThis("END CLEAR IMAGES IN THEME SUGAR", $path);
    } 
    
	if($sugar_version < '5.5.1') {
    	_logThis("Begin Clear all English inline help files", $path);
    	clearHelpFiles();
    	_logThis("End all English inline help files", $path);
    }
    //Rebuild roles
     _logThis("Rebuilding Roles", $path);
	 if($sugar_version < '5.5.0') {
	     add_EZ_PDF();
     }    
     ob_start();
     rebuild_roles();
     ob_end_clean();
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

	if($origVersion < '550') {
        require('include/utils/autoloader.php');
        spl_autoload_register(array('SugarAutoLoader', 'autoload'));
        hide_subpanels_if_tabs_are_hidden();
	}    
	
	if($origVersion < '551') {
		_logThis('Upgrade outbound email setting', $path);
        upgradeOutboundSetting();
	}
	
	if($origVersion < '600' && !isset($_SERVER['HTTP_USER_AGENT'])) {
	   _logThis('Check to hide iFrames and Feeds modules', $path);
	   hide_iframes_and_feeds_modules();
	}	
				
	if($origVersion < '550' && ($sugar_config['dbconfig']['db_type'] == 'mssql')) {
		dropColumnConstraintForMSSQL("outbound_email", "mail_smtpssl");
		$GLOBALS['db']->query("ALTER TABLE outbound_email alter column mail_smtpssl int NULL");
		
		dropColumnConstraintForMSSQL("outbound_email", "mail_sendtype");
		$GLOBALS['db']->query("alter table outbound_email  add default 'smtp' for mail_sendtype;");
	} // if	
	
    //Upgrade multienum data if the version was less than 5.2.0k
    if ($sugar_version < '5.2.0k') {
        _logThis("Upgrading multienum data", $path);
        require_once("$unzip_dir/scripts/upgrade_multienum_data.php");
        upgrade_multienum_data();   
    }
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
        if($origVersion < '600') {
	   _logThis("Start of check to see if Jigsaw connector should be disabled", $path);
	   require_once('include/connectors/utils/ConnectorUtils.php');
	   if(!ConnectorUtils::isSourceEnabled('ext_soap_jigsaw')) {
	   	  _logThis("Jigsaw connector is not being used, remove it", $path);
	   	  ConnectorUtils::uninstallSource('ext_soap_jigsaw');
	   	  if(file_exists('modules/Connectors/connectors/filters/ext/soap/jigsaw')) {
	   	     rmdir_recursive('modules/Connectors/connectors/filters/ext/soap/jigsaw');
	   	  }
	   	  
	   	  if(file_exists('modules/Connectors/connectors/formatters/ext/soap/jigsaw')) {
	   	     rmdir_recursive('modules/Connectors/connectors/formatters/ext/soap/jigsaw');
	   	  }
	   	  
	   	  if(file_exists('modules/Connectors/connectors/sources/ext/soap/jigsaw')) {
	   	     rmdir_recursive('modules/Connectors/connectors/sources/ext/soap/jigsaw');
	   	  }
	   	  
	   	  if(file_exists('custom/modules/Connectors')) {
		   	  ConnectorUtils::uninstallSource('ext_soap_jigsaw');
		   	  if(file_exists('custom/modules/Connectors/metadata/connectors.php')) {
			   	  require('custom/modules/Connectors/metadata/connectors.php');
			   	  if(is_array($connectors) && isset($connectors['ext_soap_jigsaw'])) {
				   	  unset($connectors['ext_soap_jigsaw']);
				   	  if(!write_array_to_file('connectors', $connectors, 'custom/modules/Connectors/metadata/connectors.php')) {
			   			_logThis("Could not remove Jigsaw connector from custom/modules/Connectors/metadata/connectors.php", $path);
					  }	else {
					  	_logThis("Removed Jigsaw connector from custom/modules/Connectors/metadata/connectors.php", $path);
					  }
			   	  } 	  
		   	  }
	   	  }
	   	  
	   } else {
	   	  _logThis("Jigsaw connector is being used, do not remove it", $path);
	   }
	   _logThis("End of check to see if Jigsaw connector should be disabled", $path);
	}		
        //END SUGARCRM flav=pro ONLY

	
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

function hide_subpanels_if_tabs_are_hidden(){
	global $path;	
	require_once('modules/MySettings/TabController.php');
    require_once ('include/SubPanel/SubPanelDefinitions.php') ;
        
	//grab the existing system tabs
	$newTB = new TabController();
	$tabs = $newTB->get_tabs_system();

	//set the hidden tabs key to lowercase
	$hidpanels_arr = array_change_key_case($tabs[1]);
	_logThis('panels to hide because tabs are hidden: '.var_export($hidpanels_arr,true), $path);
		
    //make subpanels hidden if tab is hidden
	SubPanelDefinitions::set_hidden_subpanels($hidpanels_arr);
	_logThis('panels were hidden ', $path);	
}

/**
 * hide_iframes_and_feeds_modules
 * This method determines whether or not to hide the iFrames and Feeds module
 * for an upgrade to 551
 */
function hide_iframes_and_feeds_modules() {
	global $path;
	
    _logThis('Beginning hide_iframes_and_feeds_modules', $path);
	$query = "SELECT id, contents, assigned_user_id FROM user_preferences WHERE deleted = 0 AND category = 'Home'";
	$result = $GLOBALS['db']->query($query, true, "Unable to update iFrames and Feeds dashlets!");
	while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
		$content = unserialize(base64_decode($row['contents']));
		$assigned_user_id = $row['assigned_user_id'];
		$record_id = $row['id'];
		$current_user = new User();
        $current_user->retrieve($row['assigned_user_id']);
        
		if(!empty($content['dashlets']) && !empty($content['pages'])){
			$originalDashlets = $content['dashlets'];
			$originalPages = $content['pages'];
			
			//Determine if the original perference has already had the two dashlets or not
			foreach($originalDashlets as $key=>$ds){
				//BEGIN SUGARCRM flav=com ONLY 
				if(!empty($ds['options']['title']) && $ds['options']['title'] == 'LBL_DASHLET_DISCOVER_SUGAR_PRO'){
				   $originalDashlets[$key]['module'] = 'Home';
				}
				//END SUGARCRM flav=com ONLY 
				if(!empty($ds['options']['title']) && $ds['options']['title'] == 'LBL_DASHLET_SUGAR_NEWS'){
				   $originalDashlets[$key]['module'] = 'Home';
				}
			}
		}
		$current_user->setPreference('dashlets', $originalDashlets, 0, 'Home');
		$current_user->setPreference('pages', $originalPages, 0, 'Home');	
	} //while	
	
	$remove_iframes = false;
	$remove_feeds = false;
	
	//Check if we should remove iframes.  If the table does not exist or the directory
	//does not exist then we set remove_iframes to true
	if(!$GLOBALS['db']->tableExists('iframes') || !file_exists('modules/iFrames')) {
		$remove_iframes = true;
	} else {
		$result = $GLOBALS['db']->query('SELECT count(id) as total from iframes');
		if(!empty($result)) {
			$row = $GLOBALS['db']->fetchByAssoc($result);
			if($row['total'] == 0) {
			   $remove_iframes = true;
			}
		}
	}
	
	//Check if we should remove Feeds.  We check if the tab is hidden
	require_once("modules/MySettings/TabController.php");
	$controller = new TabController();
	$tabs = $controller->get_tabs_system();
	
	//If the feeds table does not exists or if the directory does not exist or if it is hidden in 
	//system tabs then set remove_feeds to true
	if(!$GLOBALS['db']->tableExists('feeds') || !file_exists('modules/Feeds') || (isset($tabs) && isset($tabs[1]) && isset($tabs[1]['Feeds']))) {
	   $remove_feeds = true;
	}
	
	if($remove_feeds) {
	   //Remove the modules/Feeds files
	   if(is_dir('modules/Feeds')) 
	   {
	      _logThis('Removing the Feeds files', $path);
	      rmdir_recursive('modules/Feeds');
	   }
		
	   if(file_exists('custom/Extension/application/Ext/Include/Feeds.php'))
	   {
	      _logThis('Removing custom/Extension/application/Ext/Include/Feeds.php ', $path);
	      unlink('custom/Extension/application/Ext/Include/Feeds.php');	   	
	   }
	   
	   //Drop the table
	   if($GLOBALS['db']->tableExists('feeds')) 
	   {
		   _logThis('Removing the Feeds table', $path);
		   $GLOBALS['db']->dropTableName('feeds');
	   }
	} else {
	   if(file_exists('modules/Feeds') && $GLOBALS['db']->tableExists('feeds')) {
		   _logThis('Writing Feed.php module to custom/Extension/application/Ext/Include', $path);
		   write_to_modules_ext_php('Feed', 'Feeds', 'modules/Feeds/Feed.php', true);
	   }
	}
	
	if($remove_iframes) {
		//Remove the module/iFrames files
		if(is_dir('modules/iFrames')) 
		{
		   _logThis('Removing the iFrames files', $path);
		   rmdir_recursive('modules/iFrames');
		}
		
		if(file_exists('custom/Extension/application/Ext/Include/iFrames.php'))
	    {
	       _logThis('Removing custom/Extension/application/Ext/Include/iFrames.php ', $path);
	       unlink('custom/Extension/application/Ext/Include/iFrames.php');	   	
	    }		
		
		//Drop the table
		if($GLOBALS['db']->tableExists('iframes')) 
		{
		   _logThis('Removing the iframes table', $path);
		   $GLOBALS['db']->dropTableName('iframes');
		}

	} else {
	   if(file_exists('modules/iFrames') && $GLOBALS['db']->tableExists('iframes')) {
		  _logThis('Writing iFrame.php module to custom/Extension/application/Ext/Include', $path);
		  write_to_modules_ext_php('iFrame', 'iFrames', 'modules/iFrames/iFrame.php', true);
	   }
	}	
	
	 _logThis('Finshed with hide_iframes_and_feeds_modules', $path);
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