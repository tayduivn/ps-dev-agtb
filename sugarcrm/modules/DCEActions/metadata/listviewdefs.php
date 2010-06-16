<?php
$module_name = 'DCEActions';
$listViewDefs = array (
$module_name =>
  array (
    'NAME' => 
    array (
      'label' => 'LBL_NAME',
      'default' => true,
      'link' => true,
      'width' => '10',
    ),
    'INSTANCE_NAME' => 
     array (
      'width' => '9',
      'label' => 'LBL_INSTANCE_NAME',
      'default' => true,
      'module' => 'DCEInstances',
      'id' => 'INSTANCE_ID',
      'link' => true,
      'ACLTag' => 'DCEINSTANCE',
      'related_fields' => 
      array (
       0 => 'instance_id',
      ),
    ),
    'CLUSTER_NAME' => 
     array (
      'width' => '9',  
      'label' => 'LBL_CLUSTER_NAME',
      'default' => true,
      'module' => 'DCEClusters',
      'id' => 'CLUSTER_ID',
      'link' => true,
      'ACLTag' => 'DCECLUSTER',
      'related_fields' => 
      array (
       0 => 'cluster_id',
      ),
     ),
     'TEMPLATE_NAME' => 
     array (
      'width' => '9',  
      'label' => 'LBL_TEMPLATE_NAME',
      'default' => true,
      'module' => 'DCETemplates',
      'id' => 'TEMPLATE_ID',
      'link' => true,
      'ACLTag' => 'DCETEMPLATE',
      'related_fields' => 
      array (
       0 => 'template_id',
      ),
     ),
    'TYPE' => 
    array (
      'label' => 'LBL_TYPE',
      'default' => true,
      'width' => '10',
    ),
    'STATUS' => 
    array (
      'label' => 'LBL_STATUS',
      'default' => true,
      'width' => '10',
    ),
    'CLIENT_NAME' => 
    array (
      'label' => 'LBL_NODE',
      'default' => true,
      'width' => '10',
    ),
    'DATE_ENTERED' => 
    array (
      'width' => '15',
      'label' => 'LBL_DATE_ENTERED',
      'default' => true,
    ),
    'DATE_STARTED' => 
    array (
      'width' => '15',
      'label' => 'LBL_DATE_STARTED',
      'default' => true,
    ),
    'DATE_COMPLETED' => 
    array (
      'width' => '15',
      'label' => 'LBL_DATE_COMPLETED',
      'default' => true,
    ),
  ),
);
?>
