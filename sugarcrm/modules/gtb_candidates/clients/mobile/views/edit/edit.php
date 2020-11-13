<?php
$module_name = 'gtb_candidates';
$viewdefs[$module_name] = 
array (
  'mobile' => 
  array (
    'view' => 
    array (
      'edit' => 
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
                'name' => 'salutation',
                'comment' => 'Contact salutation (e.g., Mr, Ms)',
                'label' => 'LBL_SALUTATION',
              ),
              1 => 
              array (
                'name' => 'first_name',
                'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
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
                  'wireless_edit_only' => true,
                ),
              ),
              3 => 
              array (
                'name' => 'title',
                'comment' => 'The title of the contact',
                'label' => 'LBL_TITLE',
              ),
              4 => 
              array (
                'name' => 'gender',
                'label' => 'LBL_GENDER',
              ),
              5 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
              ),
              6 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
              ),
              7 => 
              array (
                'name' => 'gtb_function',
                'label' => 'LBL_GTB_FUNCTION',
              ),
              8 => 
              array (
                'name' => 'primary_address_country',
                'comment' => 'Country for primary address',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
              ),
              9 => 
              array (
                'name' => 'phone_mobile',
                'comment' => 'Mobile phone number of the contact',
                'label' => 'LBL_MOBILE_PHONE',
              ),
              10 => 'email',
              11 => 
              array (
                'name' => 'lead_source',
                'label' => 'LBL_LEAD_SOURCE',
              ),
              12 => 'tag',
              13 => 
              array (
                'name' => 'functional_mobility',
                'label' => 'LBL_FUNCTIONAL_MOBILITY',
              ),
              14 => 
              array (
                'name' => 'oe_mobility',
                'label' => 'LBL_OE_MOBILITY',
              ),
              15 => 
              array (
                'name' => 'geo_mobility',
                'label' => 'LBL_GEO_MOBILITY',
              ),
              16 => 
              array (
                'name' => 'mobility_comments',
                'studio' => 'visible',
                'label' => 'LBL_MOBILITY_COMMENTS',
              ),
              17 => 
              array (
                'name' => 'target_roles',
                'studio' => 'visible',
                'label' => 'LBL_TARGET_ROLES',
              ),
              18 => 
              array (
                'name' => 'career_discussion',
                'label' => 'LBL_CAREER_DISCUSSION',
              ),
              19 => 'assigned_user_name',
              20 => 'team_name',
            ),
          ),
        ),
      ),
    ),
  ),
);
