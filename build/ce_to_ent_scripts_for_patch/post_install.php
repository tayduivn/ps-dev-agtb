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
// $Id: post_install.php 28793 2007-10-24 03:17:09Z vineet $

require_once(clean_path($unzip_dir.'/scripts/upgrade_utils.php'));

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

function post_install()
{
	global $unzip_dir;
	global $sugar_config;
	global $sugar_version;
	global $path;
	global $db;
	global $_SESSION;
	if(!isset($_SESSION['sqlSkippedQueries'])){
	 	$_SESSION['sqlSkippedQueries'] = array();
	 }
	initialize_session_vars();

    $unzip_dir	= $_SESSION['unzip_dir'];

	$self_dir = "$unzip_dir/scripts";
	//_logThis('Start Upgrade falvor.', $path);
	$log =& $GLOBALS['log'];
	//echo 'Upgrade flavor begin';
	//upgrade_Flavors5();
	//echo 'Upgrade flavor End';
	//_logThis('End Upgrade falvor.', $path);
	$schemaFile = '';
    if($sugar_config['dbconfig']['db_type'] == 'mysql') {
	   $log->info('Running SQL file 610_ce_to_ent_mysql.sql');
	   $schemaFile = "$self_dir/610_ce_to_ent_mysql.sql";
    } else if ($sugar_config['dbconfig']['db_type'] == 'mssql') {
	   $schemaFile = "$self_dir/610_ce_to_ent_mssql.sql";
	   if(in_array(get_class($db),array('SqlsrvManager','FreeTDSManager')) && file_exists("$self_dir/610_ce_to_ent_mssql_freetds.sql")){
	       $schemaFile = "$self_dir/610_ce_to_ent_mssql_freetds.sql";
	   }	   
	   $log->info("Running SQL file $schemaFile");
    }

    $post_action = status_post_install_action('sql_query');
	if($post_action != null){
	   if($post_action != 'done'){
			//continue from where left in previous run
			@parseAndExecuteSqlFile($schemaFile,'sql_query',$post_action);
		  	$currProg['sql_query'] = 'done';
		  	post_install_progress($currProg,'set');
		}
	 }
	 else{
		//never ran before
		@parseAndExecuteSqlFile($schemaFile,'sql_query');
	  	$currProg['sql_query'] = 'done';
	  	post_install_progress($currProg,'set');
	  }


    if(isset($_SESSION['sugar_version_file']) && !empty($_SESSION['sugar_version_file'])) {
		if(!copy($_SESSION['sugar_version_file'], clean_path(getcwd().'/sugar_version.php'))) {
			$log->info('*** ERROR: sugar_version.php could not be copied to destination! Cannot complete upgrade');
			return false;
		}
		else {
			$log->info('sugar_version.php successfully updated!');
		}
	}
    //set license date.
	$_SESSION['LICENSE_EXPIRES_IN'] = 'valid';
	$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';
	//require_once('modules/Teams/Team.php');
	//require_once('modules/Users/User.php');
	//include('modules/Administration/upgradeTeams.php');
	//include('modules/ACL/install_actions.php');
    
    // set system_system_id
    require_once('modules/Administration/System.php');
    include_once ('include/database/DBManagerFactory.php');
    $system = new System();
    $system->system_key = $sugar_config['unique_key'];
    $system->user_id = 1;
    $system->last_connect_date = date($GLOBALS['timedate']->get_date_time_format(),mktime());
    $system_id = $system->retrieveNextKey(false, true);
    $db = DBManagerFactory::getInstance();
    $db->query( "INSERT INTO config (category, name, value) VALUES ( 'system', 'system_id', '" . $system_id . "')" );
   
	///////////////////////////////////////////////////////////////////////////
	////	FILESYSTEM SECURITY FIX (Bug 9365)
	_logThis("Applying .htaccess update security fix.", $path);
	include_once("modules/Administration/UpgradeAccess.php");

	///////////////////////////////////////////////////////////////////////////
    ////    REBUILD DASHLETS
    _logThis("Rebuilding Dashlets", $path);
    rebuild_dashlets();
    
    _logThis('Set default_theme to Sugar', $path);
    $sugar_config['default_theme'] = 'Sugar';
    
    if( !write_array_to_file( "sugar_config", $sugar_config, "config.php" ) ) {
        logThis('*** ERROR: could not write config.php! - upgrade will fail!', $path);
        $errors[] = 'Could not write config.php!';
    }
    
	_logThis("Remove 'Go To Pro' iframes on HOME page", $path);
	$post_action = status_post_install_action('updateIFramesForHomePage');
	if($post_action == null || $post_action != 'done'){
		//continue from where left in previous run
		update_iframe_dashlets();
		$currProg['updateIFramesForHomePage'] = 'done';
		post_install_progress($currProg,'set');
	}
	
	//Create the default reports. Going from ce to ent
	createDefaultReports();
	
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

    ///////////////////////////////////////////////////////////////////////////
	////	BUILD PORTAL CONFIG
    _logThis("Building portal config", $path);
    require("install/install_utils.php");
    handlePortalConfig();
	
	//Upgrade Projects
	upgradeProjects();
        setConnectorDefaults();
    rebuild_teams();
	rebuild_roles();
}

function rebuild_dashlets(){
    if(is_file('cache/dashlets/dashlets.php')) {
        unlink('cache/dashlets/dashlets.php');
    }
    require_once('include/Dashlets/DashletCacheBuilder.php');

    $dc = new DashletCacheBuilder();
    $dc->buildCache();
}

function rebuild_teams(){
	require_once('modules/Teams/Team.php');
    require_once('modules/Administration/RepairTeams.php');
    process_team_access(false, false,true,'1');
}

function rebuild_roles(){
    global $ACLActions, $beanList, $beanFiles;
    include('modules/ACLActions/actiondefs.php');
    include('include/modules.php'); 
	require_once('modules/ACLFields/ACLField.php');
    include("modules/ACL/install_actions.php");
}

function createDefaultReports(){
    require_once('modules/Reports/SavedReport.php');
	require_once('modules/Reports/SeedReports.php');
    create_default_reports();
}

function upgrade_Flavors5() {
	//echo 'running flavors script ';
	require_once ('modules/Relationships/Relationship.php');
	include_once ('include/database/DBManagerFactory.php');
	global $current_user, $beanFiles,$dictionary;
	set_time_limit(3600);
	$db = DBManagerFactory::getInstance();
	$sql = '';
	VardefManager::clearVardef();
	$execute = false;
	foreach ($beanFiles as $bean => $file) {
		require_once ($file);
		$focus = new $bean ();
		$sql .= $db->repairTable($focus, $execute);
	}
	$olddictionary = $dictionary;
	unset ($dictionary);
	include ('modules/TableDictionary.php');
	foreach ($dictionary as $meta) {
		$tablename = $meta['table'];
		$fielddefs = $meta['fields'];
		$indices = $meta['indices'];
		$sql .= $db->repairTableParams($tablename, $fielddefs, $indices, $execute);
	}

	if (isset ($sql) && !empty ($sql)) {
		$qry_str = "";
		foreach (split("\n", $sql) as $line) {
			if (!empty ($line) && substr($line, -2) != "*/") {
				$line .= ";";
			}
			$qry_str .= $line . "\n";
		 }
	}

	$dictionary = $olddictionary;
	$qry_str = str_replace(
		array(
			"\n",
			'&#039;',
		),
		array(
			'',
			"'",
		),
		preg_replace('#(/\*.+?\*/\n*)#', '', $qry_str)
	);
	foreach (split(";", $qry_str) as $stmt) {
		$stmt = trim($stmt);
		if (!empty ($stmt)) {
			$db->executeQuery($stmt, 'Executing repair query: ');
		}
	}

 //echo $qry_str;
 //echo 'done running flavors script ';
}

function updateProjectResources(){
	global $current_user;
	include_once ('include/database/DBManagerFactory.php');
    $db = DBManagerFactory::getInstance();

	$modified_user_id = $current_user->id;
	$created_by = $current_user->id;

	$query = "SELECT DISTINCT assigned_user_id, project_id from project_task";

	$result = $db->query($query, true, "Unable to retrieve assigned users of tasks");
	$row = $db->fetchByAssoc($result);

	while ($row != null){
		$id = create_guid();
		$date_modified = gmdate("Y-m-d H:i:s");
		$insert_query =	"INSERT INTO project_resources(id, date_modified, modified_user_id, " .
													  "created_by, project_id, resource_id, resource_type) " .
						"VALUES('" . $id . "'," .
							   "'" . $date_modified . "'," .
							   "'" . $modified_user_id . "'," .
							   "'" . $created_by . "'," .
							   "'" . $row['project_id'] . "'," .
							   "'" . $row['assigned_user_id'] . "'," .
							   "'Users')";

		$db->query($insert_query, true, "Unable to update project resources");

		$row = $db->fetchByAssoc($result);
	}

	// update Project Task Resource
	$update_ptr_query = "UPDATE project_task SET resource_id = assigned_user_id";
	$db->query($update_ptr_query, true, "Unable to update project task resources");

	return true;
}

function workdayDifference($start_date, $end_date){
	$workdays = 0;

	while ($start_date <= $end_date){
		if ( (date('w',$start_date) != 0) && (date('w',$start_date) != 6) ){
			$workdays++;
		}
		$start_date += 86400;
	}
	return $workdays;
}

function updateProjectTaskData(){
	include_once ('include/database/DBManagerFactory.php');
    $db = DBManagerFactory::getInstance();
	global $timedate;

    require_once('include/utils/db_utils.php');

	$query = "SELECT date_start, date_finish, id FROM project_task";

	$result = $db->query($query, true, "Unable to retrieve project task dates");
	$row = $db->fetchByAssoc($result);

	while ($row != null){
		if (empty($row['date_start']) && empty($row['date_finish'])){
			$date_start = strtotime(date('Y-m-d'));
			$date_finish = strtotime(date('Y-m-d'));
		}
		else if (!empty($row['date_start']) && empty($row['date_finish'])){
			$date_finish = strtotime(date($row['date_start']));
			$date_start = strtotime(date($row['date_start']));
		}
		else if (empty($row['date_start']) && !empty($row['date_finish'])){
			$date_start = strtotime(date($row['date_finish']));
			$date_finish = strtotime(date($row['date_finish']));
		}
		else{
			$date_start = strtotime(date($row['date_start']));
			$date_finish = strtotime(date($row['date_finish']));
		}

		if ( date('w', $date_start) == 0 ){
			$date_start = $date_start + 86400;
		}
		else if ( date('w', $date_start) == 6 ){
			$date_start = $date_start + 86400*2;
		}

		if ( date('w', $date_finish) == 0 ){
			$date_finish = $date_finish + 86400;
		}
		else if ( date('w', $date_finish) == 6 ){
			$date_finish = $date_finish + 86400*2;
		}

        $to_update_start = date($date_start);
        $to_update_finish = date($date_finish);

        $duration = workdayDifference($date_start, $date_finish);

        $update_query = "UPDATE project_task" .
                        " SET duration = '" . $duration . "'," .
                        " date_start = '" . date('Y-m-d', $to_update_start) . "'," .
                        " date_finish = '" . date('Y-m-d', $to_update_finish) . "'" .
                        " WHERE id = '" . $row['id'] ."'";

        $db->query($update_query, true, "Unable to update duration");

		$row = $db->fetchByAssoc($result);
	}
	return true;
}

function upgradeProjects(){
	updateProjectTaskData();
	updateProjectResources();
}

function setConnectorDefaults(){
    require('modules/Connectors/InstallDefaultConnectors.php');
} 

?>
