<?php


$subpanel_layout = array(
	//Removed button because this layout def is a component of
	//the activities sub-panel.

	'where' => '',
					
					
	'list_fields' => array(
		'object_image'=>array(
			'vname' => 'LBL_OBJECT_IMAGE',
			'widget_class' => 'SubPanelIcon',
 		 	'width' => '2%',
 		 	'image2'=>'attachment',
 		 	'image2_url_field'=>'file_url'
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
		
		'date_modified'=>array(
			 'vname' => 'LBL_LIST_DATE_MODIFIED',
			 'width' => '10%',
			 'sortable'=>false,
		),
		'assigned_user_name' => array (
			'vname' => 'LBL_LIST_ASSIGNED_TO_NAME',
			 'force_exists'=>true, //this will create a fake field since this field is not defined
			 'sortable'=>false,
		),
		'assigned_user_owner' => array (
			 'force_exists'=>true, //this will create a fake field since this field is not defined
			'usage'=>'query_only',
			 'sortable'=>false,
		),
		'assigned_user_mod' => array (
			 'force_exists'=>true, //this will create a fake field since this field is not defined
			'usage'=>'query_only',
			 'sortable'=>false,
		),
		'file_url'=>array(
			'usage'=>'query_only'
			),
		'filename'=>array(
			'usage'=>'query_only'
			),
				
	),
);		
?>
