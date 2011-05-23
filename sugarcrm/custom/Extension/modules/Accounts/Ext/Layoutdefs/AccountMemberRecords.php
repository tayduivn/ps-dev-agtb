<?php

/**
 * Customized subpanels to query records which are also related to child Accounts (member accounts) 
 **
 * Call the SubPanel_Accounts_Children function from custom/modules/Accounts/subpanel_accounts_children.php
 * which generates the customized queries to get the related records from the child accounts if present
 *
 * property values
 * - generate_select => must be true
 * - get_subpanel_data => the function which will be called
 *
 * function_parameters
 *	- import_function_file => file which will be included containing the function
 *	- subpanel_module => specify the module to be used for the list view
 *	
 * In case the subpanel module is linked to Accounts using a relationshiptable:
 *	- relationship_table => self-explanatory
 *	- account_col => column name containing Account ids
 *	- related_col => column name containing related record ids
 */


// Contacts
$layout_defs['Accounts']['subpanel_setup']['contacts']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['contacts']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['contacts']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'contacts',
        'relationship_table' => 'accounts_contacts',
        'account_col' => 'account_id',
        'related_col' => 'contact_id',
);

// Opportunities
$layout_defs['Accounts']['subpanel_setup']['opportunities']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['opportunities']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['opportunities']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'opportunities',
        'relationship_table' => 'accounts_opportunities',
        'account_col' => 'account_id',
        'related_col' => 'opportunity_id',
);

// Activities -> Tasks
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['tasks']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['tasks']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['tasks']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'tasks',
);

// Activities -> Meetings
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['meetings']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['meetings']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['meetings']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'meetings',
);

// Activities -> Calls
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['calls']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['calls']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['activities']['collection_list']['calls']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'calls',
);

// History -> Notes
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['notes']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['notes']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['notes']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'notes',
);

// History -> Emails
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['emails']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['emails']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['emails']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'emails',
);

// History -> Linked Emails
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['linkedemails']['get_subpanel_data'] = 'function:account_member_records';
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['linkedemails']['generate_select'] = true;
$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['linkedemails']['function_parameters'] = array(
        'import_function_file' => 'custom/Extension/modules/Accounts/Ext/Dependencies/account_member_records.php',
        'subpanel_module' => 'linkedemails',
);

?>
