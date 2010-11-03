<?php 
 //WARNING: The contents of this file are auto-generated

 

$layout_defs['Users']['subpanel_setup']['holidays'] =  array(
	'order' => 30,
	'sort_by' => 'holiday_date',
	'sort_order' => 'asc',
	'module' => 'Holidays',
	'subpanel_name' => 'default',
	'get_subpanel_data' => 'holidays',
	'refresh_page'=>1,
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopButtonQuickCreate'),
	),
	'title_key' => 'LBL_USER_HOLIDAY_SUBPANEL_TITLE',
);


 

$layout_defs['Users']['subpanel_setup']['itrequests'] =  array(
            'order' => 83,
            'module' => 'ITRequests',
            'sort_order' => 'asc',
            'sort_by' => 'date_modified',
            'get_subpanel_data' => 'itrequests',
            'add_subpanel_data' => 'itrequest_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_ITREQUESTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),
		);


 
/*
 @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17275
** Description: Add cases subpanel to User
*/

$layout_defs['Users']['subpanel_setup']['cases'] =  array(
            'order' => 95,
            'module' => 'Cases',
            'sort_order' => 'desc',
            'sort_by' => 'date_modified',
            'get_subpanel_data' => 'cases',
            'add_subpanel_data' => 'case_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),
		);



 
/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/

$layout_defs['Users']['subpanel_setup']['bugs'] =  array(
            'order' => 95,
            'module' => 'Bugs',
            'sort_order' => 'desc',
            'sort_by' => 'date_modified',
            'get_subpanel_data' => 'bugs',
            'add_subpanel_data' => 'bug_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),
		);



?>