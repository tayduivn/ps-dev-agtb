<?php 
 //WARNING: The contents of this file are auto-generated



$layout_defs['LeadContacts']['subpanel_setup']['touchpoints'] = array(
        'order' => 66,
        'sort_order' => 'asc',
        'sort_by' => 'last_name',
        'module' => 'Touchpoints',
        'subpanel_name' => 'default',
        'get_subpanel_data'=>'function:getTouchpointsQuery',
        'generate_select' => true,
        'function_parameters' => array('return_as_array' => 'true'),
        'title_key' => 'LBL_TOUCHPOINTS_SUBPANEL_TITLE',
        'top_buttons' => array(),
);


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['LeadContacts']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";
?>