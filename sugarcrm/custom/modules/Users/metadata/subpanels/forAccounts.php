<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$subpanel_layout = array(
	'top_buttons' => array(
        array('widget_class' => 'SubPanelTopCreateButton'),
			array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Users'),
	),

	'where' => '',
	
	

    'list_fields'=> array(
        'first_name'=>array(
		 	'usage' => 'query_only',
		),
		'last_name'=>array(
		 	'usage' => 'query_only',
		),
		'full_name'=>array(
			'vname' => 'LBL_LIST_NAME',
			'widget_class' => 'SubPanelDetailViewLink',
		 	'module' => 'Users',
	 		'width' => '20%',
		),
		'user_name'=>array(
			'vname' => 'LBL_LIST_USER_NAME',
			'width' => '19%',
		),
		'title' => array(
			'vname' => 'LBL_LIST_TITLE',
			'width' => '19%',
		),
		/**
		 * Skeleton to show the user role in Account team
		 * - Dropdown definition is already made
		 * - display labels are present
		 * - only thing missing is the bean extention to support these fields
		 */

		/*
		'account_role_fields'=>array(
            'usage' => 'query_only',
        ),
        'account_role_id'=>array(
            'usage' => 'query_only',
        ),
        'account_role'=>array(
            'name'=>'account_role',
            'vname' => 'LBL_LIST_USER_ROLE',
            'width' => '10%',
            'sortable'=>false,
        ),
		*/
		'email1'=>array(
			'vname' => 'LBL_LIST_EMAIL',
			'width' => '19%',
		),
		'phone_work'=>array (
			'vname' => 'LBL_LIST_PHONE',
			'width' => '19%',
		),
		'remove_button'=>array(
			'vname' => 'LBL_REMOVE',
			'widget_class' => 'SubPanelRemoveButton',
		 	'module' => 'Users',
			'width' => '4%',
			'linked_field' => 'users',
		),
	),
);

?>
