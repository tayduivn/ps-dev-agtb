<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$month_start = date('Y-m-d',mktime(12,1,1,date('m'),-1,date('Y')));
$month_end = date('Y-m-d',mktime(12,1,1,date('m')+1,1,date('Y')));
$query = "SELECT u.id user_id, CONCAT(u.first_name, ' ', u.last_name)  user_name, SUM(o.amount) amount, count(o.id) sales
FROM opportunities o
LEFT JOIN users u ON o.assigned_user_id = u.id
WHERE o.sales_stage = 'Closed Won'
AND o.date_closed BETWEEN '$month_start' AND '$month_end'
GROUP BY u.id";

$result = $GLOBALS['db']->query($query,true);
$data = array();

while($row = $GLOBALS['db']->fetchByAssoc($result)){
    $data[] = $row;
}
