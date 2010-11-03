<?php

$detail_view_mobile = array();
$list_view_mobile = array();

$detail_view_mobile['Contacts'] = array(
	'salutation',
	'first_name',
	'last_name',
	'phone_work',
	'phone_mobile',
	'assigned_user_name',
	'team_name',
);

$detail_view_mobile['Leads'] = array (
	'first_name',
	'last_name',
	'status',
	'assigned_user_name',
	'team_name',
);

$detail_view_mobile['Accounts'] = array(
	'name',
	'phone_office',
	'industry',
	'website',
	'assigned_user_name',
	'team_name',
);

$detail_view_mobile['Calls'] = array (
	'name',
	'duration_hours',
	'duration_minutes',
	'date_start',
	'status',
	'direction',
	'assigned_user_name',
);

$detail_view_mobile['Tasks'] = array (
	'name',
	'assigned_user_name',
	'team_name',
	'priority',
	'description',
);

$detail_view_mobile['Meetings'] = array (
	'name',
	'duration_hours',
	'duration_minutes',
);

$detail_view_mobile['Cases'] = array (
	'name',
	'case_number',
	'account_name',
	'description',
	'priority',
	'assigned_user_name',
	'team_name',
);

$detail_view_mobile['Opportunities'] = array (
	'name',
	'account_name',
	'date_closed',
	'description',
	'assigned_user_name',
	'team_name',
);

$detail_view_mobile['Bugs'] = array (
	'name',
	'bug_number',
	'priority',
);

$detail_view_mobile['Notes'] = array (
	'name',
	'description',
	'portal_flag',
	'team_name',
);

$detail_view_mobile['Employees'] = array (
    'first_name',
    'last_name',
    'phone_work',
    'phone_mobile',
    'email1',
);

$module_list_mobile = array();
$module_list_mobile['Accounts'] = 'Accounts';
$module_list_mobile['Bugs'] = 'Bugs';
$module_list_mobile['Cases'] = 'Cases';
$module_list_mobile['Contacts'] = 'Contacts';
$module_list_mobile['Employees'] = 'Employees';
$module_list_mobile['Leads'] = 'Leads';
$module_list_mobile['Opportunities'] = 'Opportunities';
$module_list_mobile['Tasks'] = 'Tasks';
