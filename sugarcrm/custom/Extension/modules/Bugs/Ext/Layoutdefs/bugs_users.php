<?php
/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/

$layout_defs["Bugs"]["subpanel_setup"]["users"] = array (
  'order' => 4,
  'module' => 'Users',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'last_name, first_name',
  'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
  'get_subpanel_data' => 'users',
  'add_subpanel_data' => 'id',
		'top_buttons' => array(
				array(
					'widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'Bugs',
					'mode' => 'MultiSelect',
				),
			),
);


?>
