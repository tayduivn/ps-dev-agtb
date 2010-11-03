<?php
/*
** @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #14901
** Description: Redefining entire subpanel def array since we need to remove Leads from stock array.  
** also adding lead_contacts to array, this is for M2 effort
*/
$layout_defs['Emails'] = array(
	// list of what Subpanels to show in the DetailView 
	'subpanel_setup' => array(
		'notes' => array(
			'order' => 5,
			'sort_order' => 'asc',
			'sort_by'	=> 'name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'notes',
			'title_key' => 'LBL_NOTES_SUBPANEL_TITLE',
			'module' => 'Notes',
			'top_buttons' => array(),
		),		
        'accounts' => array(
			'order' => 10,
			'module' => 'Accounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'accounts',
			'add_subpanel_data' => 'account_id',
			'title_key' => 'LBL_ACCOUNTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
        'contacts' => array(
			'order' => 20,
			'module' => 'Contacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'contacts',
			'add_subpanel_data' => 'contact_id',
			'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
        'opportunities' => array(
			'order' => 25,
			'module' => 'Opportunities',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'opportunities',
			'add_subpanel_data' => 'opportunity_id',
			'title_key' => 'LBL_OPPORTUNITY_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),

        'leadcontacts' => array(
			'order' => 30,
			'module' => 'LeadContacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'leadcontacts',
			'add_subpanel_data' => 'lead_id',
			'title_key' => 'LBL_LEAD_CONTACTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),

	'cases' => array(
			'order' => 40,
			'module' => 'Cases',
			'sort_order' => 'desc',
			'sort_by' => 'case_number',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'cases',
			'add_subpanel_data' => 'case_id',
			'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),		
        'users' => array(
			'order' => 50,
			'module' => 'Users',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'users',
			'add_subpanel_data' => 'user_id',
			'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
        'bugs' => array(
			'order' => 60,
			'module' => 'Bugs',
			'sort_order' => 'desc',
			'sort_by' => 'bug_number',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'bugs',
			'add_subpanel_data' => 'bug_id',
			'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),

        'quotes' => array(
			'order' => 70,
			'module' => 'Quotes',
			'sort_order' => 'desc',
			'sort_by' => 'date_quote_expected_closed',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'quotes',
			'add_subpanel_data' => 'quote_id',
			'title_key' => 'LBL_QUOTES_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
        'project' => array(
			'order' => 80,
			'module' => 'Project',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'ForEmails',
			'get_subpanel_data' => 'project',
			'add_subpanel_data' => 'project_id',
			'title_key' => 'LBL_PROJECT_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),

	),
);



//merged from old projects.php file
$layout_defs['Emails']['subpanel_setup']['projecttask'] =  array(
        'order' => 90,
        'module' => 'ProjectTask',
        'sort_order' => 'desc',
        'sort_by' => 'name',
        'subpanel_name' => 'default',
        'refresh_page' => 1,
        'get_subpanel_data' => 'projecttask',
        'add_subpanel_data' => 'project_task_id',
        'title_key' => 'LBL_PROJECT_TASK_SUBPANEL_TITLE',
        'top_buttons' => array(
                array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
        ),
);

?>
