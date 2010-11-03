<?php
$viewdefs ['Opportunities'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'SAVE',
          1 => 'CANCEL',
          2 => 
          array (
            'customCode' => '{if $SHOW_REMOVE_DISCOUNT == 1 && $fields.discount_pending_c.value == 1 || $fields.discount_approved_c.value == 1}<input title="Remove Discount" accesskey="R" class="button" onclick="this.form.return_module.value=\'Opportunities\';this.form.return_action.value=\'EditView\';this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'removediscount\'; this.form.module.value=\'Opportunities\';" name="button" value="Remove Discount" type="submit">{/if}',
          ),
          3 => 
          array (
            'customCode' => '<input title="Send Renewal Email" accessKey="R" type="button" class="button" onClick="document.location=\'index.php?module=Opportunities&action=RenewalEmail&record={$fields.id.value}\'" name="send_email" value="Send Renewal Email">',
          ),
        ),
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
      'javascript' => '{$PROBABILITY_SCRIPT}',
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
            'name' => 'name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_OPPORTUNITY_NAME',
          ),
          1 => 
          array (
            'name' => 'currency_id',
            'label' => 'LBL_CURRENCY',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
          1 => 
          array (
            'name' => 'amount',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_AMOUNT',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_code_c',
            'label' => 'LBL_DISCOUNT_CODE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'opportunity_type',
            'label' => 'LBL_TYPE',
          ),
          1 => 
          array (
            'name' => 'date_closed',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_DATE_CLOSED',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'operating_system',
            'label' => 'LBL_OPERATING_SYSTEM',
          ),
          1 => 
          array (
            'name' => 'users',
            'label' => 'LBL_USERS_1',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'campaign_name',
            'label' => 'LBL_CAMPAIGN',
          ),
          1 => 
          array (
            'name' => 'additional_support_cases_c',
            'label' => 'Additional_Support_Cases__c',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'email_client',
            'label' => 'LBL_EMAIL_CLIENT',
          ),
          1 => 
          array (
            'name' => 'sales_stage',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_SALES_STAGE',
            'customCode' => '
	    	<script src=\'custom/include/javascript/custom_javascript.js\'></script>
		{html_options id="sales_stage" name="sales_stage" options=$fields.sales_stage.options selected=$fields.sales_stage.value  onChange=\'checkOpportunitySalesStage()\'}
	    ',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'probability',
            'label' => 'LBL_PROBABILITY',
          ),
          1 => 
          array (
            'name' => 'connect_sell_c',
            'label' => 'LBL_CONNECT_SELL',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'Term_c',
            'label' => 'Term__c',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'lead_source',
            'label' => 'LBL_LEAD_SOURCE',
          ),
          1 => 
          array (
            'name' => 'Revenue_Type_c',
            'label' => 'Revenue_Type__c',
          ),
        ),
        10 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'renewal_date_c',
            'label' => 'Renewal_Date_c',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'current_solution',
            'label' => 'LBL_CURRENT_SOLUTION',
          ),
          1 => 
          array (
            'name' => 'order_number',
            'label' => 'LBL_ORDER_NUMBER',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'order_type_c',
            'label' => 'LBL_ORDER_TYPE_C',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'competitor_1',
            'label' => 'LBL_COMPETITOR_1',
          ),
          1 => 
          array (
            'name' => 'true_up_c',
            'label' => 'LBL_TRUE_UP',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'competitor_2',
            'label' => 'LBL_COMPETITOR_2',
          ),
          1 => 
          array (
            'name' => 'next_step',
            'label' => 'LBL_NEXT_STEP',
            'customCode' => '<textarea id="{$fields.next_step.name}" name="{$fields.next_step.name}" rows="4" cols="60" title=\'\' tabindex="1">{$fields.next_step.value}</textarea>',
          ),
        ),
        15 => 
        array (
          0 => 
          array (
            'name' => 'competitor_3',
            'label' => 'LBL_COMPETITOR_3',
          ),
          1 => 
          array (
            'name' => 'next_step_due_date',
            'label' => 'LBL_NEXT_STEP_DUE_DATE',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'competitor_expiration_c',
            'label' => 'LBL_COMPETITOR_EXPIRATION',
          ),
        ),
        17 => 
        array (
          0 => 
          array (
            'name' => 'demo_c',
            'label' => 'Demo_1',
          ),
          1 => 
          array (
            'name' => 'top20deal_c',
            'label' => 'LBL_TOP20DEAL',
          ),
        ),
        18 => 
        array (
          0 => 
          array (
            'name' => 'demo_date_c',
            'label' => 'Demo Date',
          ),
        ),
        19 => 
        array (
          0 => 
          array (
            'name' => 'evaluation',
            'label' => 'LBL_EVALUATION',
          ),
          1 => 
          array (
            'name' => 'closed_lost_reason_c',
            'label' => 'LBL_CLOSED_LOST_REASON_C',
            'customCode' => '
<script src=\'custom/include/javascript/custom_javascript.js\'></script>
	{html_options id="closed_lost_reason_c" name="closed_lost_reason_c" options=$fields.closed_lost_reason_c.options selected=$fields.closed_lost_reason_c.value  onChange=\'checkOppClosedReasonDependentDropdown("closed_lost_reason_detail_c", true)\' }
',
          ),
        ),
        20 => 
        array (
          0 => 
          array (
            'name' => 'evaluation_start_date',
            'label' => 'LBL_EVALUATION_START_DATE',
          ),
          1 => 
          array (
            'name' => 'closed_lost_reason_detail_c',
            'label' => 'LBL_CLOSED_LOST_REASON_DETAIL',
          ),
        ),
        21 => 
        array (
          0 => 
          array (
            'name' => 'Evaluation_Close_Date_c',
            'label' => 'Evaluation_Close_Date__c',
          ),
          1 => 
          array (
            'name' => 'primary_reason_competitor_c',
            'label' => 'LBL_PRIMARY_REASON_COMPETITOR',
          ),
        ),
        22 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'closed_lost_description',
            'label' => 'LBL_CLOSED_LOST_DESCRIPTION',
            'customCode' => '
<textarea id="{$fields.closed_lost_description.name}"  cols="60" rows="4" name="{$fields.closed_lost_description.name}">{$fields.closed_lost_description.value}</textarea>
<script>
detail2val = \'{$fields.closed_lost_reason_detail_c.value}\';
checkOppClosedReasonDependentDropdown("{$fields.closed_lost_reason_detail_c.name}", false,detail2val);//call initial drop down rendering
</script>
        ',
          ),
        ),
        23 => 
        array (
          0 => 
          array (
            'name' => 'partner_assigned_to_c',
            'label' => 'Partner_Assigned_To_c',
          ),
          1 => 
          array (
            'name' => 'accepted_by_partner_c',
            'label' => 'LBL_ACCEPTED_BY_PARTNER',
          ),
        ),
        24 => 
        array (
          0 => 
          array (
            'name' => 'conflict_c',
            'label' => 'LBL_CONFLICT',
          ),
          1 => 
          array (
            'name' => 'partner_contact_c',
            'label' => 'LBL_PARTNER_CONTACT',
          ),
        ),
        25 => 
        array (
          0 => 
          array (
            'name' => 'conflict_type_c',
            'studio' => 'visible',
            'label' => 'LBL_CONFLICT_TYPE',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
        ),
        26 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'associated_rep_c',
            'label' => 'Associated_Rep_c',
          ),
        ),
        27 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        28 => 
        array (
          0 => 
          array (
            'name' => 'orders_opportunities_name',
          ),
        ),
        29 => 
        array (
          0 => 
          array (
            'name' => 'discountcodes_opportunities_name',
          ),
        ),
      ),
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'discount_amount_c',
            'label' => 'LBL_DISCOUNT_AMOUNT',
          ),
          1 => 
          array (
            'name' => 'discount_percent_c',
            'label' => 'LBL_DISCOUNT_PERCENT',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'discount_valid_from_c',
            'label' => 'LBL_DISCOUNT_VALID_FROM',
            'customCode' => '{capture name=idname assign=idname}discount_valid_from_c{/capture}
                            {if !empty($displayParams.idName)}
                                {assign var=idname value=$displayParams.idName}
                            {/if}

                            {assign var=date_value value=$fields.valid_from_c.value}
                            <input autocomplete="off" type="text" name="{$idname}" id="{$idname}" value="{$date_value}" title=\'{$vardef.help}\' {$displayParams.field} tabindex={$tabindex} size="11" maxlength="10">
                            {if !$displayParams.hiddeCalendar}
                            <img border="0" src="{sugar_getimagepath file=\'jscalendar.gif\'}" alt="{$APP.LBL_ENTER_DATE}" id="{$idname}_trigger" align="absmiddle" />
                            {/if}
                            {if $displayParams.showFormats}
                            &nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
                            {/if}
                            {if !$displayParams.hiddeCalendar}
                            <script type="text/javascript">
                            Calendar.setup ({ldelim}
                            inputField : "{$idname}",
                            daFormat : "{$CALENDAR_FORMAT}",
                            button : "{$idname}_trigger",
                            singleClick : true,
                            dateStr : "{$date_value}",
                            step : 1,
                            weekNumbers:false
                            {rdelim}
                            );
                            </script>
                            {/if}

                            To

                            {capture name=idname assign=idname}discount_valid_to_c{/capture}
                            {if !empty($displayParams.idName)}
                                {assign var=idname value=$displayParams.idName}
                            {/if}

                            {assign var=date_value value=$fields.valid_to_c.value}
                            <input autocomplete="off" type="text" name="{$idname}" id="{$idname}" value="{$date_value}" title=\'{$vardef.help}\' {$displayParams.field} tabindex={$tabindex} size="11" maxlength="10">
                            {if !$displayParams.hiddeCalendar}
                            <img border="0" src="{sugar_getimagepath file=\'jscalendar.gif\'}" alt="{$APP.LBL_ENTER_DATE}" id="{$idname}_trigger" align="absmiddle" />
                            {/if}
                            {if $displayParams.showFormats}
                            &nbsp;(<span class="dateFormat">{$USER_DATEFORMAT}</span>)
                            {/if}
                            {if !$displayParams.hiddeCalendar}
                            <script type="text/javascript">
                            Calendar.setup ({ldelim}
                            inputField : "{$idname}",
                            daFormat : "{$CALENDAR_FORMAT}",
                            button : "{$idname}_trigger",
                            singleClick : true,
                            dateStr : "{$date_value}",
                            step : 1,
                            weekNumbers:false
                            {rdelim}
                            );
                            </script>
                            {/if}
                            ',
          ),
          1 => 
          array (
            'name' => 'discount_no_expiration_c',
            'label' => 'LBL_DISCOUNT_NO_EXPIRATION',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'discount_approval_status_c',
            'label' => 'LBL_DISCOUNT_APPROVAL_STATUS',
            'customCode' => '{$fields.discount_approval_status_c.value}',
          ),
        ),
      ),
      'lbl_editview_panel2' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'discount_when_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN',
          ),
        ),
        1 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_dollars_c',
            'label' => 'LBL_DISCOUNT_WHEN_DOLLARS',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_prodtemp_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN_PRODTEMP',
          ),
        ),
        3 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_prodcat_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN_PRODCAT',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'discount_to_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TO',
          ),
        ),
        5 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_to_product_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TO_PRODUCT',
          ),
        ),
        6 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_to_prodcat_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TO_PRODCAT',
          ),
        ),
      ),
    ),
  ),
);
?>
