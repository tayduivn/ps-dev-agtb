<?php 
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
