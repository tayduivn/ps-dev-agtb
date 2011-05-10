<?php 

$layout_defs['Project']['subpanel_setup']['cases'] = array(
	    'top_buttons' => array(
	        array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Cases'),
	     ),
	    'order' => 100,
	    'module' => 'Cases',
	    'sort_order' => 'desc',
	    'sort_by' => 'case_number',
	    'subpanel_name' => 'default',
	    'get_subpanel_data' => 'cases',
	    'add_subpanel_data' => 'case_id',
	    'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
	);

$layout_defs['Project']['subpanel_setup']['bugs'] = array(
        'top_buttons' => array(
            array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Bugs'),
         ),
        'order' => 110,
        'module' => 'Bugs',
        'sort_order' => 'desc',
        'sort_by' => 'bug_number',
        'subpanel_name' => 'default',
        'get_subpanel_data' => 'bugs',
        'add_subpanel_data' => 'bug_id',
        'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
    );

?>