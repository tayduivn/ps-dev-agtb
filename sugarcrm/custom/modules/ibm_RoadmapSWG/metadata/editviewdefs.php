<?php
$module_name = 'ibm_RoadmapSWG';
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
      'syncDetailEditViews' => true,
    ),
    'panels' => 
    array (
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'key_deal',
            'label' => 'LBL_KEY_DEAL',
          ),
          1 => 
          array (
            'name' => 'deal_status',
            'studio' => 'visible',
            'label' => 'LBL_DEAL_STATUS',
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
        ),
      ),
      'lbl_editview_panel2' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'executive_assigned',
            'studio' => 'visible',
            'label' => 'LBL_EXECUTIVE_ASSIGNED',
          ),
          1 => 
          array (
            'name' => 'help_needed',
            'label' => 'LBL_HELP_NEEDED',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'deal_maker',
            'studio' => 'visible',
            'label' => 'LBL_DEAL_MAKER',
          ),
          1 => 
          array (
            'name' => 'swg_incentive_assigned',
            'studio' => 'visible',
            'label' => 'LBL_SWG_INCENTIVE_ASSIGNED',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'special_bonus',
            'label' => 'LBL_SPECIAL_BONUS',
          ),
          1 => 
          array (
            'name' => 'slipped_deal_prev_qtr',
            'studio' => 'visible',
            'label' => 'LBL_SLIPPED_DEAL_PREV_QTR',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'outsourcing_type_c',
            'studio' => 'visible',
            'label' => 'LBL_OUTSOURCING_TYPE',
          ),
          1 => 
          array (
            'name' => 'contract_type_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTRACT_TYPE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'outsourcing_values_cto_c',
            'label' => 'LBL_OUTSOURCING_VALUES_CTO',
          ),
          1 => 
          array (
            'name' => 'ela_c',
            'label' => 'LBL_ELA',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'outsourcing_total_csv_c',
            'label' => 'LBL_OUTSOURCING_TOTAL_CSV',
          ),
          1 => '',
        ),
      ),
      'lbl_editview_panel3' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'ssr_c',
            'studio' => 'visible',
            'label' => 'LBL_SSR',
          ),
          1 => 
          array (
            'name' => 'roo_c',
            'studio' => 'visible',
            'label' => 'LBL_ROO',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'ssr_manager_c',
            'studio' => 'visible',
            'label' => 'LBL_SSR_MANAGER',
          ),
          1 => 
          array (
            'name' => 'roo_manager_c',
            'studio' => 'visible',
            'label' => 'LBL_ROO_MANAGER',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'industry_solution_ssr_c',
            'studio' => 'visible',
            'label' => 'LBL_INDUSTRY_SOLUTION_SSR',
          ),
          1 => 
          array (
            'name' => 'br_sr_c',
            'studio' => 'visible',
            'label' => 'LBL_BR_SR',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'bp_id_name_c',
            'studio' => 'visible',
            'label' => 'LBL_BP_ID_NAME',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'tech_leader_c',
            'studio' => 'visible',
            'label' => 'LBL_TECH_LEADER',
          ),
          1 => 
          array (
            'name' => 'tech_crit_sit_c',
            'studio' => 'visible',
            'label' => 'LBL_TECH_CRIT_SIT',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'tech_manager_c',
            'studio' => 'visible',
            'label' => 'LBL_TECH_MANAGER',
          ),
          1 => '',
        ),
      ),
      'lbl_editview_panel4' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'compelling_reason_to_act_c',
            'label' => 'LBL_COMPELLING_REASON_TO_ACT',
          ),
          1 => 
          array (
            'name' => 'qa_required_and_completed_c',
            'studio' => 'visible',
            'label' => 'LBL_QA_REQUIRED_AND_COMPLETED',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'why_eq_will_not_happen_c',
            'label' => 'LBL_WHY_EQ_WILL_NOT_HAPPEN',
          ),
          1 => 
          array (
            'name' => 'special_bid_submitted_c',
            'studio' => 'visible',
            'label' => 'LBL_SPECIAL_BID_SUBMITTED',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'met_power_sponsor_and_sent_c',
            'label' => 'LBL_MET_POWER_SPONSOR_AND_SENT',
          ),
          1 => 
          array (
            'name' => 'credit_check_received_c',
            'label' => 'LBL_CREDIT_CHECK_RECEIVED',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'agreed_soe_and_close_date_c',
            'label' => 'LBL_AGREED_SOE_AND_CLOSE_DATE',
          ),
          1 => 
          array (
            'name' => 'pa_validated_c',
            'label' => 'LBL_PA_VALIDATED',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'preliminary_proposal_c',
            'label' => 'LBL_PRELIMINARY_PROPOSAL',
          ),
          1 => 
          array (
            'name' => 'special_bid_approved_c',
            'studio' => 'visible',
            'label' => 'LBL_SPECIAL_BID_APPROVED',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'direct_order_c',
            'label' => 'LBL_DIRECT_ORDER',
          ),
          1 => 
          array (
            'name' => 'deal_clinics_date_c',
            'label' => 'LBL_DEAL_CLINICS_DATE',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'deal_clinics_c',
            'studio' => 'visible',
            'label' => 'LBL_DEAL_CLINICS',
          ),
          1 => '',
        ),
      ),
      'lbl_editview_panel5' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'services_attached_c',
            'label' => 'LBL_SERVICES_ATTACHED',
          ),
          1 => 
          array (
            'name' => 'tda_needed_c',
            'label' => 'LBL_TDA_NEEDED',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'services_closure_c',
            'studio' => 'visible',
            'label' => 'LBL_SERVICES_CLOSURE',
          ),
          1 => 
          array (
            'name' => 'tda_complete_c',
            'label' => 'LBL_TDA_COMPLETE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'services_category_c',
            'studio' => 'visible',
            'label' => 'LBL_SERVICES_CATEGORY',
          ),
          1 => 
          array (
            'name' => 'tech_status_dropdown_c',
            'studio' => 'visible',
            'label' => 'LBL_TECH_STATUS_DROPDOWN',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'fulfillment_channel_c',
            'studio' => 'visible',
            'label' => 'LBL_FULFILLMENT_CHANNEL',
          ),
          1 => 
          array (
            'name' => 'risk_level_c',
            'studio' => 'visible',
            'label' => 'LBL_RISK_LEVEL',
          ),
        ),
      ),
    ),
  ),
);
?>
