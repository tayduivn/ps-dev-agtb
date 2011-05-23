<?php
$module_name = 'ibm_RoadmapServices';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => array('buttons' => array()),
      //'form' => array('button_location' => 'none'),
      'maxColumns' => '2',
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
      'lbl_detailview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'imt_brand_exec_key_deal',
            'label' => 'LBL_IMT_BRAND_EXEC_KEY_DEAL',
          ),
          1 => 
          array (
            'name' => 'account',
            'studio' => 'visible',
            'label' => 'LBL_ACCOUNT',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'key_deal_comments',
            'studio' => 'visible',
            'label' => 'LBL_KEY_DEAL_COMMENTS',
          ),
          1 => 
          array (
            'name' => 'help_needed',
            'label' => 'LBL_HELP_NEEDED',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'exec_assigned',
            'studio' => 'visible',
            'label' => 'LBL_EXEC_ASSIGNED',
          ),
          1 => 
          array (
            'name' => 'practice_area',
            'label' => 'LBL_PRACTICE_AREA',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'signings_revenue',
            'label' => 'LBL_SIGNINGS_REVENUE',
          ),
          1 => 
          array (
            'name' => 'sector',
            'label' => 'LBL_SECTOR',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'term_years',
            'label' => 'LBL_TERM_YEARS',
          ),
          1 => 
          array (
            'name' => 'gross_profit',
            'label' => 'LBL_GROSS_PROFIT',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'staffing_timeframe',
            'studio' => 'visible',
            'label' => 'LBL_STAFFING_TIMEFRAME',
          ),
          1 => '',
        ),
      ),
      'default' => 
      array (
        0 => 
        array (
          0 => 'assigned_user_name',
          1 => 'team_name',
        ),
      ),
    ),
  ),
);
?>
