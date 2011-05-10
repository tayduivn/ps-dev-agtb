<?php 

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

?>