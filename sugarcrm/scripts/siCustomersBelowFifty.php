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
    `accounts`.`name` AS `account_name`,
    `accounts`.`assigned_user_id`,
    `sugar_installations`.`account_id`,
    MAX(`sugar_installations`.`users` / `sugar_installations`.`license_users`) AS `max_usage`
FROM
    `sugar_installations`
INNER JOIN
    `accounts` ON `accounts`.`id` = `sugar_installations`.`account_id`
WHERE
    `sugar_installations`.`account_id` <> ''
    AND `sugar_installations`.`license_users` <> 9999
    AND `sugar_installations`.`license_users` <> 0
GROUP BY
    `accounts`.`assigned_user_id`,
    `sugar_installations`.`account_id`
HAVING
    `max_usage` < 0.50
ORDER BY
    `accounts`.`assigned_user_id` ASC,
    `accounts`.`name` ASC,
    `sugar_installations`.`account_id` ASC

";

$subject = "Accounts that have never used more than 50% of their subscriptions";

$substantially_fewer_as_percent = round($substantially_fewer * 100, 0) . '%';

$template = "
These accounts have never used more than 50% of any of their subscriptions.
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
	$max_usage = round(100 * $row['max_usage'], 0) . '%';
	
	if (!isset($users[$assigned_user_id])) {
	    $users[$assigned_user_id] = array();
	}
	if (!isset($users[$assigned_user_id][$account_id])) {
	    $users[$assigned_user_id][$account_id] = array('account_name' => $account_name,
							   'max_usage' => $max_usage);
	}
	if (!isset($account_names[$account_id])) {
	    $account_names[$account_id] = $account_name;
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
    echo '<th>Account</th>';
    echo '<th>Max Usage</th>';
    echo '</thead>';
    echo "\n";
    echo '<tbody>';
    foreach ($accounts as $account_id => $data) {
	$account_name = $data['account_name'];
	
	echo "\n";
	echo '<tr>';
	
	echo '<td>';
	echo '<a href="https://sugarinternal.sugarondemand.com/index.php';
	echo '?module=Accounts&amp;action=DetailView&amp;record=';
	echo $account_id;
	echo '">';
	echo $account_name;
	echo '</a>';
	echo '</td>';
	echo '<td>';
	echo $data['max_usage'];
	echo '</td>';
	echo '</tr>';
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
