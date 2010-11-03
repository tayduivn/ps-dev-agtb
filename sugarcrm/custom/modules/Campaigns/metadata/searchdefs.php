<?php
// created: 2010-10-06 01:04:26
$searchdefs['Campaigns'] = array (
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
  'layout' => 
  array (
    'basic_search' => 
    array (
      0 => 'name',
      1 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
      2 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
    'advanced_search' => 
    array (
      0 => 'name',
      1 => 
      array (
        'name' => 'start_date',
        'type' => 'date',
        'displayParams' => 
        array (
          'showFormats' => true,
        ),
      ),
      2 => 
      array (
        'name' => 'end_date',
        'type' => 'date',
        'displayParams' => 
        array (
          'showFormats' => true,
        ),
      ),
      3 => 'status',
      4 => 'campaign_type',
      5 => 
      array (
        'name' => 'assigned_user_id',
        'label' => 'LBL_ASSIGNED_TO',
        'type' => 'enum',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
      ),
      6 => array ('name' => 'favorites_only','label' => 'LBL_FAVORITES_FILTER','type' => 'bool',),
    ),
  ),
);
?>
