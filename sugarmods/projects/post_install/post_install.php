<?php

///////////////////////////////////////////////////////////////////////////////
////	SCHEMA CHANGE PRIVATE METHODS

function _run_sql_file($filename) {
	//BEGIN SUGARCRM flav=pro ONLY 
	global $path;
	
    if(!is_file($filename)) {
    	$GLOBALS['log']->debug("*** ERROR: Could not find file: {$filename}", $path);
        return(false);
    }

    $fh         = fopen($filename,'r');
    $contents   = fread($fh, filesize($filename));
    fclose($fh);

    $lastsemi   = strrpos($contents, ';') ;
    $contents   = substr($contents, 0, $lastsemi);
    $queries    = split(';', $contents);
    $db         = & PearDatabase::getInstance();

	foreach($queries as $query){
		if(!empty($query)){
    		$GLOBALS['log']->debug("Sending query: ".$query, $path);
			$query_result = $db->query($query.';', true, "An error has occured while performing db query.  See log file for details.<br>");
		}
	}

	return(true);
	//END SUGARCRM flav=pro ONLY 
}

// BEGIN SUGARCRM flav=ent ONLY 
function run_sql_file_for_oracle($filename) {
	if(!is_file($filename)) {
		return(false);
	}

	$fh         = fopen($filename,'r');
	$contents   = fread($fh, filesize($filename));
	fclose($fh);

	$lastsemi   = strrpos($contents, ';') ;
	$contents   = substr($contents, 0, $lastsemi);
	$queries    = split(';', $contents);
	$db         = & PearDatabase::getInstance();

	foreach($queries as $query) {
		if(!empty($query)) {
			$db->query($query, true, "An error has occured while performing db query.  See log file for details.<br>", true);
		}
	}

	return(true);
}
// END SUGARCRM flav=ent ONLY 
////	END SCHEMA CHANGE METHODS
///////////////////////////////////////////////////////////////////////////////

function installProjectsDb(){
	global $unzip_dir;
	$db = &PearDatabase::getInstance();
	require( "$unzip_dir/manifest.php" );
	
	$query = "SELECT * FROM versions WHERE name='Project Management Module' OR name='" . $manifest['name'] . "'";
	
	// check to see if row exists
	$result = $db->query($query, true, "Unable to retreive data from versions table");
	$row = $db->fetchByAssoc($result);
	
	// install projects if never installed before or completely removed
	if ($row == null || $row['deleted'] == 1){
		return true;
	}
	else if ($row['file_version'] == null){
		return false;
	}
	return false;
}

function processProjectDates(){
	//BEGIN SUGARCRM flav=pro ONLY 
	$db = &PearDatabase::getInstance();
	
	$query = "SELECT min(project_task.date_start) AS start_date, max(project_task.date_finish) AS end_date, project.id AS project_id " .
			 "FROM project_task " .
			 "RIGHT OUTER JOIN project ON project_task.project_id = project.id " .
			 "GROUP BY project.id";
			 
	$result = $db->query($query, true, "Unable to find project start and end dates");
	$row = $db->fetchByAssoc($result);
	
	while($row != null){
		if ($row['start_date'] == null || $row['end_date'] == null){
			$to_update_start = date(strtotime(date('Y-m-d')));
			$to_update_end = date(strtotime(date('Y-m-d')));
					
			$update_query = "UPDATE project" .
							" SET estimated_start_date = '" . date('Y-m-d', $to_update_start) . "', estimated_end_date = '" . date('Y-m-d', $to_update_end) . "'" .
							" WHERE id = '" . $row['project_id'] ."'";
		}
		else{				
			$update_query = "UPDATE project" . 
				 			" SET estimated_start_date = '" . $row['start_date'] ."', estimated_end_date = '" . $row['end_date'] . "'" . 
				 			" WHERE id = '" . $row['project_id'] ."'";
		}
			 			
		$db->query($update_query, true, "Unable to update project start and end dates");

		$row = $db->fetchByAssoc($result);											   
	}

	return true;
	//END SUGARCRM flav=pro ONLY 
}

function workdayDifference($start_date, $end_date){
	//BEGIN SUGARCRM flav=pro ONLY 
	$workdays = 0;

	while ($start_date <= $end_date){
		if ( (date('w',$start_date) != 0) && (date('w',$start_date) != 6) ){
			$workdays++;
		}
		$start_date += 86400;
	}
	return $workdays;
	//END SUGARCRM flav=pro ONLY 
}

function updateProjectTaskData(){
	//BEGIN SUGARCRM flav=pro ONLY 
	$db = &PearDatabase::getInstance();
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
	//END SUGARCRM flav=pro ONLY 
}

function updateProjectResources(){
	//BEGIN SUGARCRM flav=pro ONLY 
	global $current_user;
	$db = &PearDatabase::getInstance();
	
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
	//END SUGARCRM flav=pro ONLY 
}


function updateProjectTaskPredecessors(){
	//BEGIN SUGARCRM flav=pro ONLY 
	$db = &PearDatabase::getInstance();
	
	$query = "SELECT P2.project_task_id, P1.id " . 
			 "FROM project_task P1, project_task P2 " . 
			 "WHERE P1.depends_on_id = P2.id AND P1.project_id = P2.project_id";
	
	$result = $db->query($query, true, "Unable to retrieve project_task_id");
	$row = $db->fetchByAssoc($result);
	
	while ($row != null){
		$update_query = "UPDATE project_task " .
						"SET predecessors = '" . $row['project_task_id'] . "'" .
						"WHERE id = '". $row['id'] ."'";
						
		$db->query($update_query, true, "Unable to update predecessors");
		$row = $db->fetchByAssoc($result);
	}
	
	return true;
	//END SUGARCRM flav=pro ONLY 
}

function fixOrderNumbers(){
	//BEGIN SUGARCRM flav=pro ONLY 
	$db = &PearDatabase::getInstance();
	
	$query = "SELECT project_id " .
			 "FROM project_task " .
			 "GROUP BY project_id";
			 
	$result = $db->query($query, true, "Unable to retrieve number of tasks for project_id");
	$row = $db->fetchByAssoc($result);
	
	while ($row != null){
		// eliminate duplicate order_numbers, build project_task_id by ordering and counting up
		$order_num_query = "SELECT order_number, id FROM project_task WHERE project_id = '" . $row['project_id'] . "' AND order_number IS NOT NULL AND deleted=0 ORDER BY order_number";
		
		$order_num_result = $db->query($order_num_query, true, "Unable to retrieve order_number");
		$order_num_row = $db->fetchByAssoc($order_num_result);
		
		$counter = 1;
		
		while ($order_num_row != null){			
			if ($order_num_row['order_number'] != $counter){
				$update_qry = "UPDATE project_task SET project_task_id = " . $counter ." WHERE id = '" . $order_num_row['id'] ."'";
			}
			else{
				$update_qry = "UPDATE project_task SET project_task_id = " . $order_num_row['order_number'] ." WHERE id = '" . $order_num_row['id'] ."'";
			}
			$db->query($update_qry, true, "Update project_task_id");
			
			$order_num_row = $db->fetchByAssoc($order_num_result);
			
			$counter = $counter+1;
		}
		
		// assign project_task_id for unassigned order_numbers
		$null_order_num_query = "SELECT order_number, id FROM project_task WHERE project_id = '" . $row['project_id'] . "' AND order_number IS NULL AND deleted=0 ORDER BY order_number";
		
		$null_order_num_result = $db->query($null_order_num_query, true, "Unable to retrieve order_number");
		$null_order_num_row = $db->fetchByAssoc($null_order_num_result);
		
		while ($null_order_num_row != null){
			$update_qry = "UPDATE project_task SET project_task_id = " . $counter ." WHERE id = '" . $null_order_num_row['id'] ."'";
			$db->query($update_qry, true, "Update project_task_id");
			
			$null_order_num_row = $db->fetchByAssoc($null_order_num_result);
			
			$counter = $counter+1;			
		}	
		
		$row = $db->fetchByAssoc($result);	
	}
	
	return true;	
	//END SUGARCRM flav=pro ONLY 
}

function updateVersionsTable(){
	$db = &PearDatabase::getInstance();
	global $current_user;
	global $unzip_dir;
	require( "$unzip_dir/manifest.php" );
	
	$query = "SELECT * FROM versions WHERE name='Project Management Module' OR name='" . $manifest['name'] . "'";
	
	// check to see if row exists
	$result = $db->query($query, true, "Unable to retreive data from versions table");
	$row = $db->fetchByAssoc($result);
	
	if ($row == null){
		$id = create_guid();
		$date_modified = gmdate("Y-m-d H:i:s");
		
		$query = "INSERT INTO versions(id, date_entered, date_modified, modified_user_id, created_by, name, file_version, db_version) " .
				 "VALUES ('" . $id . "'," .
				 		 "'" . $date_modified . "'," .
						 "'" . $date_modified . "'," .
						 "'" . $current_user->id ."'," .
						 "'" . $current_user->id ."'," .
						 "'" . $manifest['name'] . "'," .
						 "'" . $manifest['version'] . "'," .
						 "'" . $manifest['db_version'] . "')";
		
		$db->query($query, true, "Unable to insert into versions table");
	}
	else{
		$date_modified = gmdate("Y-m-d H:i:s");
		
		$query = "UPDATE versions SET deleted='0', " .
									 "date_modified = '" . $date_modified . "', " .
									 "file_version = '" . $manifest['version'] . "', " .
									 "db_version = '" . $manifest['db_version'] . "' " .
				 "WHERE name = '" . $manifest['name'] . "'";
		
		$db->query($query, true, "Unable to update versions table");							
	}
	
	return true;
}


///////////////////////////////////////////////////////////////////////////////
////	BEGIN POST INSTALL 

global $sugar_config;
global $unzip_dir;
global $path;

// BEGIN SUGARCRM flav=pro ONLY 

if (installProjectsDb()){
	// UPGRADE SCHEMA
	if ($sugar_config['dbconfig']['db_type'] == 'mysql'){
		$GLOBALS['log']->debug("Running projects_mysql.sql.", $path);	
		_run_sql_file("$unzip_dir/post_install/projects_mysql.sql");
	}
	elseif ($sugar_config['dbconfig']['db_type'] == 'mssql'){
		$GLOBALS['log']->debug("Running projects_mssql.sql.", $path);	
		_run_sql_file("$unzip_dir/post_install/projects_mssql.sql");
	}
	elseif ($sugar_config['dbconfig']['db_type'] == 'oci8'){
		//BEGIN SUGARCRM flav=ent ONLY 
		$GLOBALS['log']->debug("Running projects_oracle.sql.", $path);	
		run_sql_file_for_oracle("$unzip_dir/post_install/projects_oracle.sql");
		//END SUGARCRM flav=ent ONLY 
	}
}

// FIX ORDER NUMBERS
$GLOBALS['log']->debug("Fixing Duplicate and Empty Order Numbers", $path);
fixOrderNumbers();

// FIX PROJECT TASK WEEKEND DATES
$GLOBALS['log']->debug("Updating Project Task Weekend Dates and Durations", $path);
updateProjectTaskData();

// DETERMINE PROJECT START AND END DATES
$GLOBALS['log']->debug("Determining Project Start and End Dates", $path);
processProjectDates();

// UPDATE PROJECT RESOURCES
$GLOBALS['log']->debug("Updating Project Resources", $path);
updateProjectResources();

// UPDATE PROJECT TASK PREDECESSORS
$GLOBALS['log']->debug("Updating Project Task Predecessors", $path);
updateProjectTaskPredecessors();

ob_start();
// REBUILD JAVASCRIPT LANGUAGE FILES
$GLOBALS['log']->debug("Rebuild Javascript Language Files", $path);
global $current_user;
require_once('modules/Administration/RebuildJSLang.php');

// REBUILD RELATIONSHIPS
$GLOBALS['log']->debug("Rebuild Relationships", $path);
require_once('modules/Administration/RebuildRelationship.php');

// END SUGARCRM flav=pro ONLY 

$this->install_relationship("$unzip_dir/relationships/users_holidaysMetaData.php");
$this->install_relationship("$unzip_dir/relationships/project_casesMetaData.php");
$this->install_relationship("$unzip_dir/relationships/project_bugsMetaData.php");
// BEGIN SUGARCRM flav=pro ONLY 
$this->install_relationship("$unzip_dir/relationships/project_productsMetaData.php");
// END SUGARCRM flav=pro ONLY 
$this->install_relationship("$unzip_dir/relationships/projects_accountsMetaData.php");
$this->install_relationship("$unzip_dir/relationships/projects_contactsMetaData.php");
$this->install_relationship("$unzip_dir/relationships/projects_opportunitiesMetaData.php");
// BEGIN SUGARCRM flav=pro ONLY 
$this->install_relationship("$unzip_dir/relationships/projects_quotesMetaData.php");
// END SUGARCRM flav=pro ONLY 

ob_end_clean();

$GLOBALS['log']->debug("Update Versions Table", $path);
updateVersionsTable();

////	END POST INSTALL
///////////////////////////////////////////////////////////////////////////////

?>
