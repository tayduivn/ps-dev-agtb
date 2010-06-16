<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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
// $Id$

function updateVersionsTable(){
	$db = &PearDatabase::getInstance();
	global $current_user;
	global $unzip_dir;
	require( "$unzip_dir/manifest.php" );
	
	$date_modified = gmdate("Y-m-d H:i:s");
	
	$query = "SELECT * FROM versions WHERE name='Project Management Module' OR name='" . $manifest['name'] . "'";
	
	// check to see if row exists
	$result = $db->query($query, true, "Unable to retreive data from versions table");
	$row = $db->fetchByAssoc($result);
	
	if ($row == null){
		$id = create_guid();
		
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
		$query = "UPDATE versions SET deleted='0', " .
									 "date_modified = '" . $date_modified . "', " .
									 "file_version = '" . $manifest['version'] . "', " .
									 "db_version = '" . $manifest['db_version'] . "' " .
				 "WHERE name = '" . $manifest['name'] . "'";
		
		$db->query($query, true, "Unable to update versions table");							
	}
	
	return true;
}

function post_install(){
	updateVersionsTable();
}

?>
