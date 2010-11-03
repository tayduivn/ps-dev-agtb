<?php

// remove the default leads subpanel and add the two sub panels for the M2 Project
// jwhitcraft - 3.12.10
unset($layout_defs['Accounts']['subpanel_setup']['leads']);

$layout_defs['Accounts']['subpanel_setup']['leadaccounts'] = array(
			'order' => 50,
			'module' => 'LeadAccounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'leadaccounts',
			'add_subpanel_data' => 'leadaccount_id',
			'title_key' => 'LBL_LEADACCOUNTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'Opportunities',
					'mode' => 'MultiSelect',
				),
			),
		);

$layout_defs['Accounts']['subpanel_setup']['interactions'] = array(
			'order' => 130,
			'module' => 'Interactions',
			'sort_order' => 'desc',
			'sort_by' => 'start_date',
			'get_subpanel_data'=>'function:getInteractionsQuery',
			'generate_select' => true,
			'function_parameters' => array('return_as_array' => 'true'),
			'subpanel_name' => 'default',
			'title_key' => 'LBL_INTERACTIONS_SUBPANEL_TITLE',
		);
// end remove the default leads subpanel and add the two sub panels for the M2 Project
// jwhitcraft - 3.12.10