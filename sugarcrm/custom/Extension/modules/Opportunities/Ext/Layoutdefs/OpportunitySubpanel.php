<?php

$layout_defs['Opportunities']['subpanel_setup']['users'] = array(
	'order' => 63,
	'module' => 'Users',
	'sort_order' => 'asc',
	'sort_by' => 'last_name, first_name',
	'subpanel_name' => 'forAccounts',
	'get_subpanel_data' => 'users',
	'add_subpanel_data' => 'id',
	'title_key' => 'LBL_OPPORTUNITY_TEAM_SUBPANEL_TITLE',
	'top_buttons' => array(
		//array(
		//    'widget_class' => 'SubPanelTopSelectButton',
		//    'popup_module' => 'Opportunities',
		//    'mode' => 'MultiSelect',
		//),
	),
);
