<?php
$subpanel_layout = array(
	// BEGIN SADEK SUGARINTERNAL CUSTOMIZATION - ADDING STATUS VOICEMAIL TO APPEAR IN HISTORY SUBPANEL
	'where' => "(calls.status in ('Held', 'Not Held', 'Voicemail'))",
	// END SADEK SUGARINTERNAL CUSTOMIZATION - ADDING STATUS VOICEMAIL TO APPEAR IN HISTORY SUBPANEL
	
	
	'list_fields' => array(
		'object_image'=>array(
			'vname' => 'LBL_OBJECT_IMAGE',
			'widget_class' => 'SubPanelIcon',
 		 	'width' => '2%',
		),
		'name'=>array(
			 'vname' => 'LBL_LIST_SUBJECT',
			 'widget_class' => 'SubPanelDetailViewLink',
			 'width' => '30%',
			 'sortable'=>false,
		),
		'status'=>array(
			 'widget_class' => 'SubPanelActivitiesStatusField',
			 'vname' => 'LBL_LIST_STATUS',
			 'width' => '15%',
			 'force_exists'=>true, //this will create a fake field in the case a field is not defined
			 'sortable'=>false,
		),
		'contact_name'=>array(
			 'widget_class'			=> 'SubPanelDetailViewLink',
			 'target_record_key'	=> 'contact_id',
			 'target_module'		=> 'Contacts',
			 'module'				=> 'Contacts',
			 'vname'				=> 'LBL_LIST_CONTACT',
			 'width'				=> '11%',
			 'sortable'=>false,
		),
		'contact_id'=>array(
			'usage'=>'query_only',
		),
		'contact_name_owner'=>array(
			'usage'=>'query_only',
			'force_exists'=>true
		),	
		'contact_name_mod'=>array(
			'usage'=>'query_only',
			'force_exists'=>true
		),		
		'date_modified'=>array(
			'vname' => 'LBL_LIST_DATE_MODIFIED',
			'width' => '10%',
			'sortable'=>false,
		),
		'assigned_user_name' => array (
			'name' => 'assigned_user_name',
			'vname' => 'LBL_LIST_ASSIGNED_TO_NAME',
			'sortable'=>false,
		),
		'filename'=>array(
			'usage'=>'query_only',
			'force_exists'=>true
		),		
	),
);		
?>
