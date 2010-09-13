<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party. Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited. You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution. See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License. Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
if(!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

require_once('modules/Calendar/DateTimeUtil.php');

global $timedate;

$callBean = new Call();
$callQuery = "SELECT * FROM calls where calls.status != 'Held' and calls.deleted=0";
//$callQuery = "SELECT * FROM calls where calls.name like '1' and calls.deleted=0";

$result = $callBean->db->query($callQuery, true, "");
$row = $callBean->db->fetchByAssoc($result);
while ($row != null) {
	$date_time_start =DateTimeUtil::get_time_start($row['date_start']);
    $date_time_end =DateTimeUtil::get_time_end($date_time_start,$row['duration_hours'], $row['duration_minutes']);
    $date_end = gmdate("Y-m-d", $date_time_end->ts);
    $updateQuery = "UPDATE calls set calls.date_end='{$date_end}' where calls.id='{$row['id']}'";
	$call = new Call();
    $call->db->query($updateQuery);
    $row = $callBean->db->fetchByAssoc($result);
}

$meetingBean = new Meeting();
$meetingQuery = "SELECT * FROM meetings where meetings.status != 'Held' and meetings.deleted=0";
//$meetingQuery = "SELECT * FROM meetings where meetings.name like '1' and meetings.deleted=0";

$result = $meetingBean->db->query($meetingQuery, true, "");
$row = $meetingBean->db->fetchByAssoc($result);
while ($row != null) {
	$date_time_start =DateTimeUtil::get_time_start($row['date_start']);
    $date_time_end =DateTimeUtil::get_time_end($date_time_start,$row['duration_hours'], $row['duration_minutes']);
    $date_end = gmdate("Y-m-d", $date_time_end->ts);
	$updateQuery = "UPDATE meetings set meetings.date_end='{$date_end}' where meetings.id='{$row['id']}'";
	$call = new Call();
    $call->db->query($updateQuery);
    $row = $callBean->db->fetchByAssoc($result);
}
echo $mod_strings['LBL_DIAGNOSTIC_DONE'];

