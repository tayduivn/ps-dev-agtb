<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

$mod_strings = array (
  'LBL_MODULE_NAME' => 'Tasks',
  'LBL_MODULE_NAME_SINGULAR' => 'Task',
  'LBL_TASK' => 'Tasks: ',
  'LBL_MODULE_TITLE' => ' Tasks: Home',
  'LBL_SEARCH_FORM_TITLE' => ' Task Search',
  'LBL_LIST_FORM_TITLE' => ' Task List',
  'LBL_NEW_FORM_TITLE' => ' Create Task',
  'LBL_NEW_FORM_SUBJECT' => 'Subject:',
  'LBL_NEW_FORM_DUE_DATE' => 'Due date:',
  'LBL_NEW_FORM_DUE_TIME' => 'Due time:',
  'LBL_NEW_TIME_FORMAT' => '(24:00)',
  'LBL_LIST_CLOSE' => 'Close',
  'LBL_LIST_SUBJECT' => 'Subject',
  'LBL_LIST_CONTACT' => 'Contact',
  'LBL_LIST_PRIORITY' => 'Priority',
  'LBL_LIST_RELATED_TO' => 'Related to',
  'LBL_LIST_DUE_DATE' => 'Due date',
  'LBL_LIST_DUE_TIME' => 'Due time',
  'LBL_SUBJECT' => 'Subject:',
  'LBL_STATUS' => 'Status:',
  'LBL_DUE_DATE' => 'Due date:',
  'LBL_DUE_TIME' => 'Due time:',
  'LBL_PRIORITY' => 'Priority:',
  'LBL_COLON' => ':',
  'LBL_DUE_DATE_AND_TIME' => 'Due date & time:',
  'LBL_START_DATE_AND_TIME' => 'Start date & time:',
  'LBL_START_DATE' => 'Start date:',
  'LBL_LIST_START_DATE' => 'Start date',
  'LBL_START_TIME' => 'Start time:',
  'LBL_LIST_START_TIME' => 'Start time',
  'DATE_FORMAT' => '(yyyy-mm-dd)',
  'LBL_NONE' => 'None',
  'LBL_CONTACT' => 'Contact:',
  'LBL_EMAIL_ADDRESS' => 'Email address:',
  'LBL_PHONE' => 'Phone:',
  'LBL_EMAIL' => 'Email address:',
  'LBL_DESCRIPTION_INFORMATION' => 'Description information',
  'LBL_DESCRIPTION' => 'Description:',
  'LBL_NAME' => 'Name:',
  'LBL_CONTACT_NAME' => 'Contact name ',
  'LBL_LIST_COMPLETE' => 'Complete:',
  'LBL_LIST_STATUS' => 'Status',
  'LBL_DATE_DUE_FLAG' => 'No due date',
  'LBL_DATE_START_FLAG' => 'No start date',
  'ERR_DELETE_RECORD' => 'You must specify a record number to delete the Contact.',
  'ERR_INVALID_HOUR' => 'Please enter an hour between 0 and 24',
  'LBL_DEFAULT_PRIORITY' => 'Medium',
  'LBL_LIST_MY_TASKS' => 'My open tasks',
  'LNK_NEW_TASK' => 'Create task',
  'LNK_TASK_LIST' => 'View tasks',
  'LNK_IMPORT_TASKS' => 'Import tasks',
  'LBL_CONTACT_FIRST_NAME'=>'Contact first name',
  'LBL_CONTACT_LAST_NAME'=>'Contact last name',
  'LBL_LIST_ASSIGNED_TO_NAME' => 'Assigned user',
  'LBL_ASSIGNED_TO_NAME'=>'Assigned to:',
  'LBL_LIST_DATE_MODIFIED' => 'Date modified',
  'LBL_CONTACT_ID' => 'Contact ID:',
  'LBL_PARENT_ID' => 'Parent ID:',
  'LBL_CONTACT_PHONE' => 'Contact phone:',
  'LBL_PARENT_NAME' => 'Parent type:',
  'LBL_ACTIVITIES_REPORTS' => 'Activities report',
  'LBL_EDITLAYOUT' => 'Edit layout' /*for 508 compliance fix*/,
  'LBL_TASK_INFORMATION' => 'Overview',
  'LBL_HISTORY_SUBPANEL_TITLE' => 'Notes',
  //For export labels
  'LBL_DATE_DUE' => 'Date due',
  'LBL_EXPORT_ASSIGNED_USER_NAME' => 'Assigned user name',
  'LBL_EXPORT_ASSIGNED_USER_ID' => 'Assigned user ID',
  'LBL_EXPORT_MODIFIED_USER_ID' => 'Modified by ID',
  'LBL_EXPORT_CREATED_BY' => 'Created by ID',
  'LBL_EXPORT_PARENT_TYPE' => 'Related to module',
  'LBL_EXPORT_PARENT_ID' => 'Related to ID',
  'LBL_TASK_CLOSE_SUCCESS' => 'Task closed successfully.',
  'LBL_ASSIGNED_USER' => 'Assigned to',

    'LBL_NOTES_SUBPANEL_TITLE' => 'Notes',

    // Help Text
    // List View Help Text
    'LBL_HELP_RECORDS' => 'The {{plural_module_name}} module consists of flexible actions, to-do items, or other type of activity which requires completion. {{module_name}} records can be related to one record in most modules via the flex relate field and can also be related to a single {{contact_module}}. There are various ways you can create {{plural_module_name}} in Sugar such as via the {{plural_module_name}} module, duplication, importing {{plural_module_name}}, etc. Once the {{module_name}} record is created, you can view and edit information pertaining to the {{module_name}} via the {{plural_module_name}} record view. Depending on the details on the {{module_name}}, you may also be able to view and edit the {{module_name}} information via the Calendar module. Each {{module_name}} record may then relate to other Sugar records such as {{accounts_module}}, {{contacts_module}}, {{opportunities_module}}, and many others.',

    // Record View Help Text
    'LBL_HELP_RECORD' => 'The {{plural_module_name}} module consists of flexible actions, to-do items, or other type of activity which requires completion.

- Edit this record\'s fields by clicking an individual field or the Edit button.
- View or modify links to other records in the subpanels by toggling the bottom left pane to "Data View".
- Make and view user comments and record change history in the {{activitystream_module}} by toggling the bottom left pane to "Activity Stream".
- Follow or favorite this record using the icons to the right of the record name.
- Additional actions are available in the dropdown actions menu to the right of the Edit button.',

    // Create View Help Text
    'LBL_HELP_CREATE' => 'The {{plural_module_name}} module consists of flexible actions, to-do items, or other type of activity which requires completion.

To create a {{module_name}}:
1. Provide values for the fields as desired.
 - Fields marked "Required" must be completed prior to saving.
 - Click "Show More" to expose additional fields if necessary.
2. Click "Save" to finalize the new record and return to the previous page.
 - Choose "Save and view" to open the new {{module_name}} in record view.
 - Choose "Save and create new" to immediately create another new {{module_name}}.',

);
