<?php
$module_name = 'gtb_candidates';
$viewdefs[$module_name] = 
array (
  'mobile' => 
  array (
    'view' => 
    array (
      'detail' => 
      array (
        'templateMeta' => 
        array (
          'form' => 
          array (
            'buttons' => 
            array (
              0 => 'EDIT',
              1 => 'DUPLICATE',
              2 => 'DELETE',
            ),
          ),
          'maxColumns' => '1',
          'widths' => 
          array (
            0 => 
            array (
              'label' => '10',
              'field' => '30',
            ),
            1 => 
            array (
              'label' => '10',
              'field' => '30',
            ),
          ),
          'useTabs' => false,
        ),
        'panels' => 
        array (
          0 => 
          array (
            'label' => 'LBL_PANEL_DEFAULT',
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_PANEL_DEFAULT',
            'columns' => '1',
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'full_name',
                'label' => 'LBL_NAME',
              ),
              1 => 
              array (
                'name' => 'title',
                'comment' => 'The title of the contact',
                'label' => 'LBL_TITLE',
              ),
              2 => 
              array (
                'name' => 'gender',
                'label' => 'LBL_GENDER',
              ),
              3 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
              ),
              4 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
              ),
              5 => 
              array (
                'name' => 'gtb_function',
                'label' => 'LBL_GTB_FUNCTION',
              ),
              6 => 
              array (
                'name' => 'primary_address_country',
                'comment' => 'Country for primary address',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
              ),
              7 => 
              array (
                'name' => 'phone_mobile',
                'comment' => 'Mobile phone number of the contact',
                'label' => 'LBL_MOBILE_PHONE',
              ),
              8 => 'email',
              9 => 
              array (
                'name' => 'lead_source',
                'label' => 'LBL_LEAD_SOURCE',
              ),
              10 => 'tag',
              11 => 
              array (
                'name' => 'functional_mobility',
                'label' => 'LBL_FUNCTIONAL_MOBILITY',
              ),
              12 => 
              array (
                'name' => 'oe_mobility',
                'label' => 'LBL_OE_MOBILITY',
              ),
              13 => 
              array (
                'name' => 'geo_mobility',
                'label' => 'LBL_GEO_MOBILITY',
              ),
              14 => 
              array (
                'name' => 'mobility_comments',
                'studio' => 'visible',
                'label' => 'LBL_MOBILITY_COMMENTS',
              ),
              15 => 
              array (
                'name' => 'target_roles',
                'studio' => 'visible',
                'label' => 'LBL_TARGET_ROLES',
              ),
              16 => 
              array (
                'name' => 'commentlog',
                'displayParams' => 
                array (
                  'type' => 'commentlog',
                  'fields' => 
                  array (
                    0 => 'entry',
                    1 => 'date_entered',
                    2 => 'created_by_name',
                  ),
                  'max_num' => 100,
                ),
                'studio' => 
                array (
                  'listview' => false,
                  'recordview' => true,
                  'wirelesseditview' => false,
                  'wirelessdetailview' => true,
                  'wirelesslistview' => false,
                  'wireless_basic_search' => false,
                  'wireless_advanced_search' => false,
                ),
                'label' => 'LBL_COMMENTLOG',
              ),
              17 => 
              array (
                'name' => 'career_discussion',
                'label' => 'LBL_CAREER_DISCUSSION',
              ),
              18 => 'assigned_user_name',
              19 => 
              array (
                'name' => 'date_entered',
                'comment' => 'Date record created',
                'studio' => 
                array (
                  'portaleditview' => false,
                ),
                'readonly' => true,
                'label' => 'LBL_DATE_ENTERED',
              ),
              20 => 
              array (
                'name' => 'date_modified',
                'comment' => 'Date record last modified',
                'studio' => 
                array (
                  'portaleditview' => false,
                ),
                'readonly' => true,
                'label' => 'LBL_DATE_MODIFIED',
              ),
              21 => 'team_name',
            ),
          ),
        ),
      ),
    ),
  ),
);
