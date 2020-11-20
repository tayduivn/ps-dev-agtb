<?php
$viewdefs['Contacts']['mobile']['view']['details'] =
      array (
        'templateMeta' => 
        array (
          'maxColumns' => '1',
          'widths' => 
          array (
            0 => 
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
              0 => 'full_name',
              1 => 'title',
              2 => 
              array (
                'name' => 'gender_c',
                'label' => 'LBL_GENDER_C',
              ),
              3 => 
              array (
                'name' => 'gtb_cluster_c',
                'label' => 'LBL_GTB_CLUSTER_C',
              ),
              4 => 
              array (
                'name' => 'org_unit_c',
                'label' => 'LBL_ORG_UNIT_C',
              ),
              5 => 
              array (
                'name' => 'function_c',
                'label' => 'LBL_FUNCTION_C',
              ),
              6 => 'primary_address_country',
              7 => 'phone_mobile',
              8 => 'email',
              9 => 
              array (
                'name' => 'lead_source',
                'comment' => 'How did the contact come about',
                'label' => 'LBL_LEAD_SOURCE',
              ),
              10 => 'tag',
              11 => 
              array (
                'name' => 'functional_mobility_c',
                'label' => 'LBL_FUNCTIONAL_MOBILITY_C',
              ),
              12 => 
              array (
                'name' => 'oe_mobility_c',
                'label' => 'LBL_OE_MOBILITY_C',
              ),
              13 => 
              array (
                'name' => 'geo_mobility_c',
                'label' => 'LBL_GEO_MOBILITY_C',
              ),
              14 => 
              array (
                'name' => 'mobility_comments_c',
                'label' => 'LBL_MOBILITY_COMMENTS_C',
              ),
              15 => 
              array (
                'name' => 'target_roles_c',
                'label' => 'LBL_TARGET_ROLES_C',
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
                'name' => 'career_discussion_c',
                'label' => 'LBL_CAREER_DISCUSSION_C',
              ),
              18 => 
              array (
                'name' => 'availability_c',
                'label' => 'LBL_AVAILABILITY_C',
              ),
              19 => 'picture',
              20 => 'assigned_user_name',
              21 => 
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
              22 => 
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
              23 => 'team_name',
            ),
          ),
        ),
);
