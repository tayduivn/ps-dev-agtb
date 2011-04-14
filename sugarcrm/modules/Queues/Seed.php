<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
include_once('modules/Queues/Queue.php');


$focus = new Queue();

$focus->db->query('DELETE FROM queues WHERE queue_type != "Mailbox"');
$focus->db->query('TRUNCATE queues_queue');

$db =& $focus->db;

///////////////////////////////////////////////////////////////////////////////
////    CREATE USER QUEUES
///////////////////////////////////////////////////////////////////////////////
// create global queue
$qGlobal = "INSERT INTO queues 
			(id, deleted, date_entered, date_modified, modified_user_id, 
			created_by, name, status, owner_id, queue_type, workflows, team_id)
			VALUES ('1', 0, '".gmdate($GLOBALS['timedate']->get_db_date_time_format())."', '".gmdate($GLOBALS['timedate']->get_db_date_time_format())."', '1', '1', 'Global', 'Active', '1', 'Global','roundRobin', '1')";
$rGlobal = $db->query($qGlobal);

$res = $focus->db->query('SELECT id,first_name,last_name FROM users');
while($a = $focus->db->fetchByAssoc($res)) {
	$guid = create_guid();
	$q2 = "INSERT INTO queues 
			(id, deleted, date_entered, date_modified, modified_user_id, 
			created_by, name, status, owner_id, queue_type, workflows, team_id)
			VALUES(
				'".$guid."', 
				0, 
				'".date($GLOBALS['timedate']->get_db_date_time_format())."', 
				'".date($GLOBALS['timedate']->get_db_date_time_format())."', 
				'1', 
				'1', 
				'".trim($a['first_name']." ".$a['last_name'])."\'s queue', 
				'Active', 
				'".$a['id']."',
				'Users',
				'none',
				''
			)";
	$res2 = $focus->db->query($q2);
	$userIds[] = $a['id'];	
}


//BEGIN SUGARCRM flav=pro ONLY
///////////////////////////////////////////////////////////////////////////////
////    CREATE TEAM QUEUES
////	this will mirror the team_memberships table 
///////////////////////////////////////////////////////////////////////////////
$rTeams = $db->query('SELECT id, name FROM teams WHERE id != "1"'); // SELECT id, name FROM teams WHERE id != "1" AND private = 0 
while($aTeams = $db->fetchByAssoc($rTeams)) {
	$guid = create_guid();
	$qT = "INSERT INTO queues 
			(id, deleted, date_entered, date_modified, modified_user_id, 
			created_by, name, status, owner_id, queue_type, workflows, team_id)
			VALUES(
				'".$guid."',
				0,
				'".date($GLOBALS['timedate']->get_db_date_time_format())."', 
				'".date($GLOBALS['timedate']->get_db_date_time_format())."', 
				'1', 
				'1',
				'".$aTeams['name']."',
				'Active', 
			 	'".$aTeams['id']."',
				'Teams',
				'roundRobin',
				'".$aTeams['id']."'
			)";
	$rT = $db->query($qT);
}
//END SUGARCRM flav=pro ONLY

///////////////////////////////////////////////////////////////////////////////
////    GENERATE QUEUES_QUEUE RELATIONSHIPS
///////////////////////////////////////////////////////////////////////////////
//BEGIN SUGARCRM flav=pro ONLY
$res4 = $db->query("SELECT team_id, user_id FROM team_memberships WHERE explicit_assign = '1' AND team_id != '1' ORDER BY team_id");
while($a4 = $db->fetchByAssoc($res4)) {
	$guid = create_guid();
	$p  = $db->query("SELECT id FROM queues WHERE owner_id = '".$a4['team_id']."'");
	$ap = $db->fetchByAssoc($p);
	$c  = $db->query("SELECT id FROM queues WHERE owner_id = '".$a4['user_id']."'");
	$ac = $db->fetchByAssoc($c);

	if($ap['id'] != $ac['id']) {
		$q5 = "INSERT INTO queues_queue
				(id, deleted, date_entered, date_modified, queue_id, parent_id) 
				VALUES(
				'".$guid."',
				0,
				'".date($GLOBALS['timedate']->get_db_date_time_format())."', 
				'".date($GLOBALS['timedate']->get_db_date_time_format())."',
				'".$ac['id']."',
	 			'".$ap['id']."')";
		$r5 = $db->query($q5);
	}
}
//END SUGARCRM flav=pro ONLY


// all queues inherit from Global
$rAllQueues = $db->query("SELECT id FROM queues WHERE id != '1' AND queue_type IN ('Users','Teams')");
while($aAllQueues = $db->fetchByAssoc($rAllQueues)) {
	$guid = create_guid();
	$qQQ = "INSERT INTO queues_queue
			(id, deleted, date_entered, date_modified, queue_id, parent_id) 
			VALUES ('".$guid."', 0, '".date($GLOBALS['timedate']->get_db_date_time_format())."', '".date($GLOBALS['timedate']->get_db_date_time_format())."', '".$aAllQueues['id']."', '1')";
	$rQQ = $db->query($qQQ);
}
// clean up any User/Team queues that dist to global
$rClean = $db->query("DELETE FROM queues_queue WHERE queue_id = '1'"); // we get 1 anomaly with (admin) team.

// DO SOME CLEAN UP
$rClean = $db->query('SELECT id FROM teams WHERE id != "1" AND private = 1'); // delete private queues;
while($a = $db->fetchByAssoc($rClean)) {
	$r2 = $db->query('SELECT id FROM queues WHERE owner_id = "'.$a['id'].'"');
	$a2 = $db->fetchByAssoc($r2);
	$db->query('DELETE FROM queues_queue WHERE queue_id="'.$a2['id'].'" OR parent_id="'.$a2['id'].'"');
	$db->query('DELETE FROM queues WHERE owner_id = "'.$a['id'].'"');
}





header('Location: index.php?module=Queues&action=index');

?>
