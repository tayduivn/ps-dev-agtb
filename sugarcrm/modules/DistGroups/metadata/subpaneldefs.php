<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['DistGroups'] = array(
	'subpanel_setup' => array(
		/*
		'sugarproducts' => array(
			'order' => 10,
			'module' => 'SugarProducts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'get_subpanel_data' => 'sugarproducts',
			'add_subpanel_data' => 'sugarproduct_id',
			'subpanel_name' => 'default',
			'title_key' => 'LBL_SUGARPRODUCTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				//array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				//array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
		*/
		'subscriptions' => array(
			'order' => 20,
			'module' => 'Subscriptions',
			'sort_order' => 'asc',
			'sort_by' => 'subscription_id',
			'get_subpanel_data' => 'subscriptions',
			'add_subpanel_data' => 'subscription_id',
			'subpanel_name' => 'default',
			'title_key' => 'LBL_SUBSCRIPTIONS_SUBPANEL_TITLE',
			'top_buttons' => array(
				//array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				//array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
	),
);

?>
