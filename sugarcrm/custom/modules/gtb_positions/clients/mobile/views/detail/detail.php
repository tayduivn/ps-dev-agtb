<?php
$module_name = 'gtb_positions';
$viewdefs[$module_name]['mobile']['view']['detail'] =
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
              0 => 'name',
              1 => 
              array (
                'name' => 'pos_function',
                'label' => 'LBL_POS_FUNCTION',
              ),
              2 => 
              array (
                'name' => 'region',
                'label' => 'LBL_REGION',
              ),
              3 => 
              array (
                'name' => 'org_unit',
                'label' => 'LBL_ORG_UNIT',
              ),
              4 => 
              array (
                'name' => 'location',
                'label' => 'LBL_LOCATION',
              ),
              5 => 
              array (
                'name' => 'gtb_cluster',
                'label' => 'LBL_GTB_CLUSTER',
              ),
              6 => 
              array (
                'name' => 'gtb_source',
                'label' => 'LBL_GTB_SOURCE',
              ),
              7 => 
              array (
                'name' => 'gtb_contacts_gtb_positions_1_name',
                'label' => 'LBL_GTB_CONTACTS_GTB_POSITIONS_1_FROM_GTB_CONTACTS_TITLE',
              ),
              8 => 'tag',
              9 => 
              array (
                'name' => 'status',
                'label' => 'LBL_STATUS',
              ),
              10 => 
              array (
                'name' => 'process_step',
                'label' => 'LBL_PROCESS_STEP',
              ),
              11 => 
              array (
                'name' => 'real_position',
                'label' => 'LBL_REAL_POSITION',
              ),
              12 => 
              array (
                'name' => 'description',
                'comment' => 'Full text of the note',
                'studio' => 'visible',
                'label' => 'LBL_DESCRIPTION',
              ),
              13 => 
              array (
                'name' => 'reason_not_filled',
                'label' => 'LBL_REASON_NOT_FILLED',
              ),
              14 => 'assigned_user_name',
              15 => 
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
              16 => 
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
              17 => 'team_name',
            ),
          ),
        ),
);
