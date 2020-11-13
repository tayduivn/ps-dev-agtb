<?php
$module_name = 'gtb_candidates';
$viewdefs[$module_name] = 
array (
  'mobile' => 
  array (
    'view' => 
    array (
      'list' => 
      array (
        'panels' => 
        array (
          0 => 
          array (
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'label' => 'LBL_NAME',
                'default' => true,
                'enabled' => true,
                'link' => true,
                'related_fields' => 
                array (
                  0 => 'first_name',
                  1 => 'last_name',
                  2 => 'salutation',
                ),
              ),
              1 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
                'enabled' => true,
                'default' => true,
              ),
              2 => 
              array (
                'name' => 'title',
                'label' => 'LBL_TITLE',
                'enabled' => true,
                'default' => false,
              ),
              3 => 
              array (
                'name' => 'assigned_user_name',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'default' => false,
                'enabled' => true,
                'link' => true,
              ),
              4 => 
              array (
                'name' => 'gtb_function',
                'label' => 'LBL_GTB_FUNCTION',
                'enabled' => true,
                'default' => false,
              ),
              5 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
                'enabled' => true,
                'default' => false,
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);
