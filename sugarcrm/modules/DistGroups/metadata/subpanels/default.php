<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$subpanel_layout = array(
	'top_buttons' => array(
		//array('widget_class' => 'SubPanelTopCreateButton'),
		array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'DistGroups'),
	),

	'where' => '',
	'default_order_by' => '',

	'list_fields' => array(
		'name'=>array(
	 		'vname' => 'LBL_LIST_NAME',
	 		'widget_class' => 'SubPanelDetailViewLink',
			'width' => '90%',
		),
		'edit_button'=>array(
			'widget_class' => 'SubPanelEditButton',
		 	'module' => 'DistGroups',
			'width' => '5%',
		),
		'remove_button'=>array(
			'widget_class' => 'SubPanelRemoveButton',
		 	'module' => 'DistGroups',
			'width' => '5%',
		),
	),
);
?>
