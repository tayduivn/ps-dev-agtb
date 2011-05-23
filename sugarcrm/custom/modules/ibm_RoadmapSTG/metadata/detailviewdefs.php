<?php
$module_name = 'ibm_RoadmapSTG';
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
            'name' => 'weekly_deal_dynamic',
            'label' => 'LBL_WEEKLY_DEAL_DYNAMIC',
          ),
          1 => 
          array (
            'name' => 'rdmp_ownership',
            'label' => 'LBL_RDMP_OWNERSHIP',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'challenge_deployed_to',
            'label' => 'LBL_CHALLENGE_DEPLOYED_TO',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'type_of_win_room_cvdm',
            'studio' => 'visible',
            'label' => 'LBL_TYPE_OF_WIN_ROOM_CVDM',
          ),
          1 => 
          array (
            'name' => 'cvdm_applied_submitted_to_win',
            'label' => 'LBL_CVDM_APPLIED_SUBMITTED_TO_WIN',
          ),
        ),
        5 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'is_this_an_oio',
            'label' => 'LBL_IS_THIS_AN_OIO',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'major_business_driver',
            'studio' => 'visible',
            'label' => 'LBL_MAJOR_BUSINESS_DRIVER',
          ),
          1 => 
          array (
            'name' => 'rollover_deferred_transaction',
            'label' => 'LBL_ROLLOVER_DEFERRED_TRANSACTION',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'key_win_loss',
            'studio' => 'visible',
            'label' => 'LBL_KEY_WIN_LOSS',
          ),
          1 => 
          array (
            'name' => 'express_seller_forecast',
            'studio' => 'visible',
            'label' => 'LBL_EXPRESS_SELLER_FORECAST',
          ),
        ),
      ),
      'lbl_detailview_panel3' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'default_small_opp',
            'studio' => 'visible',
            'label' => 'LBL_DEFAULT_SMALL_OPP',
          ),
          1 => 
          array (
            'name' => 'bill_date_passed',
            'studio' => 'visible',
            'label' => 'LBL_BILL_DATE_PASSED',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'override_small_opp',
            'label' => 'LBL_OVERRIDE_SMALL_OPP',
          ),
          1 => 
          array (
            'name' => 'bill_date_before_decision_date',
            'studio' => 'visible',
            'label' => 'LBL_BILL_DATE_BEFORE_DECISION_DATE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'age_of_opps',
            'studio' => 'visible',
            'label' => 'LBL_AGE_OF_OPPS',
          ),
          1 => '',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'decision_date_passed',
            'studio' => 'visible',
            'label' => 'LBL_DECISION_DATE_PASSED',
          ),
          1 => '',
        ),
      ),
      'lbl_detailview_panel4' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'bp_assessment',
            'label' => 'LBL_BP_ASSESSMENT',
          ),
          1 => 
          array (
            'name' => 'bp_relationship',
            'label' => 'LBL_BP_RELATIONSHIP',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'load_from_distributor_stock',
            'label' => 'LBL_LOAD_FROM_DISTRIBUTOR_STOCK',
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
