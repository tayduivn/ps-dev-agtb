<?php
$module_name = 'gtb_candidates';
$viewdefs[$module_name] = 
array (
  'base' => 
  array (
    'view' => 
    array (
      'record' => 
      array (
        'buttons' => 
        array (
          0 => 
          array (
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
            'events' => 
            array (
              'click' => 'button:cancel_button:click',
            ),
          ),
          1 => 
          array (
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
          ),
          2 => 
          array (
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => 
            array (
              0 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:edit_button:click',
                'name' => 'edit_button',
                'label' => 'LBL_EDIT_BUTTON_LABEL',
                'acl_action' => 'edit',
              ),
              1 => 
              array (
                'type' => 'shareaction',
                'name' => 'share',
                'label' => 'LBL_RECORD_SHARE_BUTTON',
                'acl_action' => 'view',
              ),
              2 => 
              array (
                'type' => 'pdfaction',
                'name' => 'download-pdf',
                'label' => 'LBL_PDF_VIEW',
                'action' => 'download',
                'acl_action' => 'view',
              ),
              3 => 
              array (
                'type' => 'pdfaction',
                'name' => 'email-pdf',
                'label' => 'LBL_PDF_EMAIL',
                'action' => 'email',
                'acl_action' => 'view',
              ),
              4 => 
              array (
                'type' => 'divider',
              ),
              5 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:find_duplicates_button:click',
                'name' => 'find_duplicates_button',
                'label' => 'LBL_DUP_MERGE',
                'acl_action' => 'edit',
              ),
              6 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:duplicate_button:click',
                'name' => 'duplicate_button',
                'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                'acl_module' => 'gtb_candidates',
                'acl_action' => 'create',
              ),
              7 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:audit_button:click',
                'name' => 'audit_button',
                'label' => 'LNK_VIEW_CHANGE_LOG',
                'acl_action' => 'view',
              ),
              8 => 
              array (
                'type' => 'divider',
              ),
              9 => 
              array (
                'type' => 'rowaction',
                'event' => 'button:delete_button:click',
                'name' => 'delete_button',
                'label' => 'LBL_DELETE_BUTTON_LABEL',
                'acl_action' => 'delete',
              ),
              10 => 
              array (
                'type' => 'vcard',
                'name' => 'vcard_button',
                'label' => 'LBL_VCARD_DOWNLOAD',
                'acl_action' => 'edit',
              ),
            ),
          ),
          3 => 
          array (
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
          ),
        ),
        'panels' => 
        array (
          0 => 
          array (
            'name' => 'panel_header',
            'label' => 'LBL_RECORD_HEADER',
            'header' => true,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'picture',
                'type' => 'avatar',
                'width' => 42,
                'height' => 42,
                'dismiss_label' => true,
                'size' => 'large',
              ),
              1 => 
              array (
                'name' => 'name',
                'label' => 'LBL_NAME',
                'dismiss_label' => true,
                'type' => 'fullname',
                'fields' => 
                array (
                  0 => 'salutation',
                  1 => 'first_name',
                  2 => 'last_name',
                ),
              ),
              2 => 
              array (
                'name' => 'favorite',
                'label' => 'LBL_FAVORITE',
                'type' => 'favorite',
                'readonly' => true,
                'dismiss_label' => true,
              ),
              3 => 
              array (
                'name' => 'follow',
                'label' => 'LBL_FOLLOW',
                'type' => 'follow',
                'readonly' => true,
                'dismiss_label' => true,
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 'title',
              1 => 
              array (
                'name' => 'gender',
                'label' => 'LBL_GENDER',
              ),
              2 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
              ),
              3 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
              ),
              4 => 
              array (
                'name' => 'gtb_function',
                'label' => 'LBL_GTB_FUNCTION',
              ),
              5 => 
              array (
                'name' => 'primary_address_country',
                'comment' => 'Country for primary address',
                'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
              ),
              6 => 
              array (
                'name' => 'phone_mobile',
                'comment' => 'Mobile phone number of the contact',
                'label' => 'LBL_MOBILE_PHONE',
              ),
              7 => 'email',
              8 => 
              array (
                'name' => 'lead_source',
                'label' => 'LBL_LEAD_SOURCE',
              ),
              9 => 
              array (
                'name' => 'tag',
              ),
            ),
          ),
          2 => 
          array (
            'columns' => 2,
            'name' => 'panel_hidden',
            'label' => 'LBL_SHOW_MORE',
            'hide' => true,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' => 
            array (
              0 => 
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
                'span' => 12,
              ),
              1 => 
              array (
                'name' => 'functional_mobility',
                'label' => 'LBL_FUNCTIONAL_MOBILITY',
                'span' => 12,
              ),
              2 => 
              array (
                'name' => 'oe_mobility',
                'label' => 'LBL_OE_MOBILITY',
                'span' => 12,
              ),
              3 => 
              array (
                'name' => 'geo_mobility',
                'label' => 'LBL_GEO_MOBILITY',
                'span' => 12,
              ),
              4 => 
              array (
                'name' => 'mobility_comments',
                'studio' => 'visible',
                'label' => 'LBL_MOBILITY_COMMENTS',
                'span' => 12,
              ),
              5 => 
              array (
                'name' => 'target_roles',
                'studio' => 'visible',
                'label' => 'LBL_TARGET_ROLES',
                'span' => 12,
              ),
              6 => 
              array (
                'name' => 'career_discussion',
                'label' => 'LBL_CAREER_DISCUSSION',
              ),
              7 => 
              array (
              ),
            ),
          ),
          3 => 
          array (
            'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL2',
            'label' => 'LBL_RECORDVIEW_PANEL2',
            'columns' => 2,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'language_2',
                'label' => 'LBL_LANGUAGE',
              ),
              1 => 
              array (
                'name' => 'prof_level_1',
                'label' => 'LBL_PROF_LEVEL',
              ),
              2 => 
              array (
                'name' => 'language_1',
                'label' => 'LBL_LANGUAGE',
              ),
              3 => 
              array (
                'name' => 'prof_level_2',
                'label' => 'LBL_PROF_LEVEL',
              ),
              4 => 
              array (
                'name' => 'language_6',
                'label' => 'LBL_LANGUAGE',
              ),
              5 => 
              array (
                'name' => 'prof_level_3',
                'label' => 'LBL_PROF_LEVEL',
              ),
              6 => 
              array (
                'name' => 'language_5',
                'label' => 'LBL_LANGUAGE',
              ),
              7 => 
              array (
                'name' => 'prof_level_4',
                'label' => 'LBL_PROF_LEVEL',
              ),
              8 => 
              array (
                'name' => 'language_4',
                'label' => 'LBL_LANGUAGE',
              ),
              9 => 
              array (
                'name' => 'prof_level_5',
                'label' => 'LBL_PROF_LEVEL',
              ),
              10 => 
              array (
                'name' => 'language_3',
                'label' => 'LBL_LANGUAGE',
              ),
              11 => 
              array (
                'name' => 'prof_level_6',
                'label' => 'LBL_PROF_LEVEL',
              ),
            ),
          ),
          4 => 
          array (
            'newTab' => true,
            'panelDefault' => 'expanded',
            'name' => 'LBL_RECORDVIEW_PANEL1',
            'label' => 'LBL_RECORDVIEW_PANEL1',
            'columns' => 2,
            'placeholders' => 1,
            'fields' => 
            array (
              0 => 'assigned_user_name',
              1 => 
              array (
                'name' => 'date_modified_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_MODIFIED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_modified',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'modified_by_name',
                  ),
                ),
              ),
              2 => 
              array (
                'name' => 'team_name',
                'studio' => 
                array (
                  'portallistview' => false,
                  'portalrecordview' => false,
                ),
                'label' => 'LBL_TEAMS',
              ),
              3 => 
              array (
                'name' => 'date_entered_by',
                'readonly' => true,
                'inline' => true,
                'type' => 'fieldset',
                'label' => 'LBL_DATE_ENTERED',
                'fields' => 
                array (
                  0 => 
                  array (
                    'name' => 'date_entered',
                  ),
                  1 => 
                  array (
                    'type' => 'label',
                    'default_value' => 'LBL_BY',
                  ),
                  2 => 
                  array (
                    'name' => 'created_by_name',
                  ),
                ),
              ),
            ),
          ),
        ),
        'templateMeta' => 
        array (
          'useTabs' => true,
        ),
      ),
    ),
  ),
);
