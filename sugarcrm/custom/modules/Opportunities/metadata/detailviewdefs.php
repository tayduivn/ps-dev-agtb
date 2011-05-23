<?php
$viewdefs ['Opportunities'] = 
array (
  'DetailView' => 
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
          3 => 
          array (
            'customCode' => '<input title="{$APP.LBL_DUP_MERGE}" accesskey="M" class="button" onclick="this.form.return_module.value=\'Opportunities\';this.form.return_action.value=\'DetailView\';this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Step1\'; this.form.module.value=\'MergeRecords\';" name="button" value="{$APP.LBL_DUP_MERGE}" type="submit">',
          ),
          4 => 
          array (
            'customCode' => '<input title="{$MOD.LBL_SET_DEFAULT}" class="button" onclick="ibmO_showSetDefaults(\'{$MOD.LBL_SET_DEFAULT_TITLE}\'); return false;" name="button" value="{$MOD.LBL_SET_DEFAULT}" type="submit">',
          ),
        ),
        'headerTpl'=>'custom/modules/Opportunities/tpls/DetailViewHeader.tpl',
        'footerTpl'=>'custom/modules/Opportunities/tpls/DetailViewFooter.tpl',
      ),
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
      'LBL_PANEL_OVERVIEW' => 
      array (
        array (
          0 => 
          array (
            'name' => 'description',
            'nl2br' => true,
          ),
          1 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'name',
            'comment' => 'Name of the opportunity',
            'label' => 'LBL_OPPORTUNITY_NAME',
          ),
          1 => 
          array (
            'name' => 'contact_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTACT',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'sales_stage',
            'comment' => 'Indication of progression towards closure',
            'label' => 'LBL_SALES_STAGE',
          ),
          1 => 
          array (
            'name' => 'date_closed',
            'comment' => 'Expected or actual date the oppportunity will close',
            'label' => 'LBL_DATE_CLOSED',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'probability',
            'comment' => 'The probability of closure',
            'label' => 'LBL_PROBABILITY',
          ),
          1 => 
          array (
            'name' => 'reason_won_c',
            'label' => 'LBL_REASON_WON',
          ),
        ),
		array(
		  0 => '',
		  1 =>
          array (
            'name' => 'solution_codes_c',
            'studio' => 'visible',
            'label' => 'LBL_SOLUTION_CODES',
          ),
		),
        array (
          0 => 
          array (
            'name' => 'key_deal_c',
            'label' => 'LBL_KEY_DEAL',
          ),
          1 => 
          array (
            'name' => 'amount',
            'label' => '{$MOD.LBL_AMOUNT} ({$CURRENCY})',
          ),
        ),
        array (
          0 => array(
            'name' => 'related_opportunity_c',
            'label' => 'LBL_RELATED_OPPORTUNITY',
            'customCode' => '
{if !empty($fields.opportunity_id_c.value)}<a href="index.php?module=Opportunities&action=DetailView&record={$fields.opportunity_id_c.value}">{/if}
{$fields.related_opportunity_c.value}
{if !empty($fields.opportunity_id_c.value)}</a>{/if}{if !empty($fields.related_opportunity_description.value)}&nbsp;({$fields.related_opportunity_description.value}){/if}
'
          ),
          1 =>
          array (
            'name' => 'key_deal_comments_c',
            'label' => 'LBL_KEY_DEAL_COMMENTS',
          ),

        ),
		array(
			0 => '',
			1 => 
          array (
            'name' => 'tags',
          ),

			2 => array(
	            'name' => 'NEW_PANEL',
	            'label' => 'LBL_DETAILVIEW_PANEL1',
	            'default' => 'true',
			)
		),
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          1 => array (
			'name' => 'additional_team_members_c',
            'label' => 'LBL_ADDITIONAL_TEAM_MEMBERS',
		  ),

			2 => array(
	            'name' => 'NEW_PANEL',
	            'label' => 'LBL_PANEL_ASSIGNMENT',
	            'default' => 'false',
			)

        ),
/*
story 11237083 - remove opp type from layout
        array (
          0 => 
          array (
            'name' => 'opportunity_type',
            'comment' => 'Type of opportunity (ex: Existing, New)',
            'label' => 'LBL_TYPE',
          ),
          1 => '',
        ),
*/
        array (
          0 => 
          array (
            'name' => 'lead_source',
            'comment' => 'Source of the opportunity',
            'label' => 'LBL_LEAD_SOURCE',
          ),
          1 => 
          array (
            'name' => 'business_partner_c',
            'label' => 'LBL_BUSINESS_PARTNER',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'competitor_c',
            'label' => 'LBL_COMPETITOR',
          ),
          1 => 
          array (
            'name' => 'financing_sales_stage_c',
            'studio' => 'visible',
            'label' => 'LBL_FINANCING_SALES_STAGE',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'buying_behavior_c',
            'studio' => 'visible',
            'label' => 'LBL_BUYING_BEHAVIOR',
          ),
          1 => '',
        ),
        array (
          0 => 
          array (
            'name' => 'conditions_of_satisfaction_c',
            'studio' => 'visible',
            'label' => 'LBL_CONDITIONS_OF_SATISFACTION',
          ),
          1 => 
          array (
            'name' => 'reason_lost_c',
            'label' => 'LBL_REASON_LOST',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'is_restricted_c',
            'label' => 'LBL_IS_RESTRICTED',
          ),
          1 => 
          array (
            'name' => 'international_c',
            'label' => 'LBL_INTERNATIONAL',
          ),
        ),
        array (
          0 => 
          array (
            'name' => 'business_transaction_type_c',
            'label' => 'LBL_BUSINESS_TRANSACTION_TYPE',
          ),
          1 => 
          array (
            'name' => 'itar_compliance_c',
            'studio' => 'visible',
            'label' => 'LBL_ITAR_COMPLIANCE',
          ),
        ),

        array (
          0 =>           array (
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          ),
          1 =>           array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
          ),
        ),

      ),

    ),
  ),
);
?>
