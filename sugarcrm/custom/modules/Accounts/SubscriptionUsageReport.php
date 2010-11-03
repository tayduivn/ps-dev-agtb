<?php

if (!defined('sugarEntry')) {
    die("Not a valid entry point");
}
if (!isset($GLOBALS['current_user'])) {
    echo "You are not authorized to access this Report.<br />";
    return;
}
#elseif (!is_admin($GLOBALS['current_user'])
#	  && !$GLOBALS['current_user']->check_role_membership('Lead Funnel Access')
#	  ) {
#    echo "You are not authorized to access this Report.<br />";
#    return;
#}


require_once('include/TimeDate.php');
$timedate = new TimeDate();
require_once('modules/Accounts/Account.php');
require_once('modules/Subscriptions/Subscription.php');
require_once('include/SugarCharts/SugarChart.php');
require_once('include/SugarCharts/SugarChartReports.php');






$cache_file_stub = (!empty($GLOBALS['current_user']->id) ? $GLOBALS['current_user']->id : gmdate('YmdHis'));

$cache_file = 'cache/xml/SubscriptionUsage_chart_data' . $cache_file_stub . '.xml';

$user_date_format = '(yyyy-mm-dd)';
$calendar_date_format = '%Y-%m-%d';

if (!isset($_REQUEST['record'])
    || !preg_match('/^[-A-Za-z0-9]+$/', $_REQUEST['record'])) {
    sugar_die('You must supply a valid account id');
} else {
    $account_id = $_REQUEST['record'];
}

$account = new Account();
if (!$account->retrieve($account_id)) {
    sugar_die('You must supply a valid account id');
}

$query = "
SELECT
    EXTRACT(YEAR_MONTH FROM `sugar_updates`.`time_stamp`) AS `year_month`,
    `subscriptions`.`account_id`,
    `subscriptions`.`id` AS `subscription_primary`,
    `subscriptions`.`subscription_id` AS `subscription_id`,
    `sugar_installations`.`id`,
    MAX(`sugar_updates`.`users`) AS `max_users`,
    CEILING(AVG(`sugar_updates`.`users`)) AS `mean_users`,
    MAX(`subscriptions_distgroups`.`quantity`) AS `allowed_users`
FROM
    `sugar_installations`
INNER JOIN
    `subscriptions` ON `subscriptions`.`subscription_id` = `sugar_installations`.`license_key`
INNER JOIN
    `sugar_updates` ON `sugar_installations`.`id` = `sugar_updates`.`installation_id`
INNER JOIN
    `subscriptions_distgroups` ON `subscriptions`.`id` = `subscriptions_distgroups`.`subscription_id`
WHERE
    DATE_SUB(CURDATE(), INTERVAL 1 YEAR) <= `sugar_updates`.`time_stamp`
    AND `subscriptions`.`account_id` = '$account_id'
    AND `sugar_updates`.`users` > 0
GROUP BY
    `year_month`,
    `subscriptions`.`id`,
    `subscriptions`.`subscription_id`,
    `subscriptions`.`account_id`
ORDER BY
    `year_month` ASC,
    `subscriptions`.`account_id` ASC,
    `subscriptions`.`id` ASC,
    `sugar_installations`.`id` ASC

";

$res = $GLOBALS['db']->query($query);
if (!$res) {
    echo "Error in getSubscriptionUsage for $account_id<br />";
    echo '<pre>';
    echo $query;
    echo '</pre>';
    echo '<br />';
    echo mysql_errno($GLOBALS['db']->database) . " : " . mysql_error($GLOBALS['db']->database). "<br />";
    return null;
}
$year_months = array();
$year_month_data = array();
$year_month_totals = array();
$subscriptions = array();

$rows = array();
while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
    $rows[] = $row;
    $subscriptions[$row['subscription_primary']] = $row['subscription_id'];
    $year_months[] = $row['year_month'];
}
if (!$rows) {
    echo '<p>No data for this Account.</p>';
    echo '<p>Go back to: ';
    echo '<a href="https://sugarinternal.sugarondemand.com/index.php?module=Accounts&action=DetailView&record=';
    echo $account->id;
    echo '">';
    echo $account->name;
    echo '</a>';
    echo '</p>';
    return;
}

/*
 * Make sure that we have every month from the start to end.  This is possibly
 * horrible, but the premise is to parse out the start and end points and then
 * use PHP to iterate through each month without having to compare to 12 the
 * whole way through.
 */
$min_year_month = min($year_months);
$max_year_month = max($year_months);
$min_year = intval(substr($min_year_month, 0, 4));
$min_month = intval(substr($min_year_month, 4, 2));
$max_year = intval(substr($max_year_month, 0, 4));
$max_month = intval(substr($max_year_month, 4, 2));
$end_time = mktime(0, 0, 0, $max_month, 1, $max_year);
$i_month = $min_month;

while ($end_time >= ($month_time = mktime(0, 0, 0, $i_month++, 1, $min_year))) {
    $year_month = date('Ym', $month_time);
    $year_months[] = $year_month;
    
    $year_month_data[$year_month] = array();
    $year_month_totals[$year_month] = array('max_users' => 0,
					    'mean_users' => 0,
					    'allowed_users' => 0);
    
    foreach ($subscriptions as $subscription_primary => $subscription_id) {
	$year_month_data[$year_month][$subscription_primary] = array('max_users' => 0,
								     'mean_users' => 0,
								     'allowed_users' => 0);
    }
}
$year_months = array_unique($year_months);
sort($year_months);

foreach ($rows as $row) {
    $year_month = $row['year_month'];

    $subscription_primary = $row['subscription_primary'];
    $year_month_data[$year_month][$subscription_primary]['max_users'] = intval($row['max_users']);
    $year_month_data[$year_month][$subscription_primary]['mean_users'] = intval($row['mean_users']);
    $year_month_data[$year_month][$subscription_primary]['allowed_users'] = intval($row['allowed_users']);
    $year_month_totals[$year_month]['max_users'] += intval($row['max_users']);
    $year_month_totals[$year_month]['mean_users'] += intval($row['mean_users']);
    $year_month_totals[$year_month]['allowed_users'] += intval($row['allowed_users']);
}
$max_users = 0;
$min_users = null;
foreach ($year_month_totals as $year_month => $data) {
    if ($max_users < $data['max_users']) {
	$max_users = $data['max_users'];
    }
    if ($max_users < $data['allowed_users']) {
	$max_users = $data['allowed_users'];
    }
    if (null === $min_users || $min_users > $data['max_users']) {
	$min_users = $data['max_users'];
    }
}
if ($min_users < 100) {
    $min_users = 0;
}

$title = 'Account Subscription Usage Report';
$chart_type = 'stacked group by chart';
/* Maybe this? */
$chart_type = 'line chart';
echo '<h2>';
echo $title;
echo '</h2>';
echo '<h3>Subscription Data</h3>';

echo '<p>';
echo '<a href="https://sugarinternal.sugarondemand.com/index.php?module=Accounts&action=DetailView&record=';
echo $account->id;
echo '">';
echo $account->name;
echo '</a>';
echo '</p>';

echo '<br />';
echo '<script type="text/javascript" src="include/javascript/sugar_grp_overlib.js" />';
echo '<br />';
if (false) {
    echo '<table>';
    foreach ($year_months as $year_month) {
	echo '<tr>';
	echo '<td class="tabDetailViewDF">';
	echo $year_month;
	echo '</td>';
	echo '<td class="tabDetailViewDF">';
	echo $year_month_totals[$year_month]['max_users'];
	echo '</td>';
	echo '<td class="tabDetailViewDF">';
	echo $year_month_totals[$year_month]['allowed_users'];
	echo '</td>';
	echo '</tr>';
    }
    echo '</table>';
    
    echo '<br />';
    echo '<br />';
}

ob_start();
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<sugarcharts version="1.0">';

echo "\n";
echo '<properties>';
echo "\n";
echo '<gauge_target_list>Array</gauge_target_list>';
echo "\n";
echo '<title>'. $title. '</title>';
echo "\n";
echo '<subtitle></subtitle>';
echo "\n";
echo '<type>'. $chart_type. '</type>';
echo "\n";
echo '<legend>on</legend>';
echo "\n";
echo '<labels>value</labels>';
echo "\n";
echo '<print>on</print>';
echo "\n";
echo '</properties>';
echo "\n";
echo '<data>';

$first_value = 19831983;
$last_value = 0;
$prior_allowed = 0;
foreach ($year_months as $year_month) {
    echo "\n";
    echo '<group>';
    echo "\n";
    echo '<title>' . $year_month . '</title>';
    echo "\n";
    echo '<value>' . $year_month_totals[$year_month]['max_users'] . '</value>';
    echo "\n";
    echo '<label>' . $year_month_totals[$year_month]['max_users'] . '</label>';
    echo "\n";
    echo '<subgroups>';

    echo '<group>';
    echo '<title>';
    echo 'Subscriptions';
    echo '</title>';
    echo '<value>';
    if (!$year_month_totals[$year_month]['allowed_users']
	&& $prior_allowed
	&& !$year_month_totals[$year_month]['max_users']) {
	$allowed = $prior_allowed;
	$allowed_label = $allowed . ' (Assumed)';
    } else {
	$allowed = $year_month_totals[$year_month]['allowed_users'];
	$allowed_label = $allowed;
	$prior_allowed = $allowed;
    }
    echo $allowed;
    echo '</value>';
    echo '<label>';
    echo $allowed_label;
    echo '</label>';
    echo '</group>';


    if (1 < count($year_month_data[$year_month])) {
	echo '<group>';
	echo '<title>';
	echo 'Total Users';
	echo '</title>';
	echo '<value>';
	echo $year_month_totals[$year_month]['max_users'];
	echo '</value>';
	echo '<label>';
	echo $year_month_totals[$year_month]['max_users'];
	echo '</label>';
	echo '</group>';
    }
    
    if (20 > count($year_month_data[$year_month])) {
	foreach ($year_month_data[$year_month] as $subscription_primary => $data) {
	    echo '<group>';
	    echo '<title>';
	    if (isset($subscriptions[$subscription_primary])) {
		echo $subscriptions[$subscription_primary];
	    } else {
		echo $subscription_primary;
	    }
	    echo '</title>';
	    echo '<value>';
	    echo $data['max_users'];
	    echo '</value>';
	    echo '<label>';
	    echo $data['max_users'];
	    echo '</label>';
	    if (isset($subscriptions[$subscription_primary])) {
		echo '<link>';
		echo 'https://sugarinternal.sugarondemand.com/index.php?module=Subscriptions&action=DetailView&record=';
		echo $subscription_primary;
		echo '</link>';
	    }
	    echo '</group>';
	}
    }
    echo "\n";
    echo '</subgroups>';
    echo "\n";
    echo '</group>';
}

echo "\n";
echo '</data>';

echo "\n";
echo '<yAxis>';

echo "\n";
echo '<yMin>';
echo $min_users;
echo '</yMin>';

echo "\n";
echo '<yMax>';
echo $max_users;
echo '</yMax>';

echo "\n";
echo '<yStep>';
echo max(($max_users - $min_users) / 10, 1);
echo '</yStep>';

echo "\n";
echo '<yLog>1</yLog>';
echo "\n";
echo '</yAxis>';
echo "\n";
echo '</sugarcharts>';

$chart_content = ob_get_clean();

file_put_contents($cache_file, $chart_content);

echo '<script type="text/javascript" src="include/javascript/swfobject.js"></script>';
$sugarChart = new SugarChartReports();
echo $sugarChart->display('SugarChartReport', '/' . $cache_file, 640, 400, '');
?>
<script type="text/javascript">
	if (typeof YAHOO != 'undefined') {
	    YAHOO.util.Event.addListener(window, 'load', loadChartForReports);
	}
</script>
