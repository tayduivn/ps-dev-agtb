<?php

// START jvink - customization
// Collection subpanel for all WinPlans modules

$layout_defs['Opportunities']['subpanel_setup']['ibm_winplans'] = array(

	'order' => 30,
	'sort_order' => 'desc',
	'sort_by' => 'date_modified',
	'title_key' => 'Win Plans',
	'type' => 'collection',
	'subpanel_name' => 'ibm_winplans',	// just a bogus name, no module needed
	'module'=>'ibm_WinPlans',			// just a bogus name, no module needed

	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopCreateWinPlanGeneric'),
		array('widget_class' => 'SubPanelTopCreateWinPlanSTG'),
		array('widget_class' => 'SubPanelTopCreateWinPlanSWG'),
	),

	'collection_list' => array(
		'ibm_winplangeneric' => array(
			'module' => 'ibm_WinPlanGeneric',
			'subpanel_name' => 'ForOpportunities',
			'get_subpanel_data' => 'opportunities_ibm_winplangeneric',
		),
		'ibm_winplanstg' => array(
			'module' => 'ibm_WinPlanSTG',
			'subpanel_name' => 'ForOpportunities',
			'get_subpanel_data' => 'opportunities_ibm_winplanstg',
		),
		'ibm_winplanswg' => array(
			'module' => 'ibm_WinPlanSWG',
			'subpanel_name' => 'ForOpportunities',
			'get_subpanel_data' => 'opportunities_ibm_winplanswg',
		),
	),
);

// END jvink
