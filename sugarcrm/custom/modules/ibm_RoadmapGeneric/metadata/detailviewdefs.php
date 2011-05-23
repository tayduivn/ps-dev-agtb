<?php
$module_name = 'ibm_RoadmapGeneric';
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
          1 => '',
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
            'name' => 'account',
            'studio' => 'visible',
            'label' => 'LBL_ACCOUNT',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'exec_asssigned',
            'studio' => 'visible',
            'label' => 'LBL_EXEC_ASSSIGNED',
          ),
          1 => 
          array (
            'name' => 'help_needed',
            'label' => 'LBL_HELP_NEEDED',
          ),
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
