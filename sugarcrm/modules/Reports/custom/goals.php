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
global $current_user;
$data = array();
$sc = Phaser::singleton();
 $dbgoal = $sc->getInstanceProperty('annual_revenue_goal');
if (!is_null($dbgoal) && is_int(intval($dbgoal))) {
    $goal=intval($dbgoal);
}else{
    $goal = 1000000;
}
$dayOfTheYear=date('z',strtotime('today'));
$goalsper = array(
        'Week'=>$goal/52,
        'Month'=>$goal/12,
        'Year'=>$goal,
        'YTD'=>$goal*($dayOfTheYear/365)
    );
$today = getdate(time());

$firstDayThisMonth=date('Y-m-d',strtotime('first day of this month'));
$lastDayThisMonth=date('Y-m-d',strtotime('last day of this month'));


$firstDayThisWeek=date('Y-m-d',strtotime('last monday'));
$lastDayThisWeek=date('Y-m-d',strtotime('next sunday'));

$data = array();
$queries = array(
        'Week'=>'SELECT sum(amount) val FROM opportunities where sales_stage = \'Closed Won\' and deleted=0 and date_closed >=\''.$firstDayThisWeek.'\' and date_closed <=\''.$lastDayThisWeek.'\'',
        'Month'=>'SELECT sum(amount) val FROM opportunities where sales_stage = \'Closed Won\' and deleted=0 and date_closed >=\''.$firstDayThisMonth.'\' and date_closed <=\''.$lastDayThisMonth.'\'',
        'Year'=>'SELECT sum(amount) val FROM opportunities where sales_stage = \'Closed Won\' and deleted=0 and date_closed >=\''.$today['year'].'-01-01\' and date_closed <=\''.$today['year'].'-12-31\'',
        'YTD'=>'SELECT sum(amount) val FROM opportunities where sales_stage = \'Closed Won\' and deleted=0 and date_closed >=\''.$today['year'].'-01-01\' and date_closed <=\''.$today['year'].'-12-31\'',
);

$styles = array(
);

$results = array();
    foreach ($queries as $queryName => $query) {
        $result = $GLOBALS['db']->query($query);
        var_dump($query);
        while($row = $GLOBALS['db']->fetchByAssoc($result)){
            if(isset($row['val'])) {
                var_dump($row);
                $results[$queryName]['committed'] = floatval($row['val']);
                $results[$queryName]['goal'] = $goalsper[$queryName];
               // $results[$queryName]['percent'] = floatval($row['val'])/$goalsper[$queryName];
            } else {
                $results[$queryName]['committed'] = floatval(0);
                $results[$queryName]['goal'] = $goalsper[$queryName];
            }
        }
    }
$data = $results;
//var_dump($data);
//die("asdf");