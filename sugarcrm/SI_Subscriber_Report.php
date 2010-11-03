<?php
if (!defined('sugarEntry'))define('sugarEntry',true);

error_reporting(E_ALL);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/entryPoint.php');
#include_once('config.php');
#require_once('include/logging.php');
#require_once('include/database/PearDatabase.php');
#require_once('modules/Users/User.php');
#require_once('include/modules.php');
#require_once('include/utils.php');

/* check for old config format. */
if (empty($sugar_config) && isset($dbconfig['db_host_name'])) {
    make_sugar_config($sugar_config);
}
if (!empty($sugar_config['session_dir'])) {
    session_save_path($sugar_config['session_dir']);
}
session_start();
$user_unique_key = (isset($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : '';
$server_unique_key = (isset($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : '';
if ($user_unique_key != $server_unique_key) {
    session_destroy();
    header("Location: index.php?action=Login&module=Users");
    exit();
}
if (!isset($_SESSION['authenticated_user_id'])) {
    /* TODO change this to a translated string. */
    session_destroy();
    die("An active session is required to export content");
}
$current_user = new User();
$result = $current_user->retrieve($_SESSION['authenticated_user_id']);
if ($result == null) {
    session_destroy();
    die("An active session is required to export content");
}
global $current_language;
/* if the language is not set yet, then set it to the default language. */
if (isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
    $current_language = $_SESSION['authenticated_user_language'];
} else {
    $current_language = $sugar_config['default_language'];
}
/* set module and application string arrays based upon selected language */
$app_strings = return_application_language($current_language);
if (!isset($modListHeader)) {
    if (isset($current_user)) {
        $modListHeader = query_module_access_list($current_user);
    }
}


$from = "
FROM
    `opportunities`
LEFT JOIN
    `opportunities_cstm`
    ON `opportunities`.`id` = `opportunities_cstm`.`id_c`   
INNER JOIN
    `accounts_opportunities`
    ON `accounts_opportunities`.`opportunity_id`=`opportunities`.`id`
    AND `accounts_opportunities`.`deleted` = 0
INNER JOIN
    `accounts` ON `accounts`.`id`=`accounts_opportunities`.`account_id`
    AND `accounts`.`deleted` = 0
";


$where = "
WHERE
    `opportunities`.`deleted` = 0

    AND (`opportunities_cstm`.`users` > 0
         OR `opportunities_cstm`.`Revenue_Type_c` IN ('Additional', 'Renewal'))

    AND (`opportunities`.`sales_stage` = 'Finance Closed'
         OR `opportunities_cstm`.`Revenue_Type_c` = 'Renewal')

    AND `opportunities_cstm`.`opportunity_type` IN (
            'Sugar Enterprise',
            'Sugar Professional',
            'Sugar Enterprise On-Demand',
            'Sugar OnDemand',
            'Sugar Cube',
            'sugar_ent_converge',
            'sugar_pro_converge',
            'Partner Fees',
            'OEM'
        )
    AND `opportunities`.`sales_stage` IN (
            'Finance Closed',
            'Closed Lost'
        )
    AND `opportunities_cstm`.`Revenue_Type_c` IN (
             'New',
             'Additional',
             'Renewal'
         )

    AND (`opportunities_cstm`.`closed_lost_reason_c` NOT LIKE '%duplicate%'
         OR `opportunities_cstm`.`closed_lost_reason_c` = ''
         OR `opportunities_cstm`.`closed_lost_reason_c` IS NULL
        )
";

$months_query = "
SELECT DISTINCT
    DATE_FORMAT(`opportunities`.`date_closed`, '%Y/%m') AS 'closed_month'
$from
$where
ORDER BY
    `opportunities`.`date_closed` ASC

";

$accounts_query = "
SELECT DISTINCT
    `accounts_opportunities`.`account_id` AS 'account_id'
$from
$where
ORDER BY
    `accounts_opportunities`.`account_id` ASC
";

$sugar_query = "
SELECT
    `accounts`.`name` as 'account_name',
    `accounts_opportunities`.`account_id` AS 'account_id',
    DATE_FORMAT(`opportunities`.`date_closed`, '%Y/%m') AS 'closed_month',
    `opportunities`.`name` AS 'opportunity_name',
    `opportunities`.`sales_stage` AS 'sales_stage',
    `opportunities_cstm`.`Revenue_Type_c` AS 'revenue_type',
    `opportunities_cstm`.`users` AS 'seats'
$from
$where
ORDER BY
    `accounts_opportunities`.`account_id` ASC,
    `opportunities`.`date_closed` ASC
";

$accounts = array();

$current_account = '';
$current_date = '';

$account_active_subs = 0;
$account_new_subs = 0;
$account_cancelled_subs = 0;

$months = array();
$account_months = array();

$res = $GLOBALS['db']->query($months_query, true);
while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
    $month = $row['closed_month'];
    $months[$month] = array('Active Subs' => 0,
			    'New Subs' => 0,
			    'Cancelled Subs' => 0,
			    'Finance Closed Additional' => 0,
			    'Finance Closed New' => 0,
			    'Closed Lost Renewal' => 0,
			    'Finance Closed Renewal' => 0
			    );
    $account_months[$month] = array();
}

$accounts = array();
$res = $GLOBALS['db']->query($accounts_query, true);
while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
    $account_id = $row['account_id'];
    $accounts[$account_id] = $account_months;
}

$sums = array();
$res = $GLOBALS['db']->query($sugar_query, true);
while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
    $account_id = $row['account_id'];
    $closed_month = $row['closed_month'];
    $sales_stage = $row['sales_stage'];
    $revenue_type = $row['revenue_type'];
    $label = "$sales_stage $revenue_type";
    if (!isset($sums[$label])) {
	$sums[$label] = 0;
    }
    $sums[$label] += $row['seats'];
    $accounts[$account_id][$closed_month][] = $row;
}

$labels = array('Active Subs' => true,
		'New Subs' => true,
		'Cancelled Subs' => true,
		'Finance Closed Additional' => true,
		'Finance Closed New' => true,
		'Closed Lost Renewal' => true,
		'Finance Closed Renewal' => true);

foreach ($accounts as $account_id => $account_months) {
    $previous_month = '';
    $current_month = '';
    
    $account_active_subs = 0;
    
    foreach ($account_months as $current_month => $month_data) {
	
	$months[$current_month]['Active Subs'] += $account_active_subs;
	
	$account_new_subs = 0;
	$account_cancelled_subs = 0;
	
	foreach ($month_data as $row) {
	    
	    $account_id = $row['account_id'];
	    $closed_month = $row['closed_month'];
	    $sales_stage = $row['sales_stage'];
	    $revenue_type = $row['revenue_type'];
	    $seats = $row['seats'];
	    $adding = 0;
	    $cancelling = 0;
    
   
	    /*
	     * +----------------+--------------+
	     * | sales_stage    | revenue_type |
	     * +----------------+--------------+
	     * | Finance Closed | Additional   |
	     * | Finance Closed | New          |
	     * | Closed Lost    | Renewal      |
	     * | Finance Closed | Renewal      |
	     * +----------------+--------------+
	     */
	    
	    /*
	     * New Subs = Revenue Type: New and Additional + Revenue Type Renewal if
	     * Renewal sub is higher than previous opp. For specific month.
	     */
	    /*
	     * Cancelled Subs = Sales Stage Lost (excluding Closed Lost Reason: Channel
	     * Duplicates and Duplicates) + Revenue Type: Renewal if Renewal sub is lown
	     * than previous opp
	     */
	    /*
	     * Active Subs = Ending Month Subs
	     */
	    $label = "$sales_stage $revenue_type";
	    $labels[$label] = true;
	    $months[$current_month][$label] += $seats;
	    if ('New' == $revenue_type) {
		if ($seats > $account_active_subs) {
		    $adding = ($seats - $account_active_subs);
		    $account_new_subs += ($seats - $account_active_subs);
		} elseif ($seats < $account_active_subs) {
		    $account_cancelled_subs += ($account_active_subs - $seats);
		    $cancelling = ($account_active_subs - $seats);
		}
		$account_active_subs = $seats;
	    } elseif ('Additional' == $revenue_type) {
		$account_new_subs += $seats;
		$account_active_subs += $seats;
		$adding = $seats;
	    } elseif ('Renewal' == $revenue_type) {
		if ('Closed Lost' == $sales_stage) {
		    $cancelling = $account_active_subs;
		    $account_cancelled_subs += $account_active_subs;
		    $account_active_subs = 0;
		} else {
		    if ($seats > $account_active_subs) {
			$adding = ($seats - $account_active_subs);
			$account_new_subs += ($seats - $account_active_subs);
		    } elseif ($seats < $account_active_subs) {
			$cancelling = ($account_active_subs - $seats);
			$account_cancelled_subs += ($account_active_subs - $seats);
		    }
		    $account_active_subs = $seats;
		}
	    }
	    $months[$current_month]['Active Subs'] += $adding;
	    $months[$current_month]['Active Subs'] -= $cancelling;
	    
	    $months[$current_month]['New Subs'] += $adding;
	    $months[$current_month]['Cancelled Subs'] += $cancelling;
	    
	}
    }
}
ksort($months);
function csv_field($string) {
    return '"' . str_replace('"', '""', $string) . '"';
}
ob_start();
$all_labels = array_merge(array('Month'), array_keys($labels));
/*
$max_length = max(array_map('strlen', array_keys($labels)));
foreach (range(0, $max_length) as $i) {
    foreach ($all_labels as $label) {
	echo str_repeat(' ', 7);
	if (strlen($label) > $i) {
	    echo $label[$i];
	} else {
	    echo ' ';
	}
    }
    echo "\n";
}
*/
echo join(',', array_map('csv_field', $all_labels));
echo "\n";
foreach ($months as $month => $data) {
    #echo ' ';
    echo $month;
    foreach (array_keys($labels) as $label) {
	echo ',';
	echo csv_field((isset($data[$label]) ? $data[$label] : 0));
	#echo str_pad(isset($data[$label]) ? $data[$label] : 0, 8, ' ', STR_PAD_LEFT);
    }
    echo "\n";
}
$content = ob_get_clean();



header("Pragma: cache");
header("Content-Disposition: inline; filename=Subscriber_Report.csv");
header("Content-Type: text/csv; charset=UTF-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header("Cache-Control: post-check=0, pre-check=0", false );
header("Content-Length: ".strlen($content));
print $content;
exit;
?>






