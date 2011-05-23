<?php
$module_name = 'ibm_RoadmapSD';
$viewdefs [$module_name] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => array('button_location' => 'none'),
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
      'lbl_editview_panel1' => 
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
          1 => '',
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'include_in_roadmaps',
            'label' => 'LBL_INCLUDE_IN_ROADMAPS',
          ),
          1 => 
          array (
            'name' => 'expected_conditional_agreement',
            'label' => 'LBL_EXPECTED_CONDITIONAL_AGREEMENT',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'winback',
            'label' => 'LBL_WINBACK',
          ),
          1 => 
          array (
            'name' => 'executive_owner',
            'studio' => 'visible',
            'label' => 'LBL_EXECUTIVE_OWNER',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'incentive_challenge',
            'label' => 'LBL_INCENTIVE_CHALLENGE',
          ),
          1 => '',
        ),
      ),
    ),
  ),
);
?>
