<?php
$module_name = 'gtb_candidates';
$viewdefs[$module_name] = 
array (
  'base' => 
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
                'type' => 'fullname',
                'fields' => 
                array (
                  0 => 'salutation',
                  1 => 'first_name',
                  2 => 'last_name',
                ),
                'link' => true,
                'label' => 'LBL_LIST_NAME',
                'enabled' => true,
                'default' => true,
              ),
              1 => 
              array (
                'name' => 'title',
                'label' => 'LBL_TITLE',
                'default' => true,
                'enabled' => true,
              ),
              2 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
                'enabled' => true,
                'default' => true,
              ),
              3 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
                'enabled' => true,
                'default' => true,
              ),
              4 => 
              array (
                'name' => 'gtb_function',
                'label' => 'LBL_GTB_FUNCTION',
                'enabled' => true,
                'default' => true,
              ),
              5 => 
              array (
                'name' => 'date_modified',
                'enabled' => true,
                'default' => true,
              ),
              6 => 
              array (
                'name' => 'date_entered',
                'enabled' => true,
                'default' => true,
              ),
              7 => 
              array (
                'name' => 'gender',
                'label' => 'LBL_GENDER',
                'enabled' => true,
                'default' => false,
              ),
              8 => 
              array (
                'name' => 'phone_mobile',
                'label' => 'LBL_MOBILE_PHONE',
                'default' => false,
                'enabled' => true,
              ),
              9 => 
              array (
                'name' => 'email',
                'label' => 'LBL_EMAIL_ADDRESS',
                'link' => true,
                'default' => false,
                'enabled' => true,
              ),
              10 => 
              array (
                'name' => 'primary_address_country',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                'enabled' => true,
                'default' => false,
              ),
              11 => 
              array (
                'name' => 'functional_mobility',
                'label' => 'LBL_FUNCTIONAL_MOBILITY',
                'enabled' => true,
                'default' => false,
              ),
              12 => 
              array (
                'name' => 'oe_mobility',
                'label' => 'LBL_OE_MOBILITY',
                'enabled' => true,
                'default' => false,
              ),
              13 => 
              array (
                'name' => 'geo_mobility',
                'label' => 'LBL_GEO_MOBILITY',
                'enabled' => true,
                'default' => false,
              ),
              14 => 
              array (
                'name' => 'career_discussion',
                'label' => 'LBL_CAREER_DISCUSSION',
                'enabled' => true,
                'default' => false,
              ),
              15 => 
              array (
                'name' => 'lead_source',
                'label' => 'LBL_LEAD_SOURCE',
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
