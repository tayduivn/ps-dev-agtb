<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * This library is to be used in a clustered DCE environment and will be called from
 * cron_manager.php and cron_wrapper.php. 
 * cron_manager.php will use this file to obtain instances to run cron on
 * cron_wrapper.php will use this file to update the DCE table and release the lock on a particular instance.
 */
//DCE ONLY
//FOR TESTING PURPOSES ONLY
//require_once('include/entryPoint.php');
  require_once('db.php');
  require_once('client_utils.php');
  require_once('dce_config.php');
//FOR TESTING PURPOSES ONLY
define('DCE_LOCK_TABLE', 'dcecronschedules');
define('MAX_CRONS_TO_RUN', 10);
define('EXECUTION_INTERVAL_MINUTES', 10);
define('MAX_LOCK_MINUTES', 10);

/**
 * Query the DCE database and obtain a set of instances to run cron.php on.  While doing this, the sql
 * will be required to lock the record in order to denote that cron.php is currently in process
 * for that instance.
 * @param server_name - the ip address of the server running this job.
 * @return jobsToRun - the instance ids of the cron jobs to run.
 */
function lockJobs($server_name){
	$db = init_db();
	//query db server where is_locked = 0 or is_locked = 1 lock_date <= 1 hour.
	//loop through each return item and attempt to update this item with is_locked and lock_date = now.
	//check the number of items affected and if we still have 1, then include this instance, if not
	//then do not include this instance.
	$select_query =" SELECT ".DCE_LOCK_TABLE.".*, dceinstances.instance_path";
	$select_query.=" FROM ".DCE_LOCK_TABLE;
	//add join to dceinstances to check deleted and instance_path
	$select_query.=" INNER JOIN dceinstances ON dceinstances.id = ".DCE_LOCK_TABLE.".instance_id";
	$select_query.=" WHERE (next_execution_time <= '". gmdate('Y-m-d H:i:s') ."' OR next_execution_time IS NULL)";
	$select_query.=" AND (is_locked ='0' OR ( is_locked ='1' AND lock_date <= '" .gmdate('Y-m-d H:i:s', strtotime("-".MAX_LOCK_MINUTES." minutes"))."'))"; 
	$select_query.=" AND dceinstances.deleted = 0 AND dceinstances.status = 'live'";
	$select_query.=" ORDER BY next_execution_time";
//echo $select_query;
	$result = $db->query($select_query);
	$jobsToRun = array();
	//echo var_export($db->fetch_array($result), true);
	while(($row = $db->fetch_array($result))!= null){
		
		$lock_query="UPDATE ".DCE_LOCK_TABLE." SET is_locked=1, running_server='".$server_name."', lock_date='". gmdate('Y-m-d H:i:s')."' WHERE id = '${row['id']}'";
		$lock_query.=" AND (is_locked ='0' OR ( is_locked ='1' AND lock_date <= '" .gmdate('Y-m-d H:i:s', strtotime("-".MAX_LOCK_MINUTES." minutes"))."'))"; 

 		//if the query fails to execute.. terminate campaign email process.
 		$lock_result=$db->query($lock_query);
	
		$lock_count=$db->affectedrows();
		
		//do not process the message if unable to acquire lock.
		if ($lock_count!= 1) {
			//$GLOBALS['log']->fatal("Error acquiring lock for the instance " . print_r($row,true));
			continue;  //do not process this row we will examine it after 24 hrs. the email address based dupe check is in place too.
		}//fi
		if (empty($row['instance_id'])) {
			//$GLOBALS['log']->fatal('Skipping entry with empty instance id' . print_r($row,true));
			continue;  //do not process this row .
		}//fi
		
		//add this instance to the jobsToRun
		$jobsToRun[] = $row;
		
	}//elihw
	
	echo 'JOBS TO RUN: '.count($jobsToRun);
	return $jobsToRun;
}

/**
 * Given an instance_name, unlock the record from the DCE table. Unlocking will designate that
 * this instance is again available for cron.php to be run.
 *
 * @param instance_name - the name of the instance to unlock and make available for cron.
 */
function unlockJob($instance_id){
	$db = init_db();
	//set is_locked = 0, lock_date = NULL
	$unlock_query="UPDATE ".DCE_LOCK_TABLE." SET is_locked = 0, lock_date = NULL, running_server = NULL, next_execution_time = '" . gmdate('Y-m-d H:i:s', strtotime("+".EXECUTION_INTERVAL_MINUTES." minutes"))."' WHERE instance_id = '$instance_id'";
	$db->query($unlock_query);  
}

function init_db(){
	global $dce_config;
	$db = new DB();

     //declare information for connection     
    $db->server = $dce_config['dce_dbServer'];
    $db->user = $dce_config['dce_dbUser'];
    $db->password = $dce_config['dce_dbPass'];
    $db->database= $dce_config['dce_dbName'];

    //connect to DCE DB
    $db->connect();
	return $db;
}

function is_windows_os() {
	if(preg_match('#WIN#i', PHP_OS)) {
		return true;
	}
	return false;
}
?>