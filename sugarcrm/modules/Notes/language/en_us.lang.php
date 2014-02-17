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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$mod_strings = array(
    'ERR_DELETE_RECORD' => 'You must specify a record number to delete the account.',
    'LBL_ACCOUNT_ID' => 'Account ID:',
    'LBL_CASE_ID' => 'Case ID:',
    'LBL_CLOSE' => 'Close:',
    'LBL_COLON' => ':',
    'LBL_CONTACT_ID' => 'Contact ID:',
    'LBL_CONTACT_NAME' => 'Contact:',
    'LBL_DEFAULT_SUBPANEL_TITLE' => 'Notes',
    'LBL_DESCRIPTION' => 'Description',
    'LBL_EMAIL_ADDRESS' => 'Email address:',
    'LBL_EMAIL_ATTACHMENT' => 'Email attachment',
    'LBL_FILE_MIME_TYPE' => 'Mime type',
    'LBL_FILE_URL' => 'File URL',
    'LBL_FILENAME' => 'Attachment:',
    'LBL_LEAD_ID' => 'Lead ID:',
    'LBL_LIST_CONTACT_NAME' => 'Contact',
    'LBL_LIST_DATE_MODIFIED' => 'Last modified',
    'LBL_LIST_FILENAME' => 'Attachment',
    'LBL_LIST_FORM_TITLE' => 'Note List',
    'LBL_LIST_RELATED_TO' => 'Related to',
    'LBL_LIST_SUBJECT' => 'Subject',
    'LBL_LIST_STATUS' => 'Status',
    'LBL_LIST_CONTACT' => 'Contact',
    'LBL_MODULE_NAME' => 'Notes',
    'LBL_MODULE_NAME_SINGULAR' => 'Note',
    'LBL_MODULE_TITLE' => 'Notes: Home',
    'LBL_NEW_FORM_TITLE' => 'Create Note or Add Attachment',
    'LBL_NEW_FORM_BTN' => 'Add a note',
    'LBL_NOTE_STATUS' => 'Note',
    'LBL_NOTE_SUBJECT' => 'Subject:',
    'LBL_NOTES_SUBPANEL_TITLE' => 'Notes & Attachments',
    'LBL_NOTE' => 'Note:',
    'LBL_OPPORTUNITY_ID' => 'Opportunity ID:',
    'LBL_PARENT_ID' => 'Parent ID:',
    'LBL_PARENT_TYPE' => 'Parent type',
    'LBL_PHONE' => 'Phone:',
    'LBL_PORTAL_FLAG' => 'Display in portal?',
    'LBL_EMBED_FLAG' => 'Embed in email?',
    'LBL_PRODUCT_ID' => 'Quoted line item ID:',
    'LBL_QUOTE_ID' => 'Quote ID:',
    'LBL_RELATED_TO' => 'Related to:',
    'LBL_SEARCH_FORM_TITLE' => 'Note Search',
    'LBL_STATUS' => 'Status',
    'LBL_SUBJECT' => 'Subject:',
    'LNK_IMPORT_NOTES' => 'Import notes',
    'LNK_NEW_NOTE' => 'Create note or attachment',
    'LNK_NOTE_LIST' => 'View notes',
    'LBL_MEMBER_OF' => 'Member of:',
    'LBL_LIST_ASSIGNED_TO_NAME' => 'Assigned user',
    //BEGIN SUGARCRM flav=pro ONLY
    'LBL_OC_FILE_NOTICE' => 'Please login to server to view file',
    //END SUGARCRM flav=pro ONLY
    'LBL_REMOVING_ATTACHMENT' => 'Removing attachment...',
    'ERR_REMOVING_ATTACHMENT' => 'Failed to remove attachment...',
    'LBL_CREATED_BY' => 'Created by',
    'LBL_MODIFIED_BY' => 'Modified by',
    'LBL_SEND_ANYWAYS' => 'Are you sure you want to send/save the email without subject?',
    'LBL_LIST_EDIT_BUTTON' => 'Edit',
    'LBL_ACTIVITIES_REPORTS' => 'Activities report',
    'LBL_PANEL_DETAILS' => 'Details',
    'LBL_NOTE_INFORMATION' => 'Overview',
    'LBL_MY_NOTES_DASHLETNAME' => 'My notes',
    'LBL_EDITLAYOUT' => 'Edit layout' /*for 508 compliance fix*/,
    //For export labels
    'LBL_FIRST_NAME' => 'First name',
    'LBL_LAST_NAME' => 'Last name',
    'LBL_EXPORT_PARENT_TYPE' => 'Related to module',
    'LBL_EXPORT_PARENT_ID' => 'Related to ID',
    'LBL_DATE_ENTERED' => 'Date created',
    'LBL_DATE_MODIFIED' => 'Date modified',
    'LBL_DELETED' => 'Deleted',
    'LBL_REVENUELINEITEMS' => 'Revenue line items',

    // Help Text
    // List View Help Text
    'LBL_HELP_RECORDS' => 'The {{plural_module_name}} module consists of individual {{plural_module_name}} that contain text or an attachment pertinent to the related record. {{module_name}} records can be related to one record in most modules via the flex relate field and can also be related to a single {{contact_module}}. {{plural_module_name}} can hold generic text about a record or even an attachment related to the record. There are various ways you can create {{plural_module_name}} in Sugar such as via the {{plural_module_name}} module, importing {{plural_module_name}}, via History subpanels, etc. Once the {{module_name}} record is created, you can view and edit information pertaining to the {{module_name}} via the {{plural_module_name}} record view. Each {{module_name}} record may then relate to other Sugar records such as {{accounts_module}}, {{contacts_module}}, {{opportunities_module}}, and many others.',

    // Record View Help Text
    'LBL_HELP_RECORD' => 'The {{plural_module_name}} module consists of individual {{plural_module_name}} that contain text or an attachment pertinent to the related record.

- Edit this record\'s fields by clicking an individual field or the Edit button.
- View or modify links to other records in the subpanels by toggling the bottom left pane to "Data View".
- Make and view user comments and record change history in the {{activitystream_module}} by toggling the bottom left pane to "Activity Stream".
- Follow or favorite this record using the icons to the right of the record name.
- Additional actions are available in the dropdown Actions menu to the right of the Edit button.',

    // Create View Help Text
    'LBL_HELP_CREATE' => 'To create a {{module_name}}:
1. Provide values for the fields as desired.
 - Fields marked "Required" must be completed prior to saving.
 - Click "Show More" to expose additional fields if necessary.
2. Click "Save" to finalize the new record and return to the previous page.
 - Choose "Save and view" to open the new {{module_name}} in record view.
 - Choose "Save and create new" to immediately create another new {{module_name}}.',
);
