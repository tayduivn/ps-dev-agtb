<?php

///////////////////////////////////////////////////////////////////////////////
////	SCHEMA CHANGE PRIVATE METHODS

function _run_sql_file($filename) {	
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
}


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

////	END SCHEMA CHANGE METHODS
///////////////////////////////////////////////////////////////////////////////

function updateVersionsTable(){
	$db = &PearDatabase::getInstance();
	global $current_user;
	global $unzip_dir;
	require( "$unzip_dir/manifest.php" );
	
	$query = "SELECT * FROM versions WHERE name='" . $manifest['name'] . "'";
	
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



// UPGRADE SCHEMA
if ($sugar_config['dbconfig']['db_type'] == 'mysql'){
	$GLOBALS['log']->debug("Running knowledgebase_mysql.sql.", $path);
	_run_sql_file("$unzip_dir/post_install/knowledgebase_mysql.sql");
}
elseif ($sugar_config['dbconfig']['db_type'] == 'mssql'){
	$GLOBALS['log']->debug("Running knowledgebase_mssql.sql.", $path);
	_run_sql_file("$unzip_dir/post_install/knowledgebase_mssql.sql");
}
elseif ($sugar_config['dbconfig']['db_type'] == 'oci8'){
	//BEGIN SUGARCRM flav=ent ONLY 
	$GLOBALS['log']->debug("Running knowledgebase_oracle.sql.", $path);
	run_sql_file_for_oracle("$unzip_dir/post_install/knowledgebase_oracle.sql");
	//END SUGARCRM flav=ent ONLY 
}


ob_start();
// REBUILD JAVASCRIPT LANGUAGE FILES
$GLOBALS['log']->debug("Rebuild Javascript Language Files", $path);
global $current_user;
require_once('modules/Administration/RebuildJSLang.php');

// REBUILD RELATIONSHIPS
$GLOBALS['log']->debug("Rebuild Relationships", $path);
require_once('modules/Administration/RebuildRelationship.php');

/*
$this->install_relationship("$unzip_dir/relationships/kbdocument_casesMetaData.php");
$this->install_relationship("$unzip_dir/relationships/kbdocument_emailsMetaData.php");
$this->install_relationship("$unzip_dir/relationships/kbdocument_views_ratingsMetaData.php");
*/
ob_end_clean();

$GLOBALS['log']->debug("Update Versions Table", $path);
updateVersionsTable();

////	END POST INSTALL
///////////////////////////////////////////////////////////////////////////////

?>