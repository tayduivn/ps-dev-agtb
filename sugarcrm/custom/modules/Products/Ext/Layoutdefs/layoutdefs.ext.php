<?php 
 //WARNING: The contents of this file are auto-generated

 

$layout_defs['Products']['subpanel_setup']['projects'] = array(
    'order' => 40,
    'module' => 'Project',
    'sort_order' => 'desc',
    'sort_by' => 'project_id',
    'subpanel_name' => 'default',
    'refresh_page' => 1,    
    'get_subpanel_data' => 'projects',
    'add_subpanel_data' => 'project_id',
    'title_key' => 'LBL_PROJECTS_SUBPANEL_TITLE',
    'top_buttons' => array(
        array('widget_class' => 'SubPanelTopSelectButton'),
    ),          
); 



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Products']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";

unset($layout_defs['Products']['subpanel_setup']['contracts']);
 


// created: 2010-07-21 07:19:23
$layout_defs["Products"]["subpanel_setup"]["products_contracts"] = array (
  'order' => 100,
  'module' => 'Contracts',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_PRODUCTS_CONTRACTS_FROM_CONTRACTS_TITLE',
  'get_subpanel_data' => 'products_contracts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);

?>