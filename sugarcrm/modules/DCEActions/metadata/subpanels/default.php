<?php
$module_name='DCEActions';
$subpanel_layout = array (
  'top_buttons' => array(
    ),
  'where' => '',
  'list_fields' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'vname' => 'LBL_NAME',
      'width' => '9',
      'widget_class' => 'SubPanelDetailViewLink',
    ),
    'instance_name' => 
     array (
	  'width' => '9',
	  'vname' => 'LBL_INSTANCE_NAME',
	  'module' => 'DCEInstances',
      'target_record_key'    => 'instance_id',
      'target_module'        => 'DCEInstances',
	  'id' => 'instance_id',
	  'widget_class' => 'SubPanelDetailViewLink',
	  'related_fields' => 
      array (
       0 => 'instance_id',
      ),
    ),
    'cluster_name' => 
     array (
      'width' => '9',  
      'vname' => 'LBL_CLUSTER_NAME',
      'module' => 'DCEClusters',
      'target_record_key'    => 'cluster_id',
      'target_module'        => 'DCEClusters',
      'id' => 'cluster_id',
      'widget_class' => 'SubPanelDetailViewLink',
      'related_fields' => 
      array (
       0 => 'cluster_id',
      ),
     ),
     'template_name' => 
     array (
      'width' => '9',  
      'vname' => 'LBL_TEMPLATE_NAME',
      'module' => 'DCETemplates',
      'target_record_key'    => 'template_id',
      'target_module'        => 'DCETemplates',
      'id' => 'template_id',
      'widget_class' => 'SubPanelDetailViewLink',
      'related_fields' => 
      array (
       0 => 'template_id',
      ),
     ),
    'type' => 
    array (
      'name' => 'type',
      'vname' => 'LBL_TYPE',
      'width' => '9',
    ),
    'status' => 
    array (
      'name' => 'status',
      'vname' => 'LBL_STATUS',
      'width' => '9',
    ),
    'date_entered' => 
    array (
      'name' => 'date_entered',
      'width' => '9',
      'vname' => 'LBL_DATE_ENTERED',
    ),
    'date_started' => 
    array (
      'name' => 'date_started',
      'width' => '9',
      'vname' => 'LBL_DATE_STARTED',
    ),
    'date_completed' => 
    array (
      'name' => 'date_completed',
      'width' => '9',
      'vname' => 'LBL_DATE_COMPLETED',
    ),
  ),
);