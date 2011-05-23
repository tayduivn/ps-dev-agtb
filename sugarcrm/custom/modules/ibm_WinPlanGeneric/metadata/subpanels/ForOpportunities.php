<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name='ibm_WinPlanGeneric';
$subpanel_layout = array(
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopCreateButton'),
		array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => $module_name),
	),

	'where' => '',

	'list_fields' => array(
		'description'=>array(
	 		'vname' => 'Description',
	 		'width' => '60%',
		),
		'status_c'=>array(
			'name' => 'status_c',
	 		'vname' => 'Status',
			'widget_class' => 'SubPanelDetailViewLink',
	 		'width' => '10%',
		),
		'assigned_user_name' => array (
			'name' => 'assigned_user_name',
			'vname' => 'Owner',
			'widget_class' => 'SubPanelDetailViewLink',
		 	'target_record_key' => 'assigned_user_id',
			'target_module' => 'Employees',
			'width' => '10%',
		),
		'date_modified'=>array(
	 		'vname' => 'Last Modified',
	 		'width' => '10%',
		),
		'date_approved_c'=>array(
			'name' => 'date_approved_c',
	 		'vname' => 'Date Approved',
	 		'width' => '10%',
		),
		'approver_c' => array (
			'name' => 'approver_c',
			'vname' => 'Approver',
			'widget_class' => 'SubPanelDetailViewLink',
		 	'target_record_key' => 'user_id1_c',
			'target_module' => 'Employees',
			'width' => '10%',
		),
		'edit_button'=>array(
			'widget_class' => 'SubPanelEditButtonWinPlans',
		 	'module' => $module_name,
	 		'width' => '5%',
		),
		'remove_button'=>array(
			'widget_class' => 'SubPanelRemoveButtonWinPlans',
		 	'module' => $module_name,
			'width' => '5%',
		),
	),
);

?>
