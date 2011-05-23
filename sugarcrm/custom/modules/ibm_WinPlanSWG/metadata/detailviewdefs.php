<?php
$module_name = 'ibm_WinPlanSWG';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => array (
          	'customCode' => '{if empty($fields.assigned_user_id.value) || $current_user->id == $fields.assigned_user_id.value}
          		<input type="submit" value="{$APP.LBL_EDIT_BUTTON_LABEL}" id="edit_button" name="Edit" onclick="this.form.return_module.value=\'ibm_WinPlanSWG\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'EditView\';" class="button primary" accesskey="E" title="{$APP.LBL_EDIT_TITLE_LABEL}"/>
				<input type="submit" value="{$APP.LBL_DELETE_BUTTON_LABEL}" name="Delete" onclick="this.form.return_module.value=\'Opportunities\'; this.form.return_action.value=\'ListView\'; this.form.action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');" class="button" accesskey="{$APP.LBL_DELETE_BUTTON_KEY}" title="{$APP.LBL_DELETE_BUTTON_TITLE}"/>
          		{/if}',
          ),
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '25',
          'field' => '25',
        ),
        1 => 
        array (
          'label' => '25',
          'field' => '25',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'label' => 'LBL_DESCRIPTION',
          ),
          1 => 
          array (
            'name' => 'status_c',
            'studio' => 'visible',
            'label' => 'LBL_STATUS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'date_approved_c',
            'label' => 'LBL_DATE_APPROVED',
          ),
          1 => 
          array (
            'name' => 'approver_c',
            'studio' => 'visible',
            'label' => 'LBL_APPROVER',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => '',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'client_describe_initiative',
            'studio' => 'visible',
            'label' => 'LBL_CLIENT_DESCRIBE_INITIATIVE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'measurable_business_goal',
            'label' => 'LBL_MEASURABLE_BUSINESS_GOAL',
          ),
          1 => 
          array (
            'name' => 'achieved_through_actions',
            'label' => 'LBL_ACHIEVED_THROUGH_ACTIONS',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'business_domain_function',
            'label' => 'LBL_BUSINESS_DOMAIN_FUNCTION',
          ),
          1 => 
          array (
            'name' => 'achieved_through_projects',
            'label' => 'LBL_ACHIEVED_THROUGH_PROJECTS',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'client_need_to_act_now',
            'studio' => 'visible',
            'label' => 'LBL_CLIENT_NEED_TO_ACT_NOW',
          ),
          1 => '',
        ),
        7 => 
        array (
          0 => '',
          1 => '',
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'list_ibm_caps',
            'studio' => 'visible',
            'label' => 'LBL_LIST_IBM_CAPS',
          ),
          1 => 
          array (
            'name' => 'ibm_team_view',
            'studio' => 'visible',
            'label' => 'LBL_IBM_TEAM_VIEW',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'how_client_makes_buying_decisi',
            'studio' => 'visible',
            'label' => 'LBL_HOW_CLIENT_MAKES_BUYING_DECISI',
          ),
          1 => '',
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'opportunities_ibm_winplanswg_name',
          ),
        ),
      ),
      'lbl_detailview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'is_opportunity_real',
            'studio' => 'visible',
            'label' => 'LBL_IS_OPPORTUNITY_REAL',
            'customLabel' => '<b>{$MOD.LBL_IS_OPPORTUNITY_REAL}</b>',
          ),
          1 => 
          array (
            'name' => 'solution_meet_expectations',
            'studio' => 'visible',
            'label' => 'LBL_SOLUTION_MEET_EXPECTATIONS',
            'customLabel' => '<b>{$MOD.LBL_SOLUTION_MEET_EXPECTATIONS}</b>',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'need_to_act_now',
            'studio' => 'visible',
            'label' => 'LBL_NEED_TO_ACT_NOW',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_NEED_TO_ACT_NOW}</div>',
          ),
          1 => 
          array (
            'name' => 'meet_exceed_expectations',
            'studio' => 'visible',
            'label' => 'LBL_MEET_EXCEED_EXPECTATIONS',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_MEET_EXCEED_EXPECTATIONS}</div>',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'client_have_funding_available',
            'studio' => 'visible',
            'label' => 'LBL_CLIENT_HAVE_FUNDING_AVAILABLE',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_CLIENT_HAVE_FUNDING_AVAILABLE}</div>',
          ),
          1 => 
          array (
            'name' => 'strong_value_proposition',
            'studio' => 'visible',
            'label' => 'LBL_STRONG_VALUE_PROPOSITION',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_STRONG_VALUE_PROPOSITION}</div>',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'can_offer_best_solution',
            'studio' => 'visible',
            'label' => 'LBL_CAN_OFFER_BEST_SOLUTION',
            'customLabel' => '<b>{$MOD.LBL_CAN_OFFER_BEST_SOLUTION}</b>',
          ),
          1 => 
          array (
            'name' => 'want_to_pursue',
            'studio' => 'visible',
            'label' => 'LBL_WANT_TO_PURSUE',
            'customLabel' => '<b>{$MOD.LBL_WANT_TO_PURSUE}</b>',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'know_business_caps',
            'studio' => 'visible',
            'label' => 'LBL_KNOW_BUSINESS_CAPS',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_KNOW_BUSINESS_CAPS}</div>',
          ),
          1 => 
          array (
            'name' => 'impact_other_engagements',
            'studio' => 'visible',
            'label' => 'LBL_IMPACT_OTHER_ENGAGEMENTS',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_IMPACT_OTHER_ENGAGEMENTS}</div>',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'have_solution_that_fits',
            'studio' => 'visible',
            'label' => 'LBL_HAVE_SOLUTION_THAT_FITS',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_HAVE_SOLUTION_THAT_FITS}</div>',
          ),
          1 => 
          array (
            'name' => 'support_all_ibm_units',
            'studio' => 'visible',
            'label' => 'LBL_SUPPORT_ALL_IBM_UNITS',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_SUPPORT_ALL_IBM_UNITS}</div>',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'diffed_ibm_team_caps',
            'studio' => 'visible',
            'label' => 'LBL_DIFFED_IBM_TEAM_CAPS',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_DIFFED_IBM_TEAM_CAPS}</div>',
          ),
          1 => 
          array (
            'name' => 'strong_winplan',
            'studio' => 'visible',
            'label' => 'LBL_STRONG_WINPLAN',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_STRONG_WINPLAN}</div>',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'client_want_solution',
            'studio' => 'visible',
            'label' => 'LBL_CLIENT_WANT_SOLUTION',
            'customLabel' => '<b>{$MOD.LBL_CLIENT_WANT_SOLUTION}</b>',
          ),
          1 => 
          array (
            'name' => 'worth_pursuing',
            'studio' => 'visible',
            'label' => 'LBL_WORTH_PURSUING',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_WORTH_PURSUING}</div>',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'strong_ibm_client_relationship',
            'studio' => 'visible',
            'label' => 'LBL_STRONG_IBM_CLIENT_RELATIONSHIP',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_STRONG_IBM_CLIENT_RELATIONSHIP}</div>',
          ),
          1 => '',
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'know_how_decision_made',
            'studio' => 'visible',
            'label' => 'LBL_KNOW_HOW_DECISION_MADE',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_KNOW_HOW_DECISION_MADE}</div>',
          ),
          1 => '',
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'strong_leader_support',
            'studio' => 'visible',
            'label' => 'LBL_STRONG_LEADER_SUPPORT',
            'customLabel' => '<div style="padding-left: 15px;">{$MOD.LBL_STRONG_LEADER_SUPPORT}</div>',
          ),
          1 => '',
        ),
        11 => 
        array (
          0 => '',
          1 => '',
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
        ),
      ),
    ),
  ),
);
?>
