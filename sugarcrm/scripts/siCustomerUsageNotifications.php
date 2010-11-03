<?php
/*
** @author: Jesse Mullan
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 10540, 11446
** Description: sends an e-mail to CA reps noting utilization of their customers' Sugar instances (logged-in users vs. users purchased) 
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/SiCustomerUsageNotifications.php
*/

ob_start();
chdir('..');
define('sugarEntry', true);

require_once('/var/www/sugarinternal/sugarinternal_lib/SI.class.php');

$do_log = true;
$dry_run = false;
$send_to_jesse_instead = false;

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
    `sugar_installations`.`last_touch` AS `last_touch`,
    `sugar_installations`.`users_active_30_days`,
    `sugar_installations`.`sugar_flavor`,
    `sugar_installations`.`sugar_version`,
    `sugar_installations`.`status`,
    `subscriptions_distgroups`.`quantity`,
    IF(`sugar_installations`.`users_active_30_days` > `subscriptions_distgroups`.`quantity`, 'Over', 'Under') AS `over_under`
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
    AND DATEDIFF(now(), `sugar_installations`.`last_touch`) < 7
    AND `subscriptions_distgroups`.`quantity` <> 9999
    AND (
        `sugar_installations`.`users_active_30_days` < .7 * `subscriptions_distgroups`.`quantity`
        OR `sugar_installations`.`users_active_30_days` > 1.05 * `subscriptions_distgroups`.`quantity`
    )
ORDER BY
    `accounts`.`assigned_user_id`,
    `accounts`.`name`,
    `subscriptions`.`id`,
    `sugar_installations`.`last_touch` DESC
";

$subject = "Heartbeat Data for Your Accounts";

$template = "
Accounts using less than 70% of their users or more than 105% of their users in the last week.

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
	if (!isset($users[$row['assigned_user_id']])) {
	    $users[$row['assigned_user_id']] = array('Over' => array(), 'Under' => array());
	}
	if (!isset($users[$row['assigned_user_id']][$row['account_id']])) {
	    $users[$row['assigned_user_id']][$row['over_under']][$row['account_id']] = array();
	}
	$users[$row['assigned_user_id']][$row['over_under']][$row['account_id']][]
		= array('last_touch' => $row['last_touch'],
			'users_active_30_days' => intval($row['users_active_30_days']),
			'quantity' => intval($row['quantity']),
			'sugar_flavor' => $row['sugar_flavor'],
			'sugar_version' => $row['sugar_version'],
			'status' => $row['status']);
	if (!isset($account_names[$row['account_id']])) {
	    $account_names[$row['account_id']] = $row['account_name'];
	}
    }
}

if (false && $do_log) { SILog::appendStringToFile($logfile, count($users) . ' accounts found'); }

$roll_up_emails = array();
$roll_up_emails['salesops@sugarcrm.com'] = '';

foreach ($users as $assigned_user_id => $accounts) {
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
    echo '<th>Over (105%+ / Under (70% or less)</th>';
    echo '<th>Account</th>';
    echo '<th>Last Touch</th>';
    echo '<th>Sugar Edition</th>';
    echo '<th>Version</th>';
    echo '<th>Current Status</th>';
    echo '<th>Active Users</th>';
    echo '<th>Subscription Quantity</th>';
    echo '<th>Difference</th>';
    echo '</thead>';
    echo "\n";
    echo '<tbody>';
    foreach ($accounts as $over_under => $over_under_data) {
	foreach ($over_under_data as $account_id => $data) {
	    $account_name = $account_names[$account_id];
	    foreach ($data as $usage) {
		echo "\n";
		echo '<tr>';
		echo '<td>';
		echo ($usage['users_active_30_days'] > $usage['quantity'] ? 'Over' : 'Under');
		echo '</td>';
		
		echo '<td>';
		echo '<a href="https://sugarinternal.sugarondemand.com/index.php?module=Accounts&amp;action=DetailView&amp;record=';
		echo $account_id;
		echo '">';
		echo $account_name;
		echo '</a>';
		echo '</td>';
		echo '<td>';
		echo $usage['last_touch'];
		echo '</td>';
		echo '<td>';
		echo $usage['sugar_flavor'];
		echo '</td>';
		echo '<td>';
		echo $usage['sugar_version'];
		echo '</td>';
		echo '<td>';
		echo $usage['status'];
		echo '</td>';
		echo '<td>';
		echo $usage['users_active_30_days'];
		echo '</td>';
		echo '<td>';
		echo $usage['quantity'];
		echo '</td>';
		echo '<td>';
		echo $usage['users_active_30_days'] - $usage['quantity'];
		echo '</td>';
		echo '</tr>';
	    }
	}
    }
    echo "\n";
    echo '</tbody>';
    echo "\n";
    echo '</table>';
    $order_table = ob_get_clean();

    $replacements = array('ORDER_TABLE' => $order_table, 'ASSIGNED_REP_NAME' => $assigned_user->name);

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
	#echo preg_replace('/<[^>]*>/', ' ', $order_table);
	#echo "\n";
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
