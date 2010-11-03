<?php
/*
** @author: Julian Ostrow, DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 11379, 17806
** Description: sends an e-mail to each member of the CA Group with a list of their Accounts that haven't been "touched" in the past 30 days,
**              17806 refined report to include touches if rep hasnt touched
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/NoTouchReportCAGroup.php
*/

chdir('..');
define('sugarEntry', true);
ob_start();

// BEGIN SCRIPT CONFIGURATION
$cagroup_members = array(
	'csharma',
	'lhopkins',
	'cdoolittle',
	'browe',
	'ddjanikian',
	'mdonato',
	'lhoover',
	'msantos',
);

$email_subject = "Your Accounts with no touches in the past 30 days";

$modPluralToSingular = array(
	'Notes' => 'Note',
	'Calls' => 'Call',
	'Meetings' => 'Meeting',
	'Tasks' => 'Task',
	'Emails' => 'Email',
);
// END SCRIPT CONFIGURATION

function arr_to_in($arr) {
	return implode(', ', array_map('add_quotes', $arr));
}

function sortByAccountName($acc1, $acc2) {
	if ($acc1['name'] == $acc2['name']) {
		return 0;
	}

	return ($acc1['name'] < $acc2['name']) ? -1 : 1;
}

function checkTouchLast30Days($acc_data) {
	$s30day = 86400 * 30;

	$touches = array(
		'Notes' => isset($acc_data['Notes']) ? (time() - strtotime($acc_data['Notes']['date_modified'])) : -1,
		'Calls' => isset($acc_data['Calls']) ? (time() - strtotime($acc_data['Calls']['date_modified'])) : -1,
		'Meetings' => isset($acc_data['Meetings']) ? (time() - strtotime($acc_data['Meetings']['date_modified'])) : -1,
		'Tasks' => isset($acc_data['Tasks']) ? (time() - strtotime($acc_data['Tasks']['date_modified'])) : -1,
		'Emails' => isset($acc_data['Emails']) ? (time() - strtotime($acc_data['Emails']['date_modified'])) : -1,
	);

	asort($touches);

	foreach ($touches as $mod => $time) {
		if ($time == -1) continue;

		return array(
			'module' => $mod,
			'time' => $time,
		);
	}

	return FALSE;
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

$current_language = $sugar_config['default_language'];
$app_strings = return_application_language($current_language);
if (!isset($modListHeader)) {
    if (isset($current_user)) {
        $modListHeader = query_module_access_list($current_user);
    }
}

$userguid_query = "SELECT users.id, users.user_name
	FROM users WHERE users.user_name IN (" . arr_to_in($cagroup_members) . ")";

$userguid_res = $GLOBALS['db']->query($userguid_query);

$cagroup_guids = array();
while ($userguid_row = $GLOBALS['db']->fetchByAssoc($userguid_res)) {
	$cagroup_guids[$userguid_row['id']] = $userguid_row['user_name'];
}

$acc_query = "SELECT accounts.id, accounts.assigned_user_id, accounts.name FROM accounts
	WHERE accounts.assigned_user_id IN (" . arr_to_in(array_keys($cagroup_guids)) . ")
		AND accounts.deleted = 0
		AND accounts.account_type IN ('Customer', 'Customer-Ent', 'Customer-Express')";

$acc_res = $GLOBALS['db']->query($acc_query);

$accounts = array();
$accounts_ANY = array();
while ($acc_row = $GLOBALS['db']->fetchByAssoc($acc_res)) {
	$accounts[$acc_row['id']] = array(
		'assigned_user_id' => $acc_row['assigned_user_id'],
		'name' => $acc_row['name'],
	);
	$$accountsAnyTouch[$acc_row['id']] = array(
		'assigned_user_id' => $acc_row['assigned_user_id'],
		'name' => $acc_row['name'],
	);
}

// get Notes and associate them to the Accounts
$notes_query = "SELECT notes.id, notes.name, notes.date_modified, notes.modified_user_id, notes.parent_id
	FROM notes LEFT JOIN accounts ON notes.parent_type = 'Accounts' AND notes.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND notes.modified_user_id IN (" . arr_to_in(array_keys($cagroup_guids)) . ")
		AND notes.deleted = 0
	GROUP BY notes.parent_id ORDER BY notes.date_modified DESC";

$notes_res = $GLOBALS['db']->query($notes_query);
while ($notes_row = $GLOBALS['db']->fetchByAssoc($notes_res)) {
	$accounts[$notes_row['parent_id']]['Notes'] = array(
		'id' => $notes_row['id'],
		'name' => $notes_row['name'],
		'date_modified' => $notes_row['date_modified'],
		'modified_user_id' => $notes_row['modified_user_id'],
	);
}

// get ANY Notes touch and associate
$notes_ANY_query = "SELECT notes.id, notes.name, notes.date_modified, notes.modified_user_id, notes.parent_id
	FROM notes LEFT JOIN accounts ON notes.parent_type = 'Accounts' AND notes.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND notes.deleted = 0
	GROUP BY notes.parent_id ORDER BY notes.date_modified DESC";

$notes_ANY_res = $GLOBALS['db']->query($notes_ANY_query );
while ($notes_row = $GLOBALS['db']->fetchByAssoc($notes_ANY_res)) {
	$$accounts_ANY[$notes_row['parent_id']]['Notes'] = array(
		'id' => $notes_row['id'],
		'name' => $notes_row['name'],
		'date_modified' => $notes_row['date_modified'],
		'modified_user_id' => $notes_row['modified_user_id'],
	);
}

// get Calls and associate them to the Accounts
$calls_query = "SELECT calls.id, calls.date_modified, calls.modified_user_id, calls.name, calls.parent_id
	FROM calls LEFT JOIN accounts ON calls.parent_type = 'Accounts' AND calls.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND calls.deleted = 0
 		AND calls.modified_user_id IN (" . arr_to_in(array_keys($cagroup_guids)) . ")
	GROUP BY calls.parent_id ORDER BY calls.date_modified DESC";


$calls_res = $GLOBALS['db']->query($calls_query);
while ($calls_row = $GLOBALS['db']->fetchByAssoc($calls_res)) {
	$accounts[$calls_row['parent_id']]['Calls'] = array(
		'id' => $calls_row['id'],
		'name' => $calls_row['name'],
		'date_modified' => $calls_row['date_modified'],
		'modified_user_id' => $calls_row['modified_user_id'],
	);
}

// get ANY Calls and associate them to the Accounts
$calls_ANY_query = "SELECT calls.id, calls.date_modified, calls.modified_user_id, calls.name, calls.parent_id
	FROM calls LEFT JOIN accounts ON calls.parent_type = 'Accounts' AND calls.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND calls.deleted = 0
	GROUP BY calls.parent_id ORDER BY calls.date_modified DESC";


$calls_ANY_res = $GLOBALS['db']->query($calls_ANY_query);
while ($calls_row = $GLOBALS['db']->fetchByAssoc($calls_ANY_res)) {
	$accounts_ANY[$calls_row['parent_id']]['Calls'] = array(
		'id' => $calls_row['id'],
		'name' => $calls_row['name'],
		'date_modified' => $calls_row['date_modified'],
		'modified_user_id' => $calls_row['modified_user_id'],
	);
}

// get Meetings and associate them to the Accounts
$meetings_query = "SELECT meetings.id, meetings.date_modified, meetings.modified_user_id, meetings.name, meetings.parent_id
	FROM meetings LEFT JOIN accounts ON meetings.parent_type = 'Accounts' AND meetings.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND meetings.deleted = 0
 		AND meetings.modified_user_id IN (" . arr_to_in(array_keys($cagroup_guids)) . ")
	GROUP BY meetings.parent_id ORDER BY meetings.date_modified DESC";


$meetings_res = $GLOBALS['db']->query($meetings_query);
while ($meetings_row = $GLOBALS['db']->fetchByAssoc($meetings_res)) {
	$accounts[$meetings_row['parent_id']]['Meetings'] = array(
		'id' => $meetings_row['id'],
		'name' => $meetings_row['name'],
		'date_modified' => $meetings_row['date_modified'],
		'modified_user_id' => $meetings_row['modified_user_id'],
	);
}

// get ANY Meetings and associate them to the Accounts
$meetings_ANY_query = "SELECT meetings.id, meetings.date_modified, meetings.modified_user_id, meetings.name, meetings.parent_id
	FROM meetings LEFT JOIN accounts ON meetings.parent_type = 'Accounts' AND meetings.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND meetings.deleted = 0
	GROUP BY meetings.parent_id ORDER BY meetings.date_modified DESC";


$meetings_ANY_res = $GLOBALS['db']->query($meetings_ANY_query);
while ($meetings_row = $GLOBALS['db']->fetchByAssoc($meetings_ANY_res)) {
	$accounts_ANY[$meetings_row['parent_id']]['Meetings'] = array(
		'id' => $meetings_row['id'],
		'name' => $meetings_row['name'],
		'date_modified' => $meetings_row['date_modified'],
		'modified_user_id' => $meetings_row['modified_user_id'],
	);
}

// get Tasks and associate them to the Accounts
$tasks_query = "SELECT tasks.id, tasks.date_modified, tasks.modified_user_id, tasks.name, tasks.parent_id
	FROM tasks LEFT JOIN accounts ON tasks.parent_type = 'Accounts' AND tasks.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND tasks.deleted = 0
 		AND tasks.modified_user_id IN (" . arr_to_in(array_keys($cagroup_guids)) . ")
	GROUP BY tasks.parent_id ORDER BY tasks.date_modified DESC";


$tasks_res = $GLOBALS['db']->query($tasks_query);
while ($tasks_row = $GLOBALS['db']->fetchByAssoc($tasks_res)) {
	$accounts[$tasks_row['parent_id']]['Tasks'] = array(
		'id' => $tasks_row['id'],
		'name' => $tasks_row['name'],
		'date_modified' => $tasks_row['date_modified'],
		'modified_user_id' => $tasks_row['modified_user_id'],
	);
}

// get ANY Tasks and associate them to the Accounts
$tasks_ANY_query = "SELECT tasks.id, tasks.date_modified, tasks.modified_user_id, tasks.name, tasks.parent_id
	FROM tasks LEFT JOIN accounts ON tasks.parent_type = 'Accounts' AND tasks.parent_id = accounts.id
	WHERE accounts.id IN (" . arr_to_in(array_keys($accounts)) . ")
		AND tasks.deleted = 0
	GROUP BY tasks.parent_id ORDER BY tasks.date_modified DESC";


$tasks_ANY_res = $GLOBALS['db']->query($tasks_ANY_query);
while ($tasks_row = $GLOBALS['db']->fetchByAssoc($tasks_ANY_res)) {
	$accounts_ANY[$tasks_row['parent_id']]['Tasks'] = array(
		'id' => $tasks_row['id'],
		'name' => $tasks_row['name'],
		'date_modified' => $tasks_row['date_modified'],
		'modified_user_id' => $tasks_row['modified_user_id'],
	);
}

// get emails and associate
$emails_query = "SELECT derivedemails.bean_id, emails.id, emails.name, emails.date_modified, emails.modified_user_id
    FROM emails
    JOIN (
        SELECT DISTINCT email_id, eabr.bean_id
        FROM emails_email_addr_rel eear
        JOIN emails
            ON eear.email_id = emails.id
            AND emails.modified_user_id IN (" . arr_to_in(array_keys($cagroup_guids)) . ")
        JOIN email_addr_bean_rel eabr
            ON eabr.bean_id IN (" . arr_to_in(array_keys($accounts)) . ")
            AND eabr.bean_module = 'Accounts'
            AND eabr.email_address_id = eear.email_address_id
            AND eabr.deleted=0
        ORDER BY emails.date_modified DESC
    ) derivedemails
        ON derivedemails.email_id = emails.id
    WHERE emails.deleted = 0
    GROUP BY derivedemails.bean_id";

$emails_res = $GLOBALS['db']->query($emails_query);
while ($emails_row = $GLOBALS['db']->fetchByAssoc($emails_res)) {
	$accounts[$emails_row['bean_id']]['Emails'] = array(
		'id' => $emails_row['id'],
		'name' => $emails_row['name'],
		'date_modified' => $emails_row['date_modified'],
		'modified_user_id' => $emails_row['modified_user_id'],
	);
}


// get ANY emails and associate to account
$emails_ANY_query = "SELECT derivedemails.bean_id, emails.id, emails.name, emails.date_modified, emails.modified_user_id
    FROM emails
    JOIN (
        SELECT DISTINCT email_id, eabr.bean_id
        FROM emails_email_addr_rel eear
        JOIN emails
            ON eear.email_id = emails.id
        JOIN email_addr_bean_rel eabr
            ON eabr.bean_id IN (" . arr_to_in(array_keys($accounts)) . ")
            AND eabr.bean_module = 'Accounts'
            AND eabr.email_address_id = eear.email_address_id
            AND eabr.deleted=0
        ORDER BY emails.date_modified DESC
    ) derivedemails
        ON derivedemails.email_id = emails.id
    WHERE emails.deleted = 0
    GROUP BY derivedemails.bean_id";

$emails_ANY_res = $GLOBALS['db']->query($emails_ANY_query);
while ($emails_row = $GLOBALS['db']->fetchByAssoc($emails_ANY_res)) {
	$accounts_ANY[$emails_row['bean_id']]['Emails'] = array(
		'id' => $emails_row['id'],
		'name' => $emails_row['name'],
		'date_modified' => $emails_row['date_modified'],
		'modified_user_id' => $emails_row['modified_user_id'],
	);
}

uasort($accounts_ANY, 'sortByAccountName');
uasort($accounts, 'sortByAccountName');

$lonely_accounts = array();
$result="DEBUG OUTPUT \n";

foreach ($accounts as $acc_id => $acc_data) {
	$checkTouch = checkTouchLast30Days($acc_data);
	if ($checkTouch === FALSE) {  // If hasn't been touched by rep load any touch
		$acc_ANY_data = $accounts_ANY[$acc_id ];
		$check_any_touch = checkTouchLast30Days($accounts_ANY[$acc_id ]);
		$lonely_accounts[$acc_data['assigned_user_id']][$acc_id] = array(
			'name' => $acc_data['name'],
			'last_touch' => array(
				'module' => $check_any_touch['module'],
				'time' => round($check_any_touch['time'] / 86400),
				'id' => $acc_ANY_data[$check_any_touch['module']]['id'],
				'name' => $acc_ANY_data[$check_any_touch['module']]['name'],
			),
		);
		if (round($check_any_touch['time'] / 86400) == 0) {
			$lonely_accounts[$acc_data['assigned_user_id']][$acc_id]['last_touch']['name'] = "No touches";
		}
	}
	elseif ($checkTouch['time'] > (86400 * 30)) {
		$lonely_accounts[$acc_data['assigned_user_id']][$acc_id] = array(
			'name' => $acc_data['name'],
			'last_touch' => array(
				'module' => $checkTouch['module'],
				'time' => round($checkTouch['time'] / 86400),
				'id' => $acc_data[$checkTouch['module']]['id'],
				'name' => $acc_data[$checkTouch['module']]['name'],
			),
		);
	}
}

foreach ($lonely_accounts as $user_guid => $lonely) {
	$fname = $cagroup_guids[$user_guid] . ".csv";
	$email = "<html><head><style type='text/css'>BODY { font-family: Verdana, sans-serif; font-size: medium; }</style></head><body>";
	$email .= "<table><tr><td><b>Account Name</b></td><td style=\"padding-right: 10px;\"><b>Last Touch (days)</b></td><td><b>Last Touch Details</b></td></tr>";

	$csv = "\"Account Name\",\"Last Touch (days)\",\"Last Touch Details\",\"Last Touch URL\",\"Account URL\"\n";

	foreach ($lonely as $acc_id => $acc_info) {
		$lt_acc = "<a href=\"{$sugar_config['site_url']}/index.php?module=Accounts&action=DetailView&record={$acc_id}\">{$acc_info['name']}</a>";
		$lt_days = empty($acc_info['last_touch']) ? "<i>never</i>" : "{$acc_info['last_touch']['time']}";
		$lt_info = empty($acc_info['last_touch']) ? "<i>n/a</i>" : "{$modPluralToSingular[$acc_info['last_touch']['module']]}: <a href=\"{$sugar_config['site_url']}/index.php?module={$acc_info['last_touch']['module']}&action=DetailView&record={$acc_info['last_touch']['id']}\">{$acc_info['last_touch']['name']}</a>";

		$email .= "<tr><td>{$lt_acc}</td><td>{$lt_days}</td><td>{$lt_info}</td></tr>\n";

		$csv .= "\"" . $acc_info['name'] . "\",";
		$csv .= empty($acc_info['last_touch']) ? "\"\"," : "\"{$acc_info['last_touch']['time']}\",";
		$csv .= empty($acc_info['last_touch']) ? "\"\"," : "\"{$modPluralToSingular[$acc_info['last_touch']['module']]}: " . str_replace("\"", "\\\"", $acc_info['last_touch']['name']) . "\",";
		$csv .= empty($acc_info['last_touch']) ? "\"\"," : "\"{$sugar_config['site_url']}/index.php?module={$acc_info['last_touch']['module']}&action=DetailView&record={$acc_info['last_touch']['id']}\",";
		$csv .= "\"{$sugar_config['site_url']}/index.php?module=Accounts&action=DetailView&record={$acc_id}\"\n";
	}

	$email .= "</table></body></html>";

	$email_bean = new Email();
	$email_bean->id = create_guid();
	$email_bean->new_with_id = true;

	$csv_filename = $cagroup_guids[$user_guid] . "_accounts_with_no_touches_" . date('Y-m-d') . ".csv";

	$note = new Note();
	$note->filename = $csv_filename;
	$note->team_id = 1;
	$note->file_mime_type = "text/csv";
	$note->name = "Email Attachment: {$csv_filename}";

	$note->parent_id = $email_bean->id;
	$note->parent_type = "Emails";
	$note->save(false);

	// write the CSV to the Sugar Internal filesystem
	$fp = fopen($GLOBALS['sugar_config']['upload_dir'] . "/{$note->id}", "wb");
	fwrite($fp, $csv);
	fclose($fp);

    $email_bean->saved_attachments[] = $note;
    $email_bean->save(false);
    $email_bean->new_with_id = false;

    $email_bean->load_relationship('notes');
    $email_bean->notes->add($note->id);
    $email_bean->save(false);
    $email_bean->new_with_id = false;
    
    $email_bean->name = $email_subject;
	    
    $email_bean->from_addr = 'internalsystems@sugarcrm.com';
    $email_bean->from_name = 'Internal Systems';

	$send_to = new User();
	$send_to->retrieve($user_guid);
	    
    $email_bean->to_addrs_arr[] = array(
		'email' => $send_to->email1,
		'name' => $send_to->name,
	);

    $email_bean->bcc_addrs_arr = array();
    $email_bean->cc_addrs_arr = array();

	if ($cagroup_guids[$user_guid] != 'csharma') {
		$email_bean->cc_addrs_arr[] = array(
			'email' => 'csharma@sugarcrm.com',
			'name' => 'Christine Sharma',
		);
	}
	    
    $email_bean->description_html = $email;
	    
    $email_bean->date_sent = date('Y-m-d h:i:s');
    $email_bean->assigned_user_id = $user_guid;

    $success = $email_bean->Send();
    $email_bean->save(false);
}

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
//echo $whatevs;
