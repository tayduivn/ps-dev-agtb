<?php
// created: 2010-10-10 14:52:02
$subpanel_layout['list_fields'] = array (
  'name' => 
  array (
    'vname' => 'LBL_LIST_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => '28%',
    'sort_by' => 'products.name',
    'default' => true,
  ),
  'status' => 
  array (
    'vname' => 'LBL_LIST_STATUS',
    'width' => '8%',
    'default' => true,
  ),
  'date_purchased' => 
  array (
    'vname' => 'LBL_LIST_DATE_PURCHASED',
    'width' => '10%',
    'default' => true,
  ),
  'quantity' => 
  array (
    'type' => 'int',
    'vname' => 'LBL_QUANTITY',
    'width' => '10%',
    'default' => true,
  ),
  'discount_price' => 
  array (
    'type' => 'currency',
    'vname' => 'LBL_DISCOUNT_PRICE',
    'currency_format' => true,
    'width' => '10%',
    'default' => true,
  ),
  'edit_button' => 
  array (
    'vname' => 'LBL_EDIT_BUTTON',
    'widget_class' => 'SubPanelEditButton',
    'module' => 'Products',
    'width' => '4%',
    'default' => true,
  ),
  'remove_button' => 
  array (
    'vname' => 'LBL_REMOVE',
    'widget_class' => 'SubPanelRemoveButton',
    'module' => 'Leads',
    'width' => '4%',
    'default' => true,
  ),
  'discount_usdollar' => 
  array (
    'usage' => 'query_only',
  ),
);
?>
