<?php
/*
 @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17275
** Description: Add subpanel between Cases and User
*/

$layout_defs["Cases"]["subpanel_setup"]["users"] = array (
  'order' => 10,
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
					'popup_module' => 'Cases',
					'mode' => 'MultiSelect',
				),
			),
);


?>
