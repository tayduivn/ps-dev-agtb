<?php

// BEGIN SUGARINTERNAL CUSTOMIZATION (whole file custom) - LEAD QUAL FROM POOL BY SCORE

if(!defined('sugarEntry')) sugar_die('Not a valid entry point');

// leads_locked table
/*
CREATE TABLE `leads_locked` (
  `lead_id` char(36) NOT NULL default '',
  `date_entered` datetime NOT NULL,
  `created_by` varchar(36) default NULL,
  PRIMARY KEY (`lead_id`),
  KEY `idx_date_entered` (`date_entered`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
*/

$user_id = 'c15afb6d-a403-b92a-f388-4342a492003e';
if(!empty($_REQUEST['user'])){
	$user_id = $_REQUEST['user'];
}

$_SESSION['lead_qual_bucket'] = array(
	'user' => $_REQUEST['user'],
);

require_once('modules/Touchpoints/Touchpoint.php');
require_once('custom/si_custom_files/custom_functions.php');

$seed = new Touchpoint();

// Get the DB date from 12 hours ago to filter out locked leads
$date = gmdate('Y-m-d H:i:s', time() - (60 * 60 * 12));

$purge_locked_ids = "delete from leads_locked where date_entered < '$date'";
$GLOBALS['db']->query($purge_locked_ids);

$locked_id_query = "select lead_id, email1 from leads_locked inner join touchpoints on leads_locked.lead_id = touchpoints.id where leads_locked.date_entered > '$date'";
$locked_res = $GLOBALS['db']->query($locked_id_query);
$locked_ids = array();
$email_addresses = array();
while($locked_row = $GLOBALS['db']->fetchByAssoc($locked_res)){
	$locked_ids[] = $locked_row['lead_id'];
	$email_addresses[] = $locked_row['email1'];
}

$locked_id_exclusion = '';
if(!empty($locked_ids)){
	$locked_id_exclusion = "and touchpoints.id not in (
     '".implode("', '", $locked_ids)."'
  )";
}

$email_address_exclusion = '';
if(!empty($email_addresses)){
	$email_address_exclusion = "and touchpoints.email1 not in (
     '".implode("', '", $email_addresses)."'
  )";
}

$team_ids = array();
if(!is_admin($GLOBALS['current_user'])){
	$team_ids = get_team_membership_array($GLOBALS['current_user']->id);
}
else{
	$team_q = "select distinct teams.id from teams inner join team_memberships on teams.id = team_memberships.team_id inner join users on users.id = team_memberships.user_id where teams.deleted = 0 and team_memberships.deleted = 0";
	$team_res = $GLOBALS['db']->query($team_q);
	while($team_row = $GLOBALS['db']->fetchByAssoc($team_res)){
		$team_ids[] = $team_row['id'];
	}
}

$leadQuery =
"
SELECT touchpoints.id, touchpoints.score 'score', touchpoints.date_entered 'date_entered'
FROM touchpoints
";
$seed->add_team_security_where_clause($leadQuery);
$leadQuery .= "
where 
      touchpoints.deleted = 0 and
      touchpoints.assigned_user_id = '{$user_id}' and
";
/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 16219 :: Sugar 6.0 Scrub Queue Empty
** Description: Change query to retrieve 0 or null values from scrubbed column
*/

$leadQuery .= "
      (touchpoints.scrubbed = 0 or touchpoints.scrubbed is null)";
/*END CUSTOMIZATION*/
$leadQuery .= "
      $locked_id_exclusion
      $email_address_exclusion
ORDER BY touchpoints.score DESC, touchpoints.date_entered DESC
LIMIT 0,1
";
//if($GLOBALS['current_user']->user_name == 'sadek') { echo $leadQuery."\n"; die(); }

$poolRes = $GLOBALS['db']->query($leadQuery, true);
$poolRow = $GLOBALS['db']->fetchByAssoc($poolRes);

if(!$poolRow){
	sugar_die("There were no leads found in the Lead Qual Pool. If you feel this is an error, please contact <a href=\'mailto:sadek@sugarcrm.com\'>sadek@sugarcrm.com</a>");
}
else{
	$lock_query = '';
	if(in_array($poolRow['id'], $locked_ids)){
		$lock_query = "update leads_locked set date_entered = NOW(), created_by = '{$GLOBALS['current_user']->id}' where lead_id = '{$poolRow['id']}'";
	}
	else{
		$lock_query = "insert into leads_locked set lead_id = '{$poolRow['id']}', date_entered = NOW(), created_by = '{$GLOBALS['current_user']->id}'";
	}
	$GLOBALS['db']->query($lock_query);
	$return_module = !empty($_REQUEST['return_module']) ? $_REQUEST['return_module'] : 'Touchpoints';
	$return_action = !empty($_REQUEST['return_action']) ? $_REQUEST['return_action'] : 'LeadQualScoredLead';
	$return_id = !empty($_REQUEST['return_id']) ? $_REQUEST['return_id'] : '';
	header("Location: index.php?module=Touchpoints&action=ScrubView&record={$poolRow['id']}&return_module={$return_module}&return_action={$return_action}&return_id={$return_id}&user={$user_id}");
}
