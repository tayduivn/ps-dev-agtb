<?php
$module_name = 'ibm_WinPlanGeneric';
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
          		<input type="submit" value="{$APP.LBL_EDIT_BUTTON_LABEL}" id="edit_button" name="Edit" onclick="this.form.return_module.value=\'ibm_WinPlanGeneric\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'EditView\';" class="button primary" accesskey="E" title="{$APP.LBL_EDIT_TITLE_LABEL}"/>
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
          'label' => '20',
          'field' => '20',
        ),
        1 => 
        array (
          'label' => '20',
          'field' => '20',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'lbl_detailview_panel6' => 
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
          0 => '',
          1 => '',
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'roi_tco_analysis_completed',
            'studio' => 'visible',
            'label' => 'LBL_ROI_TCO_ANALYSIS_COMPLETED',
          ),
          1 => 
          array (
            'name' => 'signed_contract',
            'studio' => 'visible',
            'label' => 'LBL_SIGNED_CONTRACT',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'proposal_deliver_reviewer',
            'label' => 'LBL_PROPOSAL_DELIVER_REVIEWER',
          ),
          1 => 
          array (
            'name' => 'tech_solution_finalized',
            'studio' => 'visible',
            'label' => 'LBL_TECH_SOLUTION_FINALIZED',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'proposal_deliver_client',
            'label' => 'LBL_PROPOSAL_DELIVER_CLIENT',
          ),
          1 => 
          array (
            'name' => 'cross_ibm_on_track',
            'studio' => 'visible',
            'label' => 'LBL_CROSS_IBM_ON_TRACK',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'client_budget_secured',
            'studio' => 'visible',
            'label' => 'LBL_CLIENT_BUDGET_SECURED',
          ),
          1 => 
          array (
            'name' => 'solution_assurance_complete',
            'studio' => 'visible',
            'label' => 'LBL_SOLUTION_ASSURANCE_COMPLETE',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'price_agreement_reached',
            'studio' => 'visible',
            'label' => 'LBL_PRICE_AGREEMENT_REACHED',
          ),
          1 => 
          array (
            'name' => 'key_programs_plays',
            'studio' => 'visible',
            'label' => 'LBL_KEY_PROGRAMS_PLAYS',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'opportunities_ibm_winplangeneric_name',
          ),
        ),
      ),
      'lbl_detailview_panel5' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'cvdm_session_completed',
            'label' => 'LBL_CVDM_SESSION_COMPLETED',
          ),
          1 => 
          array (
            'name' => 'compelling_reason_to_act',
            'studio' => 'visible',
            'label' => 'LBL_COMPELLING_REASON_TO_ACT',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'cvdm_status',
            'studio' => 'visible',
            'label' => 'LBL_CVDM_STATUS',
          ),
          1 => 
          array (
            'name' => 'reason_to_act_description',
            'studio' => 'visible',
            'label' => 'LBL_REASON_TO_ACT_DESCRIPTION',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'ibm_executive_sponsor_user_c',
            'studio' => 'visible',
            'label' => 'LBL_IBM_EXECUTIVE_SPONSOR_USER',
          ),
          1 => 
          array (
            'name' => 'unique_business_value',
            'studio' => 'visible',
            'label' => 'LBL_UNIQUE_BUSINESS_VALUE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'access_to_power',
            'studio' => 'visible',
            'label' => 'LBL_ACCESS_TO_POWER',
          ),
          1 => 
          array (
            'name' => 'unique_value_description',
            'studio' => 'visible',
            'label' => 'LBL_UNIQUE_VALUE_DESCRIPTION',
          ),
        ),
      ),
      'lbl_detailview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'approver_c',
            'studio' => 'visible',
            'label' => 'LBL_APPROVER',
          ),
          1 => '',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'date_approved_c',
            'label' => 'LBL_DATE_APPROVED',
          ),
          1 => '',
        ),
        2 => 
        array (
          0 => '',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
      ),
    ),
  ),
);
?>
