<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
 
$mod_strings = array (

	'LBL_MODULE_NAME' => 'Calendar',
	'LBL_MODULE_TITLE' => 'Calendar',
	'LNK_NEW_CALL' => 'Schedule Call',
	'LNK_NEW_MEETING' => 'Schedule Meeting',
	'LNK_NEW_APPOINTMENT' => 'Create Appointment',
	'LNK_NEW_TASK' => 'Create Task',
	'LNK_CALL_LIST' => 'Calls',
	'LNK_MEETING_LIST' => 'Meetings',
	'LNK_TASK_LIST' => 'Tasks',
	'LNK_VIEW_CALENDAR' => 'Today',
	'LNK_IMPORT_CALLS' => 'Import Calls',
	'LNK_IMPORT_MEETINGS' => 'Import Meetings',
	'LNK_IMPORT_TASKS' => 'Import Tasks',
	'LBL_MONTH' => 'Month',
	'LBL_DAY' => 'Day',
	'LBL_YEAR' => 'Year',
	'LBL_WEEK' => 'Week',
	'LBL_PREVIOUS_MONTH' => 'Previous Month',
	'LBL_PREVIOUS_DAY' => 'Previous Day',
	'LBL_PREVIOUS_YEAR' => 'Previous Year',
	'LBL_PREVIOUS_WEEK' => 'Previous Week',
	'LBL_NEXT_MONTH' => 'Next Month',
	'LBL_NEXT_DAY' => 'Next Day',
	'LBL_NEXT_YEAR' => 'Next Year',
	'LBL_NEXT_WEEK' => 'Next Week',
	'LBL_AM' => 'AM',
	'LBL_PM' => 'PM',
	'LBL_SCHEDULED' => 'Scheduled',
	'LBL_BUSY' => 'Busy',
	'LBL_CONFLICT' => 'Conflict',
	'LBL_USER_CALENDARS' => 'User Calendars',
	'LBL_SHARED' => 'Shared',
	'LBL_PREVIOUS_SHARED' => 'Previous',
	'LBL_NEXT_SHARED' => 'Next',
	'LBL_SHARED_CAL_TITLE' => 'Shared Calendar',
	'LBL_USERS' => 'Users',
	'LBL_REFRESH' => 'Refresh',
	'LBL_EDIT_USERLIST' => 'User List',
	'LBL_SELECT_USERS' => 'Select users for calendar display',
	'LBL_FILTER_BY_TEAM' => 'Filter user list by team:',
	'LBL_ASSIGNED_TO_NAME' => 'Assigned to',
	'LBL_DATE' => 'Start Date & Time',  
	'LBL_CREATE_MEETING' => 'Schedule Meeting',
	'LBL_CREATE_CALL' => 'Log Call',
	'LBL_HOURS_ABBREV' => 'h',
	'LBL_MINS_ABBREV' => 'm',


	'LBL_YES' => 'Yes',
	'LBL_NO' => 'No',
	'LBL_SETTINGS' => 'Settings',
	'LBL_CREATE_NEW_RECORD' => 'Create Activity',
	'LBL_LOADING' => 'Loading ......',
	'LBL_SAVING' => 'Saving ......',
	'LBL_SENDING_INVITES' => 'Saving & Sending Invites .....',
	'LBL_CONFIRM_REMOVE' => 'Are you sure you want to remove the record?',
	'LBL_CONFIRM_REMOVE_ALL_RECURRING' => 'Are you sure you want to remove all recurring records?',
	'LBL_EDIT_RECORD' => 'Edit Activity',
	'LBL_ERROR_SAVING' => 'Error while saving',
	'LBL_ERROR_LOADING' => 'Error while loading',
	'LBL_GOTO_DATE' => 'Goto Date',
	'NOTICE_DURATION_TIME' => 'Duration time must be greater than 0',
	'LBL_STYLE_BASIC' => 'Basic',
	'LBL_STYLE_ADVANCED' => 'Advanced',

	'LBL_INFO_TITLE' => 'Additional Details',
	'LBL_INFO_DESC' => 'Description',
	'LBL_INFO_START_DT' => 'Start Date',
	'LBL_INFO_DUE_DT' => 'Due Date',
	'LBL_INFO_DURATION' => 'Duration',
	'LBL_INFO_NAME' => 'Subject',
	'LBL_INFO_RELATED_TO' => 'Related to',

	'LBL_NO_USER' => 'No match for field: Assigned to',
	'LBL_SUBJECT' => 'Subject',
	'LBL_DURATION' => 'Duration',
	'LBL_STATUS' => 'Status',
	'LBL_DATE_TIME' => 'Date and Time',


	'LBL_SETTINGS_TITLE' => 'Settings',
	'LBL_SETTINGS_DISPLAY_TIMESLOTS' => 'Display time slots in Day and Week views:',
	'LBL_SETTINGS_TIME_STARTS'=>'Start time:', 
	'LBL_SETTINGS_TIME_ENDS'=>'End time:', 
	'LBL_SETTINGS_CALLS_SHOW' => 'Show Calls:',
	'LBL_SETTINGS_TASKS_SHOW' => 'Show Tasks:', 

	'LBL_SAVE_BUTTON' => 'Save',
	'LBL_DELETE_BUTTON' => 'Delete',
	'LBL_APPLY_BUTTON' => 'Apply',
	'LBL_SEND_INVITES' => 'Save & Send Invites',
	'LBL_CANCEL_BUTTON' => 'Cancel',
	'LBL_CLOSE_BUTTON' => 'Close',

	'LBL_GENERAL_TAB' => 'Details',
	'LBL_PARTICIPANTS_TAB' => 'Invitees',
	'LBL_REPEAT_TAB' => 'Recurrence',	
	
	'LBL_REPEAT_TYPE' => 'Repeat',
	'LBL_REPEAT_INTERVAL' => 'Every',
	'LBL_REPEAT_END' => 'End',	
	'LBL_REPEAT_END_AFTER' => 'After',
	'LBL_REPEAT_OCCURRENCES' => 'recurrences',
	'LBL_REPEAT_END_BY' => 'By',	
	'LBL_REPEAT_DOW' => 'On',	
	'LBL_REPEAT_UNTIL' => 'Repeat Until',
	'LBL_REPEAT_COUNT' => 'Number of recurrences',
	'LBL_REPEAT_LIMIT_ERROR' => 'Your request was going to create more than $limit meetings.',
	
	'LBL_EDIT_ALL_RECURRENCES' => 'Edit All Recurrences',
	'LBL_REMOVE_ALL_RECURRENCES' => 'Delete All Recurrences',
	
	'LBL_DATE_END_ERROR' => 'End date is before start date',

);

$mod_list_strings = array(
	'dom_cal_weekdays'=>
		array(
			"Sun",
			"Mon",
			"Tue",
			"Wed",
			"Thu",
			"Fri",
			"Sat",
		),
	'dom_cal_weekdays_long'=>
		array(
			"Sunday",
			"Monday",
			"Tuesday",
			"Wednesday",
			"Thursday",
			"Friday",
			"Saturday",
		),
	'dom_cal_month'=>
		array(
			"",
			"Jan",
			"Feb",
			"Mar",
			"Apr",
			"May",
			"Jun",
			"Jul",
			"Aug",
			"Sep",
			"Oct",
			"Nov",
			"Dec",
		),
	'dom_cal_month_long'=>
		array(
			"",
			"January",
			"February",
			"March",
			"April",
			"May",
			"June",
			"July",
			"August",
			"September",
			"October",
			"November",
			"December",
		),
);
?>
