<?php

///////////////////////////////////////////////////////////////////////////////
////	SCHEMA CHANGE PRIVATE METHODS

function _run_sql_file($filename) {
// BEGIN SUGARCRM flav=pro ONLY 
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
// END SUGARCRM flav=pro ONLY 
	return(true);
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

global $sugar_config;
global $unzip_dir;
global $path;

// BEGIN SUGARCRM flav=pro ONLY 

// UPGRADE SCHEMA
if ($sugar_config['dbconfig']['db_type'] == 'mysql'){
	$GLOBALS['log']->debug("Running knowledgebase_mysql.sql.", $path);
	_run_sql_file("$unzip_dir/post_uninstall/knowledgebase_mysql.sql");
}
elseif ($sugar_config['dbconfig']['db_type'] == 'mssql'){
	$GLOBALS['log']->debug("Running knowledgebase_mssql.sql.", $path);
	_run_sql_file("$unzip_dir/post_uninstall/knowledgebase_mssql.sql");
}
elseif ($sugar_config['dbconfig']['db_type'] == 'oci8'){
	//BEGIN SUGARCRM flav=ent ONLY 
	$GLOBALS['log']->debug("Running knowledgebase_oracle.sql.", $path);
	run_sql_file_for_oracle("$unzip_dir/post_uninstall/knowledgebase_oracle.sql");
	//END SUGARCRM flav=ent ONLY 
}


// END SUGARCRM flav=pro ONLY 

ob_start();

// REBUILD RELATIONSHIPS
$GLOBALS['log']->debug("Rebuild Relationships", $path);
require_once('modules/Administration/RebuildRelationship.php');

ob_end_clean();

?>
