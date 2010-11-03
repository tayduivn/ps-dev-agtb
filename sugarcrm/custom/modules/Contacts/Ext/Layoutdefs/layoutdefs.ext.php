<?php 
 //WARNING: The contents of this file are auto-generated


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Contacts']['subpanel_setup']['cases']['sort_order'] = 'desc';
$layout_defs['Contacts']['subpanel_setup']['cases']['sort_by'] = 'cases.date_entered';
$layout_defs['Contacts']['subpanel_setup']['bugs']['sort_order'] = 'desc';
$layout_defs['Contacts']['subpanel_setup']['bugs']['sort_by'] = 'bugs.date_entered';


//auto-generated file DO NOT EDIT
$layout_defs['Contacts']['subpanel_setup']['contacts']['override_subpanel_name'] = 'ContactForContacts';


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Contacts']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";

// created: 2010-07-27 14:41:48
$layout_defs["Contacts"]["subpanel_setup"]["contacts_orders"] = array (
  'order' => 100,
  'module' => 'Orders',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_CONTACTS_ORDERS_FROM_ORDERS_TITLE',
  'get_subpanel_data' => 'contacts_orders',
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


//auto-generated file DO NOT EDIT
$layout_defs['Contacts']['subpanel_setup']['leads']['override_subpanel_name'] = 'Contactdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Contacts']['subpanel_setup']['contacts']['override_subpanel_name'] = 'ContactForContacts';


//auto-generated file DO NOT EDIT
$layout_defs['Contacts']['subpanel_setup']['contacts_orders']['override_subpanel_name'] = 'Contactdefault';

?>