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
			'width' => '40%',
		),
        'quantity_fields'=>array(
            'usage' => 'query_only',
        ),
        'quantity_fields_id'=>array(
            'usage' => 'query_only',
        ),
        'quantity'=>array(
            'name' => 'quantity',
            'vname' => 'LBL_LIST_QUANTITY',
            'width' => '50%',
            'sortable'=>false,
        ),
		'edit_button'=>array(
			'widget_class' => 'SubPanelEditQuantityButton',
			//'widget_class' => 'SubPanelEditButton',
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
