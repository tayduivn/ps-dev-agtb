<?php
ob_start();
chdir('..');
define('sugarEntry', true);

require_once('/var/www/sugarinternal/sugarinternal_lib/SI.class.php');

$do_log = true;
$dry_run = false;
$send_to_jesse_instead = true;

$substantially_fewer = 0.25;

$roll_ups = array('csharma' => 'csharma@sugarcrm.com',
		  'browe' => 'csharma@sugarcrm.com',
		  'lhopkins' => 'csharma@sugarcrm.com',
		  'cdoolittle' => 'csharma@sugarcrm.com',
		  'CAGroup' => 'csharma@sugarcrm.com',
		  'wmclaughlin' => 'csharma@sugarcrm.com'
		  );

if ($do_log) {
# For all your logging needs
    require_once('/var/www/sugarinternal/sugarinternal_lib/SILog.class.php');
    $logfile = 'siCustomerUsageNotifications.php.log';
}

require_once('include/entryPoint.php');
require_once('modules/EmailTemplates/EmailTemplate.php');
require_once('include/SugarPHPMailer.php');
require_once("modules/Administration/Administration.php");
require_once('modules/Emails/Email.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Users/User.php');

global $current_user;
$current_user = new User();
$current_user->getSystemUser();

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

/*
 * TODO: Change logging level before going into production, but probably after
 * testing.
 */

$GLOBALS['log']->fatal("----->siCustomerUsageNotifications.php started");
	
$query = "
SELECT
    `accounts`.`assigned_user_id`,
    `accounts`.`name` AS `account_name`,
    `accounts`.`id` AS `account_id`,
    IF (DATEDIFF(now(), `sugar_installations`.`last_touch`) >= 90,
        'Q1',
        'Q2'
    ) AS `quarter`,
    MAX(`sugar_installations`.`last_touch`) AS `last_touch`,
    MAX(`sugar_installations`.`users_active_30_days`) AS `user_count`,
    MAX(`subscriptions_distgroups`.`quantity`) AS `subscription_count`,
    GROUP_CONCAT(DISTINCT `sugar_installations`.`status`) AS `status`,
    GROUP_CONCAT(DISTINCT `sugar_installations`.`sugar_flavor`) AS `sugar_flavor`,
    MAX(`sugar_installations`.`sugar_version`) AS `sugar_version`
FROM
    `accounts`
INNER JOIN
    `subscriptions` ON `accounts`.`id` = `subscriptions`.`account_id`
INNER JOIN
    `sugar_installations` ON `subscriptions`.`subscription_id` = `sugar_installations`.`license_key`
INNER JOIN
    `subscriptions_distgroups` ON `subscriptions`.`id` = `subscriptions_distgroups`.`subscription_id`
WHERE
    `subscriptions`.`account_id` <> ''
#    AND `sugar_installations`.`status` = 'A'
    AND `subscriptions`.`status` = 'enabled'
    AND `subscriptions_distgroups`.`deleted` <> 1
    AND DATEDIFF(now(), `sugar_installations`.`last_touch`) < 180
    AND `subscriptions_distgroups`.`quantity` <> 9999
GROUP BY
    `accounts`.`assigned_user_id`,
    `accounts`.`id`,
    `quarter`


ORDER BY
    `accounts`.`assigned_user_id`,
    `accounts`.`id`,
    `quarter`

";

$subject = "Subscription Usage Predictions for Your Accounts";

$substantially_fewer_as_percent = round($substantially_fewer * 100, 0) . '%';

$template = "
Accounts predicted to dip below $substantially_fewer_as_percent or rise over 100% of their subscriptions

ORDER_TABLE
";


$users = array();
$accounts = array();
$account_names = array();
$res = $GLOBALS['db']->query($query);
if (!$res) {
    /*
     * If there is a problem with the query, quick early and make a lot of
     * noise in the logs.
     */
    $GLOBALS['db']->checkError('Error with query');
    $GLOBALS['log']->fatal("----->siCustomerUsageNotifications.php query failed: " . $query);
    exit;
} else {
    /*
     * The query did not bomb, so put all of the opportunity ids into an
     * array for future processing.
     */
    while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
	$assigned_user_id = $row['assigned_user_id'];
	if (!$assigned_user_id) {
	    continue;
	}
	$account_name = $row['account_name'];
	$account_id = $row['account_id'];
	$quarter = $row['quarter'];
	$user_count = intval($row['user_count']);
	$subscription_count = intval($row['subscription_count']);
	$last_touch = substr($row['last_touch'], 0, 10);
	$status = $row['status'];
	$sugar_version = $row['sugar_version'];
	$sugar_flavor = $row['sugar_flavor'];
	
	if (!isset($users[$assigned_user_id])) {
	    $users[$assigned_user_id] = array();
	}
	if (!isset($users[$assigned_user_id][$account_id])) {
	    $users[$assigned_user_id][$account_id] = array('account_name' => $account_name,
							   'qs' => array(),
							   'change' => 0,
							   'projection' => null,
							   'last_touch' => '',
							   'last_subscription_amount' => null,
							   'sugar_flavor' => null,
							   'sugar_version' => null,
							   'status' => null);
	}
	$users[$assigned_user_id][$account_id]['qs'][$quarter] = array('user_count' => $user_count,
								       'subscription_count' => $subscription_count);
	if ('Q2' == $quarter) {
	    $users[$assigned_user_id][$account_id]['last_touch'] = $last_touch;
	    $users[$assigned_user_id][$account_id]['last_subscription_amount'] = $subscription_count;
	    $users[$assigned_user_id][$account_id]['status'] = $status;
	    $users[$assigned_user_id][$account_id]['sugar_version'] = $sugar_version;
	    $users[$assigned_user_id][$account_id]['sugar_flavor'] = $sugar_flavor;
	}
	if (!isset($account_names[$account_id])) {
	    $account_names[$account_id] = $account_name;
	}
	if (2 == count($users[$assigned_user_id][$account_id]['qs'])) {
	    $users[$assigned_user_id][$account_id]['projection']
		    = max(0, ((2 * $users[$assigned_user_id][$account_id]['qs']['Q2']['user_count'])
			      - $users[$assigned_user_id][$account_id]['qs']['Q1']['user_count']));
	    $users[$assigned_user_id][$account_id]['change']
		    = ($users[$assigned_user_id][$account_id]['qs']['Q2']['user_count']
		       - $users[$assigned_user_id][$account_id]['qs']['Q1']['user_count']);
	}
    }
}
$notifies = array();
foreach ($users as $assigned_user_id => $accounts) {
    foreach ($accounts as $account_id => $account) {
	if (is_null($account['projection'])
	    || 0 == $account['change']
	    || ($account['projection'] > $substantially_fewer * $account['last_subscription_amount']
		&& $account['projection'] <= $account['last_subscription_amount'])) {
	    continue;
	}
	$over_under = $account['projection'] > $account['last_subscription_amount'] ? 'Over' : 'Under';
	if (!isset($notifies[$assigned_user_id])) {
	    $notifies[$assigned_user_id] = array('Over' => array(),
						 'Under' => array());
	}
	$notifies[$assigned_user_id][$over_under][$account_id] = $account;
    }
}

if (false && $do_log) { SILog::appendStringToFile($logfile, count($users) . ' accounts found'); }

$roll_up_emails = array();
$roll_up_emails['salesops@sugarcrm.com'] = '';

foreach ($notifies as $assigned_user_id => $over_unders) {
    $assigned_user = null;
    if (!empty($assigned_user_id)) {
	$assigned_user = new User();
	$assigned_user->retrieve($assigned_user_id);
    }
    if (!$assigned_user) {
	if ($do_log) {
	    SILog::appendStringToFile($logfile, "$assigned_user_id NO ASSIGNED USER");
        }
	continue;
    }
    /*
     * String buffers are the fastest way to concatenate strings -- and
     * hopefully readable as well.
     */
    /*    Last Touch Sugar Edition Sugar Version Current Status: Active Users: */
    ob_start();
    echo '<table>';
    echo "\n";
    echo '<thead>';
    echo "<th>Prediction: Over (100%+) / Under ($substantially_fewer_as_percent or less)</th>";
    echo '<th>Account</th>';
    echo '<th>Last Touch</th>';
    echo '<th>Sugar Edition</th>';
    echo '<th>Version</th>';
    echo '<th>Current Status</th>';
    
    echo '<th>Q1</th>';
    echo '<th>Q2</th>';
    echo '<th>Prediction</th>';
    echo '<th>Subscription</th>';
    
    echo '</thead>';
    echo "\n";
    echo '<tbody>';
    foreach ($over_unders as $over_under => $over_under_data) {
	foreach ($over_under_data as $account_id => $data) {
	    $account_name = $data['account_name'];

	    echo "\n";
	    echo '<tr>';
	    echo '<td>';
	    echo $over_under;
	    echo '</td>';
	    
	    echo '<td>';
	    echo '<a href="https://sugarinternal.sugarondemand.com/index.php';
	    echo '?module=Accounts&amp;action=DetailView&amp;record=';
	    echo $account_id;
	    echo '">';
	    echo $account_name;
	    echo '</a>';
	    echo '</td>';
	    echo '<td>';
	    echo $data['last_touch'];
	    echo '</td>';
	    echo '<td>';
	    echo $data['sugar_flavor'];
	    echo '</td>';
	    echo '<td>';
	    echo $data['sugar_version'];
	    echo '</td>';
	    echo '<td>';
	    echo $data['status'];
	    echo '</td>';
	    
	    echo '<td>';
	    echo $data['qs']['Q1']['user_count'];		
	    echo '</td>';
	    echo '<td>';
	    echo $data['qs']['Q2']['user_count'];
	    echo '</td>';
	    echo '<td>';
	    echo $data['projection'];
	    echo '</td>';
	    echo '<td>';
	    echo $data['last_subscription_amount'];
	    echo '</td>';
	    echo '</tr>';
	}
    }
    echo "\n";
    echo '</tbody>';
    echo "\n";
    echo '</table>';
    $order_table = ob_get_clean();

    $replacements = array('ORDER_TABLE' => $order_table,
			  'ASSIGNED_REP_NAME' => $assigned_user->name);

    $body = $template;
    foreach ($replacements as $search => $replace) {
	$body = str_replace($search, $replace, $body);
    }
    
    $customer_template_data = array('subject' => $subject,
				    'body' => $body);

    $roll_up_chunk = '';
    $roll_up_chunk .= '<p>For assigned rep: ';
    $roll_up_chunk .= $assigned_user->first_name . " " . $assigned_user->last_name;
    $roll_up_chunk .= '</p>';
    $roll_up_chunk .= "\n";
    $roll_up_chunk .= $body;

    if (!empty($roll_ups[$assigned_user->user_name])) {
	$roll_up = $roll_ups[$assigned_user->user_name];
	if (!isset($roll_up_emails[$roll_up])) {
	    $roll_up_emails[$roll_up] = '';
	}
	$roll_up_emails[$roll_up] .= $roll_up_chunk;
    }
    /* All roll up emails get sent to salesops */
    $roll_up_emails['salesops@sugarcrm.com'] .= $roll_up_chunk;
    
    
    if (!$dry_run) {

	$email_bean = new Email();
	$email_bean->id = create_guid();
	$email_bean->new_with_id = true;
	$email_bean->saved_attachments = array();
	$email_bean->save(false);
	$email_bean->new_with_id = false;	
	
	$email_bean->name = $customer_template_data['subject'];
	    
	$email_bean->from_addr = $assigned_user->email1;
	$email_bean->from_name = $assigned_user->first_name . " " . $assigned_user->last_name;
	
	$email_bean->to_addrs_arr = array();
	$email_bean->bcc_addrs_arr = array();

	if (!$send_to_jesse_instead) {
	    $email_bean->to_addrs_arr[] = array('email' => $assigned_user->email1,
						'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
	    $email_bean->to_addrs = $assigned_user->email1;
	} else {
	    $email_bean->to_addrs_arr[] = array('email' => 'jmullan@sugarcrm.com',
						'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
	    $email_bean->to_addrs = 'jmullan@sugarcrm.com';
	}
	$email_bean->cc_addrs_arr = array();
	
	$email_bean->description_html = $customer_template_data['body'];
	    
	$email_bean->date_sent = date('Y-m-d h:i:s');
	$email_bean->assigned_user_id = $assigned_user->id;   
		    
	$success = $email_bean->Send();
	$email_bean->save(false);
    } else {
	echo preg_replace('/<[^>]*>/', ' ', $order_table);
	echo "\n";
    }
}

foreach ($roll_up_emails as $email_address => $master_email) {
    if ($master_email) {
	if (!$dry_run) {
	    
	    $email_bean = new Email();
	    $email_bean->id = create_guid();
	    $email_bean->new_with_id = true;
	    $email_bean->saved_attachments = array();
	    $email_bean->save(false);
	    $email_bean->new_with_id = false;	
	    
	    $email_bean->name = $subject . ' by rep';
	    
	    $email_bean->from_addr = 'internalsystems@sugarcrm.com';
	    $email_bean->from_name = 'Internal Systems';
	    
	    $email_bean->to_addrs_arr = array();
	    $email_bean->bcc_addrs_arr = array();
	    
	    if (!$send_to_jesse_instead) {
		$email_bean->to_addrs_arr[] = array('email' => $email_address,
						    'name' => $email_address);
		$email_bean->to_addrs = $email_address;
	    } else {
		$email_bean->to_addrs_arr[] = array('email' => 'jmullan@sugarcrm.com',
						    'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
		$email_bean->to_addrs = 'jmullan@sugarcrm.com';
	    }
	    $email_bean->cc_addrs_arr = array();
	    
	    $email_bean->description_html = $master_email;
	    
	    $email_bean->date_sent = date('Y-m-d h:i:s');
	    $email_bean->assigned_user_id = $assigned_user->id;   
	    
	    $success = $email_bean->Send();
	    $email_bean->save(false);
	} else {
	    echo "$email_address\n";
	    echo preg_replace('/<[^>]*>/', ' ', $master_email);
	    echo "\n";
	}
    }
}

$GLOBALS['log']->fatal("----->siCustomerUsageNotifications.php finished");
/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
    echo $whatevs;
}
