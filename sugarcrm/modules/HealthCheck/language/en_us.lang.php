<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 * English language file for Health Check
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees are strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: en_us.lang.php 55130 2010-03-08 20:46:39Z jmertic $

$mod_strings = array(
    'LBL_MODULE_NAME' => 'Health Check',
    'LBL_MODULE_NAME_SINGULAR' => 'Health Check',
    'LBL_MODULE_TITLE' => 'Health Check: Home',
    'LBL_SEARCH_FORM_TITLE' => 'Health Check Search',
    'LBL_LIST_FORM_TITLE' => 'Health Check',
    'LBL_LIST_SUBJECT' => 'Subject',
    'LBL_LIST_CONTACT' => 'Contact',
    'LBL_LIST_RELATED_TO' => 'Related to',
    'LBL_LIST_DATE' => 'Date',
    'LBL_LIST_TIME' => 'Start Time',
    'LBL_LIST_CLOSE' => 'Close',
    'LBL_SUBJECT' => 'Subject:',
    'LBL_STATUS' => 'Status:',
    'LBL_LOCATION' => 'Location:',
    'LBL_DATE_TIME' => 'Start Date & Time:',
    'LBL_DATE' => 'Start Date:',
    'LBL_TIME' => 'Start Time:',
    'LBL_DURATION' => 'Duration:',
    'LBL_HOURS_MINS' => '(hours/minutes)',
    'LBL_CONTACT_NAME' => 'Contact Name: ',
    'LBL_MEETING' => 'Meeting:',
    'LBL_DESCRIPTION_INFORMATION' => 'Description Information',
    'LBL_DESCRIPTION' => 'Description:',
    'LBL_COLON' => ':',
    'LBL_DEFAULT_STATUS' => 'Planned',
    'LNK_NEW_CALL' => 'Log Call',
    'LNK_NEW_MEETING' => 'Schedule Meeting',
    'LNK_NEW_TASK' => 'Create Task',
    'LNK_NEW_NOTE' => 'Create Note or Attachment',
    'LNK_NEW_EMAIL' => 'Archive Email',
    'LNK_CALL_LIST' => 'Calls',
    'LNK_MEETING_LIST' => 'Meetings',
    'LNK_TASK_LIST' => 'Tasks',
    'LNK_NOTE_LIST' => 'Notes',
    'LNK_EMAIL_LIST' => 'Emails',
    'ERR_DELETE_RECORD' => 'A record number must be specified to delete the account.',
    'NTC_REMOVE_INVITEE' => 'Are you sure you want to remove this invitee from the meeting?',
    'LBL_INVITEE' => 'Invitees',
    'LBL_LIST_DIRECTION' => 'Direction',
    'LBL_DIRECTION' => 'Direction',
    'LNK_NEW_APPOINTMENT' => 'New Appointment',
    'LNK_VIEW_CALENDAR' => 'Today',
    'LBL_OPEN_ACTIVITIES' => 'Open Activities',
    'LBL_Health Check' => 'Health Check',
    'LBL_UPCOMING' => 'My Upcoming Appointments',
    'LBL_TODAY' => 'through ',
    'LBL_NEW_TASK_BUTTON_TITLE' => 'Create Task',
    'LBL_NEW_TASK_BUTTON_KEY' => 'N',
    'LBL_NEW_TASK_BUTTON_LABEL' => 'Create Task',
    'LBL_SCHEDULE_MEETING_BUTTON_TITLE' => 'Schedule Meeting',
    'LBL_SCHEDULE_MEETING_BUTTON_KEY' => 'M',
    'LBL_SCHEDULE_MEETING_BUTTON_LABEL' => 'Schedule Meeting',
    'LBL_SCHEDULE_CALL_BUTTON_TITLE' => 'Log Call',
    'LBL_SCHEDULE_CALL_BUTTON_KEY' => 'C',
    'LBL_SCHEDULE_CALL_BUTTON_LABEL' => 'Log Call',
    'LBL_NEW_NOTE_BUTTON_TITLE' => 'Create Note or Attachment',
    'LBL_NEW_NOTE_BUTTON_KEY' => 'T',
    'LBL_NEW_NOTE_BUTTON_LABEL' => 'Create Note or Attachment',
    'LBL_TRACK_EMAIL_BUTTON_TITLE' => 'Archive Email',
    'LBL_TRACK_EMAIL_BUTTON_KEY' => 'K',
    'LBL_TRACK_EMAIL_BUTTON_LABEL' => 'Archive Email',
    'LBL_LIST_STATUS' => 'Status',
    'LBL_LIST_DUE_DATE' => 'Due Date',
    'hasStudioHistory' => '%s has studio history',
    'hasExtensions' => '%s has extensions: %s',
    'hasCustomVardefs' => '%s has custom vardefs',
    'hasCustomLayoutdefs' => '%s has custom layoutdefs',
    'hasCustomViewdefs' => '%s has custom viewdefs',
    // C
    'notStockModule' => '% is not a stock module',
    // D
    'toBeRunAsBWC' => '%s to be run as BWC',
    'unknownFileViews' => "Unknown file views present - %s is not MB module",
    'nonEmptyFormFile' => "Non-empty form file %s - %s is not MB module",
    'isNotMBModule' => "Unknown file %s - %s is not MB module",
    'badVardefsKey' => "Bad vardefs - key %s, name %s",
    'badVardefsRelate' => "Bad vardefs - relate field %s has empty `module`",
    'badVardefsLink' => "Bad vardefs - link %s refers to invalid relationship",
    'vardefHtmlFunction' => "Vardef HTML function in %s",
    'badMd5' => "Bad md5 for %s",
    'unknownFile' => "Unknown file %s/%s",
    // E
    'vendorFilesInclusion' => "Vendor files inclusion found, for files that have been moved to vendor/:\r\n%s",
    'badModule' => "Bad module %s - not in beanList and not in filesystem",
    'logicHookAfterUIFrame' => "Logic hook after_ui_frame detected",
    'logicHookAfterUIFooter' => "Logic hook after_ui_footer detected",
    'incompatIntegration' => "Incompatible Integration - %s %s",
    'hasCustomViews' => "%s has custom views",
    'hasCustomViewsModDir' => "%s has custom views in module dir",
    'extensionDir' => "Extension dir %s detected",
    'foundCustomCode' => "Found customCode %s in %s",
    'maxFieldsView' => "Max fields - Found more than %s fields (%s) in %s",
    'subPanelWithFunction' => "Found 'get_subpanel_data' with 'function:' value in %s",
    'badSubpanelLink' => "Bad subpanel link %s in %s",
    'unknownWidgetClass' => "Unknown widget class detected: %s for %s",
    'unknownField' => "Unknown fields handled by CRYS-36, so no more checks here",
    'badHookFile' => "Bad hook file in %s: %s",
    'byRefInHookFile' => "By-ref parameter in hook file %s function %s",
    'incompatModule' => "Incompatible module %s",
    'subpanelLinkNonExistModule' => 'Found subpanel with link to non-existing module: %s',
    'badVardefsKeyCustom' => "Bad vardefs - key %s, name %s",
    'badVardefsRelateCustom' => "Bad vardefs - relate field %s has empty `module`",
    'badVardefsLinkCustom' => "Bad vardefs - link %s refers to invalid relationship",
    'vardefHtmlFunctionCustom' => "Vardef HTML function in %s",
    'badVardefsCustom' => "Bad vardefs - %s refers to bad subfield %s",
    'inlineHtmlCustom' => 'Inline HTML found in %s on line %s',
    'foundEchoCustom' => 'Found "echo" in %s on line %s',
    'foundPrintCustom' => 'Found "print" in %s on line %s',
    'foundDieExitCustom' => 'Found "die/exit" in %s on line %s',
    'foundPrintRCustom' => 'Found "print_r" in %s on line %s',
    'foundVarDumpCustom' => 'Found "var_dump" in %s on line %s',
    'foundOutputBufferingCustom' => 'Found output buffering (%s) in %s on line %s',
    // F
    'missingFile' => "Missing file: %s",
    'md5Mismatch' => "md5 mismatch for %s, expected %s",
    'sameModuleName' => "Custom module with the same name as new Sugar7 module: %s",
    'fieldTypeMissing' => "Field type missing in module %s: %s",
    'typeChange' => "Type change in %s for field %s: from %s to %s",
    'thisUsage' => '$this usage in %s',
    'badVardefs' => "Bad vardefs - %s refers to bad subfield %s",
    'inlineHtml' => 'Inline HTML found in %s on line %s',
    'foundEcho' => 'Found "echo" in %s on line %s',
    'foundPrint' => 'Found "print" in %s on line %s',
    'foundDieExit' => 'Found "die/exit" in %s on line %s',
    'foundPrintR' => 'Found "print_r" in %s on line %s',
    'foundVarDump' => 'Found "var_dump" in %s on line %s',
    'foundOutputBuffering' => 'Found output buffering (%s) in %s on line %s',

);

?>
