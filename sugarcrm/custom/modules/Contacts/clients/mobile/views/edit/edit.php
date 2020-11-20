<?php
$viewdefs['Contacts']['mobile']['view']['edit'] =
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
              0 => 
              array (
                'name' => 'salutation',
                'comment' => 'Contact salutation (e.g., Mr, Ms)',
                'label' => 'LBL_SALUTATION',
              ),
              1 => 
              array (
                'name' => 'first_name',
                'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="15" maxlength="25" type="text" value="{$fields.first_name.value}">',
                'displayParams' => 
                array (
                  'wireless_edit_only' => true,
                ),
              ),
              2 => 
              array (
                'name' => 'last_name',
                'displayParams' => 
                array (
                  'required' => true,
                  'wireless_edit_only' => true,
                ),
              ),
              3 => 'title',
              4 => 
              array (
                'name' => 'gender_c',
                'label' => 'LBL_GENDER_C',
              ),
              5 => 
              array (
                'name' => 'gtb_cluster_c',
                'label' => 'LBL_GTB_CLUSTER_C',
              ),
              6 => 
              array (
                'name' => 'org_unit_c',
                'label' => 'LBL_ORG_UNIT_C',
              ),
              7 => 
              array (
                'name' => 'function_c',
                'label' => 'LBL_FUNCTION_C',
              ),
              8 => 'primary_address_country',
              9 => 'phone_mobile',
              10 => 'email',
              11 => 
              array (
                'name' => 'lead_source',
                'comment' => 'How did the contact come about',
                'label' => 'LBL_LEAD_SOURCE',
              ),
              12 => 'tag',
              13 => 
              array (
                'name' => 'functional_mobility_c',
                'label' => 'LBL_FUNCTIONAL_MOBILITY_C',
              ),
              14 => 
              array (
                'name' => 'oe_mobility_c',
                'label' => 'LBL_OE_MOBILITY_C',
              ),
              15 => 
              array (
                'name' => 'geo_mobility_c',
                'label' => 'LBL_GEO_MOBILITY_C',
              ),
              16 => 
              array (
                'name' => 'mobility_comments_c',
                'label' => 'LBL_MOBILITY_COMMENTS_C',
              ),
              17 => 
              array (
                'name' => 'target_roles_c',
                'label' => 'LBL_TARGET_ROLES_C',
              ),
              18 => 
              array (
                'name' => 'career_discussion_c',
                'label' => 'LBL_CAREER_DISCUSSION_C',
              ),
              19 => 
              array (
                'name' => 'availability_c',
                'label' => 'LBL_AVAILABILITY_C',
              ),
              20 => 'assigned_user_name',
              21 => 'team_name',
            ),
          ),
        ),
);
