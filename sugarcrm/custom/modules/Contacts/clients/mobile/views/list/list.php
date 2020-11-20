<?php
$viewdefs['Contacts']['mobile']['view']['list']['panels'] =
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
                'name' => 'gtb_cluster_c',
                'label' => 'LBL_GTB_CLUSTER_C',
                'enabled' => true,
                'default' => true,
              ),
              2 => 
              array (
                'name' => 'email',
                'enabled' => true,
                'default' => false,
              ),
              3 => 
              array (
                'name' => 'function_c',
                'label' => 'LBL_FUNCTION_C',
                'enabled' => true,
                'default' => false,
              ),
              4 => 
              array (
                'name' => 'phone_mobile',
                'enabled' => true,
                'default' => false,
              ),
              5 => 
              array (
                'name' => 'org_unit_c',
                'label' => 'LBL_ORG_UNIT_C',
                'enabled' => true,
                'default' => false,
              ),
              6 => 
              array (
                'name' => 'primary_address_country',
                'enabled' => true,
                'default' => false,
              ),
              7 => 
              array (
                'name' => 'title',
                'label' => 'LBL_TITLE',
                'enabled' => true,
                'default' => false,
              ),
            ),
          ),
);
