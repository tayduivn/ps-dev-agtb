<?php 
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

?>
