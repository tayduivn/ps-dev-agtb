<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * IT Request #11144 - This page is hit by an ajax request to check if a user has new 60 min opportunities
 * assigned to them.
 */


$last_check_date = ( isset($_POST['last_check_date']) && !empty($_POST['last_check_date']) ) ? $_POST['last_check_date'] : "";

$results = 0;
if( !empty($last_check_date) )
	$results = getSixtyMinuteOppsAssignedToCurrentUser($last_check_date);

/**
 * Execute a query to check for new 60 min opps.
 *
 * @param unknown_type $last_check_date
 * @return unknown
 */
function getSixtyMinuteOppsAssignedToCurrentUser($last_check_date)
{
	global $current_user;
	$db = DBManagerFactory::getInstance();
	$last_check_date = $last_check_date / 1000;  //Convert from miliseconds to seconds.
	$now = gmdate("Y-m-d H:i:s", $last_check_date);
	$query = "SELECT count(*) as cnt FROM opportunities o, opportunities_cstm oc WHERE o.id = oc.id_c 
				AND oc.sixtymin_opp_c ='1' AND o.deleted='0' AND o.assigned_user_id = '{$current_user->id}' 
				AND o.date_modified >= '$now'";
		
	$GLOBALS['log']->debug("getSixtyMinuteOppsAssignedToCurrentUser: Query used to find opps:$query");
	
	$rs = $db->query($query);
	if( ($row = $db->fetchByAssoc($rs)) != null )
		$results = $row['cnt'];
		
	return $results;
}

//Send the output.
$json = getJSONobj();
/* There is no $return_value */
#$out = $json->encode($return_value, false);

@ob_end_clean();
ob_start();
echo $results;
ob_end_flush();