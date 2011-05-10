<?php
$module_name='DCEDataBases';
$subpanel_layout = array (
    'top_buttons' => array(
        array('widget_class' => 'SubPanelTopCreateButton'),
    ),
  'where' => '',
  'list_fields' => 
  array (
    'name' => 
    array (
      'vname' => 'LBL_NAME',
      'widget_class' => 'SubPanelDetailViewLink',
      'width' => '30',
    ),
    'cluster_name' => 
    array (
      'name' => 'cluster_name',
      'vname' => '',
      'width' => '15',
    ),
    'primary_role' => 
    array (
      'name' => 'primary_role',
      'vname' => 'LBL_PRIMARY_ROLE',
      'width' => '5',
    ),
    'reports_role' => 
    array (
      'name' => 'primary_role',
      'vname' => 'LBL_REPORTS_ROLE',
      'width' => '5',
    ),
    'host' => 
    array (
      'name' => 'host',
      'vname' => 'LBL_HOST',
      'width' => '10',
    ),

    'date_modified' => 
    array (
      'vname' => 'LBL_DATE_MODIFIED',
      'width' => '15',
    ),
  ),
);