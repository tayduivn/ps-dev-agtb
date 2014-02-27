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
  'LBL_MODULE_NAME' => 'Targets',
  'LBL_MODULE_NAME_SINGULAR' => 'Target',
  'LBL_MODULE_ID'   => 'Targets',
  'LBL_INVITEE' => 'Direct reports',
  'LBL_MODULE_TITLE' => 'Targets: Home',
  'LBL_SEARCH_FORM_TITLE' => 'Target Search',
  'LBL_LIST_FORM_TITLE' => 'Target List',
  'LBL_NEW_FORM_TITLE' => 'New Target',
  'LBL_PROSPECT' => 'Target:',
  'LBL_BUSINESSCARD' => 'Business card',
  'LBL_LIST_NAME' => 'Name',
  'LBL_LIST_LAST_NAME' => 'Last name',
  'LBL_LIST_PROSPECT_NAME' => 'Target name',
  'LBL_LIST_TITLE' => 'Title',
  'LBL_LIST_EMAIL_ADDRESS' => 'Email',
  'LBL_LIST_OTHER_EMAIL_ADDRESS' => 'Other email',
  'LBL_LIST_PHONE' => 'Phone',
  'LBL_LIST_PROSPECT_ROLE' => 'Role',
  'LBL_LIST_FIRST_NAME' => 'First name',
  'LBL_ASSIGNED_TO_NAME' => 'Assigned to',
  'LBL_ASSIGNED_TO_ID'=>'Assigned To:',
//DON'T CONVERT THESE THEY ARE MAPPINGS
  'db_last_name' => 'LBL_LIST_LAST_NAME',
  'db_first_name' => 'LBL_LIST_FIRST_NAME',
  'db_title' => 'LBL_LIST_TITLE',
  'db_email1' => 'LBL_LIST_EMAIL_ADDRESS',
  'db_email2' => 'LBL_LIST_OTHER_EMAIL_ADDRESS',
//END DON'T CONVERT
  'LBL_CAMPAIGN_ID' => 'Campaign ID',
  'LBL_EXISTING_PROSPECT' => 'Used an existing contact',
  'LBL_CREATED_PROSPECT' => 'Created a new contact',
  'LBL_EXISTING_ACCOUNT' => 'Used an existing account',
  'LBL_CREATED_ACCOUNT' => 'Created a new account',
  'LBL_CREATED_CALL' => 'Created a new call',
  'LBL_CREATED_MEETING' => 'Created a new meeting',
  'LBL_ADDMORE_BUSINESSCARD' => 'Add another business card',
  'LBL_ADD_BUSINESSCARD' => 'Enter business card',
  'LBL_NAME' => 'Name:',
  'LBL_FULL_NAME' => 'Name',
  'LBL_PROSPECT_NAME' => 'Target name:',
  'LBL_PROSPECT_INFORMATION' => 'Overview',
  'LBL_MORE_INFORMATION' => 'More information',
  'LBL_FIRST_NAME' => 'First name:',
  'LBL_OFFICE_PHONE' => 'Office phone:',
  'LBL_ANY_PHONE' => 'Any phone:',
  'LBL_PHONE' => 'Phone:',
  'LBL_LAST_NAME' => 'Last name:',
  'LBL_MOBILE_PHONE' => 'Mobile:',
  'LBL_HOME_PHONE' => 'Home:',
  'LBL_OTHER_PHONE' => 'Other phone:',
  'LBL_FAX_PHONE' => 'Fax:',
  'LBL_STREET' => 'Street',
  'LBL_PRIMARY_ADDRESS_STREET' => 'Primary address street:',
  'LBL_PRIMARY_ADDRESS_CITY' => 'Primary address city:',
  'LBL_PRIMARY_ADDRESS_COUNTRY' => 'Primary address country:',
  'LBL_PRIMARY_ADDRESS_STATE' => 'Primary address state:',
  'LBL_PRIMARY_ADDRESS_POSTALCODE' => 'Primary address postal code:',
  'LBL_ALT_ADDRESS_STREET' => 'Alternate address street:',
  'LBL_ALT_ADDRESS_CITY' => 'Alternate address city:',
  'LBL_ALT_ADDRESS_COUNTRY' => 'Alternate address country:',
  'LBL_ALT_ADDRESS_STATE' => 'Alternate address state:',
  'LBL_ALT_ADDRESS_POSTALCODE' => 'Alternate address postal code:',
  'LBL_TITLE' => 'Title:',
  'LBL_DEPARTMENT' => 'Department:',
  'LBL_BIRTHDATE' => 'Birthdate:',
  'LBL_EMAIL_ADDRESS' => 'Email address:',
  'LBL_OTHER_EMAIL_ADDRESS' => 'Other email:',
  'LBL_ANY_EMAIL' => 'Email:',
  'LBL_ASSISTANT' => 'Assistant:',
  'LBL_ASSISTANT_PHONE' => 'Assistant phone:',
  'LBL_DO_NOT_CALL' => 'Do not call:',
  'LBL_EMAIL_OPT_OUT' => 'Email opt out:',
  'LBL_PRIMARY_ADDRESS' => 'Primary address:',
  'LBL_ALTERNATE_ADDRESS' => 'Other address:',
  'LBL_ANY_ADDRESS' => 'Any address:',
  'LBL_CITY' => 'City:',
  'LBL_STATE' => 'State:',
  'LBL_POSTAL_CODE' => 'Postal code:',
  'LBL_COUNTRY' => 'Country:',
  'LBL_DESCRIPTION_INFORMATION' => 'Description information',
  'LBL_ADDRESS_INFORMATION' => 'Address information',
  'LBL_DESCRIPTION' => 'Description:',
  'LBL_PROSPECT_ROLE' => 'Role:',
  'LBL_OPP_NAME' => 'Opportunity name:',
  'LBL_IMPORT_VCARD' => 'Import vCard',
  'LBL_IMPORT_VCARD_SUCCESS' => 'Target from vCard created succesfully',
  'LBL_IMPORT_VCARDTEXT' => 'Automatically create a new target by importing a vCard from your file system.',
  'LBL_DUPLICATE' => 'Possible duplicate targets',
  'MSG_SHOW_DUPLICATES' => 'The target record you are about to create might be a duplicate of a target record that already exists. target records containing similar names and/or email addresses are listed below.<br>Click create target to continue creating this new target, or select an existing target listed below.',
  'MSG_DUPLICATE' => 'The target record you are about to create might be a duplicate of a target record that already exists. Target records containing similar names and/or email addresses are listed below.<br>Click save to continue creating this new target, or click cancel to return to the module without creating the target.',
  'LNK_IMPORT_VCARD' => 'Create target from vCard',
  'LNK_NEW_ACCOUNT' => 'Create account',
  'LNK_NEW_OPPORTUNITY' => 'Create opportunity',
  'LNK_NEW_CASE' => 'Create case',
  'LNK_NEW_NOTE' => 'Create note or attachment',
  'LNK_NEW_CALL' => 'Log call',
  'LNK_NEW_EMAIL' => 'Archive email',
  'LNK_NEW_MEETING' => 'Schedule meeting',
  'LNK_NEW_TASK' => 'Create task',
  'LNK_NEW_APPOINTMENT' => 'Create appointment',
  'LNK_IMPORT_PROSPECTS' => 'Import targets',
  'NTC_DELETE_CONFIRMATION' => 'Are you sure you want to delete this record?',
  'NTC_REMOVE_CONFIRMATION' => 'Are you sure you want to remove this contact from the case?',
  'NTC_REMOVE_DIRECT_REPORT_CONFIRMATION' => 'Are you sure you want to remove this record as a direct report?',
  'ERR_DELETE_RECORD' => 'A record number must be specified to delete the contact.',
  'NTC_COPY_PRIMARY_ADDRESS' => 'Copy primary address to alternate address',
  'NTC_COPY_ALTERNATE_ADDRESS' => 'Copy alternate address to primary address',
  'LBL_SALUTATION' => 'Salutation',
  'LBL_SAVE_PROSPECT' => 'Save target',
  'LBL_CREATED_OPPORTUNITY' =>'Created a new opportunity',
  'NTC_OPPORTUNITY_REQUIRES_ACCOUNT' => 'Creating an opportunity requires an account.\n Please either create a new account or select an existing one.',
  'LNK_SELECT_ACCOUNT' => 'Select account',
  'LNK_NEW_PROSPECT' => 'Create target',
  'LNK_PROSPECT_LIST' => 'View targets',
  'LNK_NEW_CAMPAIGN' => 'Create campaign',
  'LNK_CAMPAIGN_LIST' => 'Campaigns',
  'LNK_NEW_PROSPECT_LIST' => 'Create target list',
  'LNK_PROSPECT_LIST_LIST' => 'Target lists',
  'LNK_IMPORT_PROSPECT' => 'Import targets',
  'LBL_SELECT_CHECKED_BUTTON_LABEL' => 'Select checked targets',
  'LBL_SELECT_CHECKED_BUTTON_TITLE' => 'Select checked targets',
  'LBL_INVALID_EMAIL'=>'Invalid email:',
  'LBL_DEFAULT_SUBPANEL_TITLE'=>'Targets',
  'LBL_PROSPECT_LIST' => 'Prospect list',
  'LBL_CONVERT_BUTTON_KEY' => 'V',
  'LBL_CONVERT_BUTTON_TITLE' => 'Convert Target',
  'LBL_CONVERT_BUTTON_LABEL' => 'Convert target',
  'LBL_CONVERTPROSPECT'=>'Convert target',
  'LNK_NEW_CONTACT'=>'New contact',
  'LBL_CREATED_CONTACT'=>'Created a new contact',
  'LBL_BACKTO_PROSPECTS'=>'Back to targets',
  'LBL_CAMPAIGNS'=>'Campaigns',
  'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE'=>'Campaign Log',
  'LBL_TRACKER_KEY'=>'Tracker key',
  'LBL_LEAD_ID'=>'Lead ID',
  'LBL_CONVERTED_LEAD'=>'Converted lead',
  'LBL_ACCOUNT_NAME'=>'Account name',
  'LBL_EDIT_ACCOUNT_NAME'=>'Account name:',
  'LBL_CREATED_USER' => 'Created user',
  'LBL_MODIFIED_USER' => 'Modified user',
  'LBL_CAMPAIGNS_SUBPANEL_TITLE' => 'Campaigns',
  'LBL_HISTORY_SUBPANEL_TITLE'=>'History',
  //For export labels
  'LBL_PHONE_HOME' => 'Phone home',
  'LBL_PHONE_MOBILE' => 'Phone mobile',
  'LBL_PHONE_WORK' => 'Phone work',
  'LBL_PHONE_OTHER' => 'Phone other',
  'LBL_PHONE_FAX' => 'Phone fax',
  'LBL_CAMPAIGN_ID' => 'Campaign ID',
  'LBL_EXPORT_ASSIGNED_USER_NAME' => 'Assigned user name',
  'LBL_EXPORT_ASSIGNED_USER_ID' => 'Assigned user ID',
  'LBL_EXPORT_MODIFIED_USER_ID' => 'Modified by ID',
  'LBL_EXPORT_CREATED_BY' => 'Created by ID',
  'LBL_EXPORT_EMAIL2'=>'Other email address',
  'LBL_RECORD_SAVED_SUCCESS' => 'You successfully created the {{moduleSingularLower}} <a href="#{{buildRoute model=this}}">{{full_name}}</a>.',

    //Document title
    'TPL_BROWSER_SUGAR7_RECORDS_TITLE' => '{{module}} &raquo; {{appId}}',
    'TPL_BROWSER_SUGAR7_RECORD_TITLE' => '{{#if last_name}}{{#if first_name}}{{first_name}} {{/if}}{{last_name}} &raquo; {{/if}}{{module}} &raquo; {{appId}}',

    // Help Text
    // List View Help Text
    'LBL_HELP_RECORDS' => 'The {{module_name}} module consists of individual people who are unqualified prospects that you have some information on, but is not yet a qualified {{lead_module}}. Information (e.g. name, email address) regarding these {{plural_module_name}} are normally acquired from business cards you receive while attending various trades shows, conferences, etc. {{plural_module_name}} in Sugar are stand-alone records as they are not related to {{contacts_module}}, {{leads_module}}, {{accounts_module}}, or {{opportunities_module}}. There are various ways you can create {{plural_module_name}} in Sugar such as via the {{plural_module_name}} module, importing {{plural_module_name}}, etc. Once the {{module_name}} record is created, you can view and edit information pertaining to the {{module_name}} via the {{plural_module_name}} Record view.',

    // Record View Help Text
    'LBL_HELP_RECORD' => 'The {{module_name}} module consists of individual people who are unqualified prospects that you have some information on, but is not yet a qualified {{lead_module}}.

- Edit this record\'s fields by clicking an individual field or the Edit button.
- View or modify links to other records in the subpanels by toggling the bottom left pane to "Data View".
- Make and view user comments and record change history in the {{activitystream_module}} by toggling the bottom left pane to "Activity Stream".
- Follow or favorite this record using the icons to the right of the record name.
- Additional actions are available in the dropdown Actions menu to the right of the Edit button.',

    // Create View Help Text
    'LBL_HELP_CREATE' => 'The {{module_name}} module consists of individual people who are unqualified prospects that you have some information on, but is not yet a qualified {{lead_module}}.

To create a {{module_name}}:
1. Provide values for the fields as desired.
 - Fields marked "Required" must be completed prior to saving.
 - Click "Show More" to expose additional fields if necessary.
2. Click "Save" to finalize the new record and return to the previous page.
 - Choose "Save and view" to open the new {{module_name}} in record view.
 - Choose "Save and create new" to immediately create another new {{module_name}}.',
);
