<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.    Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.    Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.    You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.    See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.    Please refer to the License for the specific language
 * governing these rights and limitations under the License.    Portions created
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: en_us.lang.php 32200 2008-02-29 18:44:55Z jmertic $
 * Description:    Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
global $timedate;
 
$mod_strings = array (
    'LBL_GOOD_FILE' => 'Import File Read Successfully',
    'LBL_RECORDS_SKIPPED_DUE_TO_ERROR' => 'rows were not imported due to error',
    'LBL_UPDATE_SUCCESSFULLY' => 'records updated successfully',
    'LBL_SUCCESSFULLY_IMPORTED' => 'records were created',
    'LBL_STEP_4_TITLE' => 'Step {0}: Import File',
    'LBL_STEP_5_TITLE' => 'Step {0}: View Import Results',
    'LBL_CUSTOM_ENCLOSURE' => 'Fields Qualified By:',
    'LBL_ERROR_UNABLE_TO_PUBLISH' => 'Unable to publish. There is another published Import map by the same name.',
    'LBL_ERROR_UNABLE_TO_UNPUBLISH' => 'Unable to un-publish a map owned by another user. You own an Import map by the same name.',
    'LBL_ERROR_IMPORTS_NOT_SET_UP' => 'Imports aren\'t set up for this module type',
    'LBL_IMPORT_TYPE' => 'What would you like to do with the imported data?',
    'LBL_IMPORT_BUTTON' => 'Create new records only',
    'LBL_UPDATE_BUTTON' => 'Create new records and update existing records',
    'LBL_CREATE_BUTTON_HELP' => 'Use this option to create new records. Note: Rows in the import file containing values that match the IDs of existing records will not be imported if the values are mapped to the ID field.',
    'LBL_UPDATE_BUTTON_HELP' => 'Use this option to update existing records. The data in the import file will be matched to existing records based on the record ID in the import file.',
    'LBL_ERROR_INVALID_BOOL'=>'Invalid boolean value',
    'LBL_NO_ID' => 'ID Required',
    'LBL_PRE_CHECK_SKIPPED' => 'Pre-Check skipped',
    'LBL_IMPORT_ERROR' => 'Import errors:',
    'LBL_ERROR' => 'Error',
    'LBL_NOLOCALE_NEEDED' => 'No locale conversion needed',
    'LBL_FIELD_NAME' => 'Field Name',
    'LBL_VALUE' => 'Value',
    'LBL_ROW_NUMBER' => 'Row Number',
    'LBL_NONE' => 'None',
    'LBL_REQUIRED_VALUE' => 'Required value missing',
    'LBL_ID_EXISTS_ALREADY' => 'ID already exists in this table',
    'LBL_ASSIGNED_USER' => 'If the user does not exist use the current user',
    'LBL_SHOW_HIDDEN' => 'Show fields that are not normally importable',
    'LBL_UPDATE_RECORDS' => 'Update existing records instead of importing them (No Undo)',
    'LBL_TEST'=> 'Test Import (do not save or change data)',
    'LBL_TRUNCATE_TABLE' => 'Empty table before import (delete all records)',
    'LBL_RELATED_ACCOUNTS' => 'Do not create related accounts',
    'LBL_NO_DATECHECK' => 'Skip date check (faster but will fail if any date is wrong)',
    //BEGIN SUGARCRM flav=pro ONLY
    'LBL_NO_WORKFLOW' => 'Do not run workflow during this import',
    //END SUGARCRM flav=pro ONLY
    'LBL_NO_EMAILS' => 'Do not send out Email notifications during this import',
    'LBL_NO_PRECHECK' => 'Native Format mode',
    'LBL_STRICT_CHECKS' => 'Use strict ruleset (Check Email addresses and phone numbers too)',
    'LBL_ERROR_SELECTING_RECORD' => 'Error selecting record:',
    'LBL_ERROR_DELETING_RECORD' => 'Error deleting record:',
    'LBL_NOT_SET_UP' => 'Import is not set up for this module type',
    'LBL_ARE_YOU_SURE' => 'Are you sure? This will erase all data in this module.',
    'LBL_NO_RECORD' => 'No record with this ID to update',
    'LBL_NOT_SET_UP_FOR_IMPORTS' => 'Import is not set up for this module type',
    'LBL_DEBUG_MODE' => 'Enable debugging mode',
    'LBL_ERROR_INVALID_ID' => 'ID given is too long to fit in the field (maximum length is 36 characters)',
    'LBL_ERROR_INVALID_PHONE' => 'Invalid phone number',
    'LBL_ERROR_INVALID_NAME' => 'String too long to fit in the field',
    'LBL_ERROR_INVALID_VARCHAR' => 'String too long to fit in the field',
    'LBL_ERROR_INVALID_DATE' => 'Invalid date',
    'LBL_ERROR_INVALID_DATETIME' => 'Invalid datetime',
    'LBL_ERROR_INVALID_DATETIMECOMBO' => 'Invalid datetime',
    'LBL_ERROR_INVALID_TIME' => 'Invalid time',
    'LBL_ERROR_INVALID_INT' => 'Invalid integer value',
    'LBL_ERROR_INVALID_NUM' => 'Invalid numeric value',
    'LBL_ERROR_INVALID_TIME' => 'Invalid time',
    'LBL_ERROR_INVALID_EMAIL'=>'Invalid Email address',
    'LBL_ERROR_INVALID_BOOL'=>'Invalid value (should be a 1 or 0)',
    'LBL_ERROR_INVALID_DATE'=>'Invalid date string',
    'LBL_ERROR_INVALID_USER'=>'Invalid user name or ID',
    'LBL_ERROR_INVALID_TEAM' => 'Invalid team name or ID',
    'LBL_ERROR_INVALID_ACCOUNT' => 'Invalid account name or ID',
    'LBL_ERROR_INVALID_RELATE' => 'Invalid relational field',
    'LBL_ERROR_INVALID_CURRENCY' => 'Invalid currency value',
    'LBL_ERROR_INVALID_FLOAT' => 'Invalid floating point number',
    'LBL_ERROR_NOT_IN_ENUM' => 'Value not in dropDown list. Allowed values are: ',
    'LBL_NOT_MULTIENUM' => 'Not a MultiEnum',
    'LBL_IMPORT_MODULE_NO_TYPE' => 'Import is not set up for this module type',
    'LBL_IMPORT_MODULE_NO_USERS' => 'WARNING: You have no users defined on your system.    If you import without adding users first, all records will be owned by the Administrator.',
    'LBL_IMPORT_MODULE_MAP_ERROR' => 'Unable to publish. There is another published Import Map by the same name.',
    'LBL_IMPORT_MODULE_MAP_ERROR2' => 'Unable to un-publish a map owned by another user. You own an Import Map by the same name.',
    'LBL_IMPORT_MODULE_NO_DIRECTORY' => 'The directory ',
    'LBL_IMPORT_MODULE_NO_DIRECTORY_END' => ' does not exist or is not writable',
    'LBL_IMPORT_MODULE_ERROR_NO_UPLOAD' => 'File was not uploaded successfully. It may be that the \'upload_max_filesize\' setting in your php.ini file is set to a small number',
    'LBL_IMPORT_MODULE_ERROR_LARGE_FILE' => 'File is too large. Max:',
    'LBL_IMPORT_MODULE_ERROR_LARGE_FILE_END' => 'Bytes. Change $sugar_config[\'upload_maxsize\'] in config.php',
    'LBL_MODULE_NAME' => 'Import',
    'LBL_TRY_AGAIN' => 'Try Again',
    'LBL_START_OVER' => 'Start Over',
    'LBL_ERROR' => 'Error:',
    'LBL_IMPORT_ERROR_MAX_REC_LIMIT_REACHED' => 'The import file contains {0} rows. The optimal number of rows is {1}. More rows may slow the import process. Click OK to continue importing. Click Cancel to revise and re-upload the import file.',
    'ERR_IMPORT_SYSTEM_ADMININSTRATOR'  => 'You cannot import a system administrator user',
    'ERR_MULTIPLE' => 'Multiple columns have been defined with the same field name.',
    'ERR_MISSING_REQUIRED_FIELDS' => 'Missing required fields:',
    'ERR_MISSING_MAP_NAME' => 'Missing custom mapping name',
    'ERR_SELECT_FULL_NAME' => 'You cannot select Full Name when First Name and Last Name are selected.',
    'ERR_SELECT_FILE' => 'Select a file to upload.',
    'LBL_SELECT_FILE' => 'Select file:',
    'LBL_CUSTOM' => 'Custom',
    'LBL_CUSTOM_CSV' => 'Custom comma delimited file',
    'LBL_CSV' => 'File on my computer',
    'LBL_EXTERNAL_SOURCE' => 'External application or service',
    'LBL_TAB' => 'Tab delimited file',
    'LBL_CUSTOM_DELIMITED' => 'Custom delimited file',
    'LBL_CUSTOM_DELIMITER' => 'Fields Delimited By:',
    'LBL_FILE_OPTIONS' => 'File options',
    'LBL_CUSTOM_TAB' => 'Custom tab delimited file',
    'LBL_DONT_MAP' => '-- Do not map this field --',
    'LBL_STEP_MODULE' => 'Which module do you want to import data into?',
    'LBL_STEP_1_TITLE' => 'Step 1: Select Data Source',
    'LBL_CONFIRM_TITLE' => 'Step {0}: Confirm Import File Properties',
    'LBL_CONFIRM_EXT_TITLE' => 'Step {0}: Confirm External Source Properties',
    'LBL_WHAT_IS' => 'My data is in a/an:',
    'LBL_MICROSOFT_OUTLOOK' => 'Microsoft Outlook',
    'LBL_MICROSOFT_OUTLOOK_HELP' => 'The custom mappings for Microsoft Outlook relies on the import file being comma-delimited (.csv). If your import file is tab-delimited, the mappings will not be applied as expected.',
    'LBL_ACT' => 'Act!',
    'LBL_SALESFORCE' => 'Salesforce.com',
    'LBL_MY_SAVED' => 'To use your saved import settings, select from below:',
    'LBL_PUBLISH' => 'Publish',
    'LBL_DELETE' => 'Delete',
    'LBL_PUBLISHED_SOURCES' => 'To use pre-set import settings, select from below:',
    'LBL_UNPUBLISH' => 'Un-Publish',
    'LBL_NEXT' => 'Next >',
    'LBL_BACK' => '< Back',
    'LBL_STEP_2_TITLE' => 'Step {0}: Upload Import File',
    'LBL_HAS_HEADER' => 'Header Row:',
    'LBL_NUM_1' => '1.',
    'LBL_NUM_2' => '2.',
    'LBL_NUM_3' => '3.',
    'LBL_NUM_4' => '4.',
    'LBL_NUM_5' => '5.',
    'LBL_NUM_6' => '6.',
    'LBL_NUM_7' => '7.',
    'LBL_NUM_8' => '8.',
    'LBL_NUM_9' => '9.',
    'LBL_NUM_10' => '10.',
    'LBL_NUM_11' => '11.',
    'LBL_NUM_12' => '12.',
    'LBL_NOTES' => 'Notes:',
    'LBL_NOW_CHOOSE' => 'Now choose that file to import:',
    'LBL_IMPORT_OUTLOOK_TITLE' => 'Microsoft Outlook 98 and 2000 can export data in the <b>Comma Separated Values</b> format, which can be used to import data into the system. To export your data from Outlook, follow the steps below:',
    'LBL_OUTLOOK_NUM_1' => 'Start <b>Outlook</b>',
    'LBL_OUTLOOK_NUM_2' => 'Select the <b>File</b> menu, then the <b>Import and Export ...</b> menu option',
    'LBL_OUTLOOK_NUM_3' => 'Choose <b>Export to a file</b> and click Next',
    'LBL_OUTLOOK_NUM_4' => 'Choose <b>Comma Separated Values (Windows)</b> and click <b>Next</b>.<br>    Note: You may be prompted to install the export component',
    'LBL_OUTLOOK_NUM_5' => 'Select the <b>Contacts</b> folder and click <b>Next</b>. You can select different contacts folders if your contacts are stored in multiple folders',
    'LBL_OUTLOOK_NUM_6' => 'Choose a filename and click <b>Next</b>',
    'LBL_OUTLOOK_NUM_7' => 'Click <b>Finish</b>',
    'LBL_IMPORT_SF_TITLE' => 'Salesforce.com can export data in the <b>Comma Separated Values</b> format, which can be used to import data into the system. To export your data from Salesforce.com, follow the steps below:',
    'LBL_SF_NUM_1' => 'Open your browser, go to http://www.salesforce.com, and login with your email address and password',
    'LBL_SF_NUM_2' => 'Click on the <b>Reports</b> tab on the top menu',
    'LBL_SF_NUM_3' => '<b>To export Accounts:</b> Click on the <b>Active Accounts</b> link<br><b>To export Contacts:</b> Click on the <b>Mailing List</b> link',
    'LBL_SF_NUM_4' => 'On <b>Step 1: Select your report type</b>, select <b>Tabular Report</b> click <b>Next</b>',
    'LBL_SF_NUM_5' => 'On <b>Step 2: Select the report columns</b>, choose the columns you want to export and click <b>Next</b>',
    'LBL_SF_NUM_6' => 'On <b>Step 3: Select the information to summarize</b>, just click <b>Next</b>',
    'LBL_SF_NUM_7' => 'On <b>Step 4: Order the report columns</b>, just click <b>Next</b>',
    'LBL_SF_NUM_8' => 'On <b>Step 5: Select your report criteria</b>, under <b>Start Date</b>, choose a date far enough in the past to include all your Accounts. You can also export a subset of Accounts using more advanced criteria. When you are done, click <b>Run Report</b>',
    'LBL_SF_NUM_9' => 'A report will be generated, and the page will display <b>Report Generation Status: Complete.</b> Now click <b>Export to Excel</b>',
    'LBL_SF_NUM_10' => 'On <b>Export Report:</b>, for <b>Export File Format:</b>, choose <b>Comma Delimited .csv</b>. Click <b>Export</b>.',
    'LBL_SF_NUM_11' => 'A dialog will pop up for you to save the export file to your computer.',
    'LBL_IMPORT_ACT_TITLE' => 'Act! can export data in the <b>Comma Separated Values</b> format, which can be used to import data into the system. To export your data from Act!, follow the steps below:',
    'LBL_ACT_NUM_1' => 'Launch <b>ACT!</b>',
    'LBL_ACT_NUM_2' => 'Select the <b>File</b> menu, the <b>Data Exchange</b> menu option, then the <b>Export...</b> menu option',
    'LBL_ACT_NUM_3' => 'Select the file type <b>Text-Delimited</b>',
    'LBL_ACT_NUM_4' => 'Choose a filename and location for the exported data and click <b>Next</b>',
    'LBL_ACT_NUM_5' => 'Select <b>Contacts records only</b>',
    'LBL_ACT_NUM_6' => 'Click the <b>Options...</b> button',
    'LBL_ACT_NUM_7' => 'Select <b>Comma</b> as the field separator character',
    'LBL_ACT_NUM_8' => 'Check the <b>Yes, export field names</b> checkbox and click <b>OK</b>',
    'LBL_ACT_NUM_9' => 'Click <b>Next</b>',
    'LBL_ACT_NUM_10' => 'Select <b>All Records</b> and then click <b>Finish</b>',
    'LBL_IMPORT_CUSTOM_TITLE' => 'Many applications allow you to export data into a <b>Comma Delimited text file (.csv)</b> by following these general steps:',
    'LBL_CUSTOM_NUM_1' => 'Launch the application and open the data file',
    'LBL_CUSTOM_NUM_2' => 'Select the <b>Save As...</b> or <b>Export...</b> menu option',
    'LBL_CUSTOM_NUM_3' => 'Save the file in a <b>CSV</b> or <b>Comma Separated Values</b> format',
    'LBL_IMPORT_TAB_TITLE' => 'Many applications allow you to export data into a <b>Tab Delimited text file (.tsv or .tab)</b> by following these general steps:',
    'LBL_TAB_NUM_1' => 'Launch the application and open the data file',
    'LBL_TAB_NUM_2' => 'Select the <b>Save As...</b> or <b>Export...</b> menu option',
    'LBL_TAB_NUM_3' => 'Save the file in a <b>TSV</b> or <b>Tab Separated Values</b> format',
    'LBL_STEP_3_TITLE' => 'Step {0}: Confirm Field Mappings',
    'LBL_STEP_DUP_TITLE' => 'Step {0}: Check for Possible Duplicates',
    'LBL_SELECT_FIELDS_TO_MAP' => 'In the list below, select the fields in the import file that should be imported into each field in the system. When you are finished, click <b>Next</b>:',
    'LBL_DATABASE_FIELD' => 'Module Field',
    'LBL_HEADER_ROW' => 'Header Row',
    'LBL_HEADER_ROW_OPTION_HELP' => 'Select if the top row of the import file is a Header Row containing field labels.',
    'LBL_ROW' => 'Row',
    'LBL_SAVE_AS_CUSTOM' => 'Save as Custom Mapping:',
    'LBL_SAVE_AS_CUSTOM_NAME' => 'Custom Mapping Name:',
    'LBL_CONTACTS_NOTE_1' => 'Either Last Name or Full Name must be mapped.',
    'LBL_CONTACTS_NOTE_2' => 'If Full Name is mapped, then First Name and Last Name are ignored.',
    'LBL_CONTACTS_NOTE_3' => 'If Full Name is mapped, then the data in Full Name will be split into First Name and Last Name when inserted into the database.',
    'LBL_CONTACTS_NOTE_4' => 'Fields ending in Address Street 2 and Address Street 3 are concatenated together with the main Address Street Field when inserted into the database.',
    'LBL_ACCOUNTS_NOTE_1' => 'Fields ending in Address Street 2 and Address Street 3 are concatenated together with the main Address Street Field when inserted into the database.',
    'LBL_REQUIRED_NOTE' => 'Required Field(s): ',
    'LBL_IMPORT_NOW' => 'Import Now',
    'LBL_' => '',
    'LBL_CANNOT_OPEN' => 'Cannot open the imported file for reading',
    'LBL_NOT_SAME_NUMBER' => 'There were not the same number of fields per line in your file',
    'LBL_NO_LINES' => 'There were no lines in your import file.  Please try again.',
    'LBL_FILE_ALREADY_BEEN_OR' => 'The import file has already been processed or does not exist',
    'LBL_SUCCESS' => 'Success:',
	'LBL_FAILURE' => 'Import Failed:',
    'LBL_SUCCESSFULLY' => 'Successfully imported',
    'LBL_LAST_IMPORT_UNDONE' => 'The import was undone.',
    'LBL_NO_IMPORT_TO_UNDO' => 'There was no import to undo.',
    'LBL_FAIL' => 'Fail:',
    'LBL_RECORDS_SKIPPED' => 'Records skipped because they were missing one or more required fields',
    'LBL_IDS_EXISTED_OR_LONGER' => 'Records skipped because the id\'s either existed or were longer than 36 characters',
    'LBL_RESULTS' => 'Results',
    'LBL_IMPORT_MORE' => 'Import Again',
    'LBL_FINISHED' => 'Return to ',
    'LBL_UNDO_LAST_IMPORT' => 'Undo Import',
    'LBL_LAST_IMPORTED'=>'Created',
    'ERR_MULTIPLE_PARENTS' => 'You can only have one Parent ID defined',
    'LBL_DUPLICATES' => 'Duplicates Found',
    'LNK_DUPLICATE_LIST' => 'Download list of duplicates',
    'LNK_ERROR_LIST' => 'Download list of errors',
    'LNK_RECORDS_SKIPPED_DUE_TO_ERROR' => 'Download list of rows that were not imported',
    'LBL_UNIQUE_INDEX' => 'Choose index for duplicate comparison',
    'LBL_VERIFY_DUPS' => 'To check for existing records matching data in the import file, select the fields to check.',
    'LBL_INDEX_USED' => 'Fields to Check:',
    'LBL_INDEX_NOT_USED' => 'Available Fields:',
    'LBL_IMPORT_MODULE_ERROR_NO_MOVE' => 'File was not successfully uploaded.    Check the file permissions in your Sugar installation cache directory.',
    'LBL_IMPORT_FIELDDEF_ID' => 'Unique ID number',
    'LBL_IMPORT_FIELDDEF_RELATE' => 'Name or ID',
    'LBL_IMPORT_FIELDDEF_PHONE' => 'Phone Number',
    'LBL_IMPORT_FIELDDEF_TEAM_LIST' => 'Team Name or ID',
    'LBL_IMPORT_FIELDDEF_NAME' => 'Any Text',
    'LBL_IMPORT_FIELDDEF_VARCHAR' => 'Any Text',
    'LBL_IMPORT_FIELDDEF_TEXT' => 'Any Text',
    'LBL_IMPORT_FIELDDEF_TIME' => 'Time',
    'LBL_IMPORT_FIELDDEF_DATE' => 'Date',
    'LBL_IMPORT_FIELDDEF_DATETIME' => 'Datetime',
    'LBL_IMPORT_FIELDDEF_ASSIGNED_USER_NAME' => 'User Name or ID',
    'LBL_IMPORT_FIELDDEF_BOOL' => '\'0\' or \'1\'',
    'LBL_IMPORT_FIELDDEF_ENUM' => 'List',
    'LBL_IMPORT_FIELDDEF_EMAIL' => 'EMail Address',
    'LBL_IMPORT_FIELDDEF_INT' => 'Numeric (No Decimal)',
    'LBL_IMPORT_FIELDDEF_DOUBLE' => 'Numeric (No Decimal)',
    'LBL_IMPORT_FIELDDEF_NUM' => 'Numeric (No Decimal)',
    'LBL_IMPORT_FIELDDEF_CURRENCY' => 'Numeric (Decimal Allowed)',
    'LBL_IMPORT_FIELDDEF_FLOAT' => 'Numeric (Decimal Allowed)',
    'LBL_DATE_FORMAT' => 'Date Format:',
    'LBL_TIME_FORMAT' => 'Time Format:',
    'LBL_TIMEZONE' => 'Time Zone:',
    'LBL_ADD_ROW' => 'Add Field',
    'LBL_REMOVE_ROW' => 'Remove Field',
    'LBL_DEFAULT_VALUE' => 'Default Value',
    'LBL_SHOW_ADVANCED_OPTIONS' => 'View Import File Properties',
    'LBL_HIDE_ADVANCED_OPTIONS' => 'Hide Import File Properties',
    'LBL_SHOW_NOTES' => 'View Notes',
    'LBL_HIDE_NOTES' => 'Hide Notes',
    'LBL_SHOW_PREVIEW_COLUMNS' => 'Show Preview Columns',
    'LBL_HIDE_PREVIEW_COLUMNS' => 'Hide Preview Columns',
    'LBL_SAVE_MAPPING_AS' => 'To save the import settings, provide a name for the saved settings:',
    'LBL_OPTION_ENCLOSURE_QUOTE' => 'Single Quote (\')',
    'LBL_OPTION_ENCLOSURE_DOUBLEQUOTE' => 'Double Quote (")',
    'LBL_OPTION_ENCLOSURE_NONE' => 'None',
    'LBL_OPTION_ENCLOSURE_OTHER' => 'Other:',
    'LBL_IMPORT_COMPLETE' => 'Exit',
    'LBL_IMPORT_COMPLETED' => 'Import Completed',
    'LBL_IMPORT_ERROR' => 'Import Errors Occurred',
    'LBL_IMPORT_RECORDS' => 'Importing Records',
    'LBL_IMPORT_RECORDS_OF' => 'of',
    'LBL_IMPORT_RECORDS_TO' => 'to',
    'LBL_CURRENCY' => 'Currency:',
	'LBL_CURRENCY_SIG_DIGITS' => 'Currency Significant Digits',
	'LBL_LOCALE_EXAMPLE_NAME_FORMAT' => 'Example',
    'LBL_NUMBER_GROUPING_SEP' => '1000s separator:',
    'LBL_DECIMAL_SEP' => 'Decimal symbol:',
    'LBL_LOCALE_DEFAULT_NAME_FORMAT' => 'Name Display Format',
    'LBL_LOCALE_EXAMPLE_NAME_FORMAT' => 'Example',
    'LBL_LOCALE_NAME_FORMAT_DESC' => '<i>"s" Salutation, "f" First Name, "l" Last Name</i>',
    'LBL_CHARSET' => 'File Encoding:',
    'LBL_MY_SAVED_HELP' => 'Use this option to apply your pre-set import settings, including import properties, mappings, and any duplicate check settings, to this import.<br><br>Click <b>Delete</b> to delete a mapping for all users.',
    'LBL_MY_SAVED_ADMIN_HELP' => 'Use this option to apply your pre-set import settings, including import properties, mappings, and any duplicate check settings, to this import.<br><br>Click <b>Publish</b> to make the mapping available to other users.<br>Click <b>Un-Publish</b> to make the mapping unavailable to other users.<br>Click <b>Delete</b> to delete a mapping for all users.',
    'LBL_MY_PUBLISHED_HELP' => 'Use this option to apply pre-set import settings, including import properties, mappings, and any duplicate check settings, to this import.',
    'LBL_ENCLOSURE_HELP' => '<p>The <b>qualifier character</b> is used to enclose the intended field content, including any characters that are used as delimiters.<br><br>Example: If the delimiter is a comma (,) and the qualifier is a quotation mark ("),<br><b>"Cupertino, California"</b> is imported into one field in the application and appears as <b>Cupertino, California</b>.<br>If there are no qualifier characters, or if a different character is the qualifier,<br><b>"Cupertino, California"</b> is imported into two adjacent fields as <b>"Cupertino</b> and <b>"California"</b>.<br><br>Note: The import file might not contain any qualifier characters.<br>The default qualifier character for comma- and tab- delimited files created in Excel is a quotation mark.</p>',
    'LBL_DELIMITER_COMMA_HELP' => 'Use this option to select and upload a spreadsheet file containing the data that you would like to import. Examples: comma-delimited .csv file or export file from Microsoft Outlook.',
    'LBL_DELIMITER_TAB_HELP' => 'Select this option if the character that separates the fields in the import file is a <b>TAB</b>, and the file extension is .txt.',
    'LBL_DELIMITER_CUSTOM_HELP' => 'Select this option if the character that separates the fields in the import file is neither a comma or a TAB, and type the character in the adjacent field.',
    'LBL_DATABASE_FIELD_HELP' => 'This column displays all of the fields in the module. Select a field to map to the data in the import file rows.',
    'LBL_HEADER_ROW_HELP' => 'This column displays the labels in the header row of the import file.',
    'LBL_DEFAULT_VALUE_HELP' => 'Indicate a value to use for the field in the created or updated record if the field in the import file contains no data.',
    'LBL_ROW_HELP' => 'This column displays the data in the first non-header row of the import file. If the header row labels are appearing in this column, click Back to specify the header row in the Import File Properties.',
    'LBL_SAVE_MAPPING_HELP' => 'Enter a name to save the import settings, including the field mappings and indexes used for the duplicate check. Saved import settings can be used for future imports.',
    'LBL_IMPORT_FILE_SETTINGS_HELP' => 'During the upload of your import file, some file properties might have been automatically detected. View and manage these properties, as<br> necessary. Note: The settings provided here pertain to this import<br> and will not override your overall User Settings.',
	'LBL_IMPORT_FILE_SETTINGS' => 'Import File Settings',
    'LBL_VERIFY_DUPLCATES_HELP' => 'Find existing records in the system that could be considered duplicates of the records about to be imported  by performing a duplicate check for matching data.  Fields dragged into the "Check Data" column will be used for the duplicate check.  The rows in your import file containing matching data will be listed within the next page, and you will be able to select which rows to import',
    'LBL_IMPORT_STARTED' => 'Import Started:',
    'LBL_IMPORT_FILE_SETTINGS' => 'Import File Settings',
    'LBL_RECORD_CANNOT_BE_UPDATED' => 'The record could not be updated due to a permissions issue',
    'LBL_DELETE_MAP_CONFIRMATION' => 'Are you sure you want to delete this saved set of import settings?',
    'LBL_THIRD_PARTY_CSV_SOURCES' => 'If the import file data was exported from any of the following sources, select which one.',
    'LBL_THIRD_PARTY_CSV_SOURCES_HELP' => 'Select the source to automatically apply custom mappings in order to simplify the mapping process (next step).',
    'LBL_EXTERNAL_SOURCE_HELP' => 'Use this option to import data directly from an external application or service, such as Gmail.',
    'LBL_EXAMPLE_FILE' => 'Download Import File Template',
    'LBL_CONFIRM_IMPORT' => 'You have selected to update records during the import process. Updates made to existing records cannot be undone. However, records created during the import process can be undone (deleted), if desired. Click Cancel to select to create new records only, or click OK to continue.',
    'LBL_CONFIRM_MAP_OVERRIDE' => 'Warning: You have already selected a custom mapping for this import, do you want to continue?',
    'LBL_EXTERNAL_FIELD' => 'External Field',
    'LBL_SAMPLE_URL_HELP' => 'Download a sample import file containing a header row of the module fields. The file can be used as a template to create an import file containing the data that you would like to import.',
    'LBL_AUTO_DETECT_ERROR' => 'The field delimiter and qualifier in the import file could not be detected. Please verify the settings in the Import File Properties.',
    'LBL_MIME_TYPE_ERROR_1' => 'The selected file does not appear to contain a delimited list. Please check the file type. We recommend comma-delimited files (.csv).',
    'LBL_MIME_TYPE_ERROR_2' => 'To proceed with importing the selected file, click Cancel. To upload a new file, click "Try Again"',
    'LBL_FIELD_DELIMETED_HELP' => 'The field delimiter specifies the character used to separate the field columns.',
    'LBL_FILE_UPLOAD_WIDGET_HELP' => 'Select a file containing data that is separated by a delimiter, such as a comma- or tab- delimited file.  Files of the type .csv are recommended.',
    'LBL_EXTERNAL_ERROR_NO_SOURCE' => 'Unable to retrieve source adapter, please try again later.',
    'LBL_EXTERNAL_ERROR_FEED_CORRUPTED' => 'Unable to retrieve external feed, please try again later.',
    'LBL_ADD_FIELD_HELP' => 'Use this option to add a value to a field in all records created and/or updated. Select the field and then enter or select a value for that field in the Default Value column.',
    'LBL_MISSING_HEADER_ROW' => 'No Header Row Found',
    'LBL_CANCEL' => 'Cancel',
    'LBL_SELECT_DS_INSTRUCTION' => 'Ready to start importing? Select the source of the data that you would like to import.',
    'LBL_SELECT_UPLOAD_INSTRUCTION' => 'Select a file on your computer that contains the data that you would like to import, or download the template to get a head start on creating the import file.',
    'LBL_SELECT_PROPERTY_INSTRUCTION' => 'Here is how the the first several rows of the import file appear with the detected file properties. If a header row was detected, it is displayed in the top row of the table. View the import file properties to make changes to the detected properties and to set additional properties. Updating the settings will update the data appearing in the table.',
    'LBL_SELECT_MAPPING_INSTRUCTION' => 'The table below contains all of the fields in the module that can be mapped to the data in the import file. If the file contains a header row, the columns in the file have been mapped to matching fields. Check the mappings to make sure that they are what you expect, and make changes, as necessary. To help you check the mappings, Row 1 displays the data in the file. Be sure to map to all of the required fields (noted by an asterisk).',
    'LBL_SELECT_DUPLICATE_INSTRUCTION' => 'To avoid creating duplicate records, select which of the mapped fields you would like to use to perform a duplicate check while data is being imported. Values within existing records in the selected fields will be checked against the data in the import file. If matching data is found, the rows in the import file containing the data will be displayed along with the import results (next page). You will then be able to select which of these rows to continue importing.',
    'LBL_EXT_SOURCE_SIGN_IN' => 'Sign In',
    'LBL_DUP_HELP' => 'Here are the rows in the import file that were not imported because they contain data that matches values in existing records based on the duplicate check. The data that matches is highlighted. To re-import these rows, download the list, make changes and click <b>Import Again</b>.',
    'LBL_DESELECT' => 'deselect',
    'LBL_SUMMARY' => 'Summary',
    'LBL_ERROR_HELP' => 'Here are the rows in the import file that were not imported due to errors. To re-import these rows, download the list, make changes and click <b>Import Again</b>',
    'LBL_EXTERNAL_MAP_HELP' => 'The table below contains the fields in the external source and the module fields to which they are mapped. Check the mappings to make sure that they are what you expect, and make changes, as necessary. Be sure to map to all of the required fields (noted by an asterisk).',
    'LBL_EXTERNAL_MAP_SUB_HELP' => 'Click <b>Import Now</b> to import and duplicates will be identified based on matching names or email addresses.',
    'LBL_EXTERNAL_FIELD_TOOLTIP' => 'This column displays the fields in the external source containing data that will be used to create new records.',
    'LBL_EXTERNAL_DEFAULT_TOOPLTIP' => 'Indicate a value to use for the field in the created record if the field in the external source contains no data.',
    'LBL_EXTERNAL_ASSIGNED_TOOLTIP' => 'To assign the new records to a user other than yourself, use the Default Value column to select a different user.',
    'LBL_EXTERNAL_TEAM_TOOLTIP' => 'To assign the new records to teams other than your default team(s), use the Default Value column to select different teams.'
);
?>
