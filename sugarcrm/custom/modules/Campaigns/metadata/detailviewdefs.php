<?php
// created: 2010-10-06 18:08:29
$viewdefs['Campaigns']['DetailView'] = array (
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
          'customCode' => '<input title="{$MOD.LBL_TEST_BUTTON_TITLE}" accessKey="{$MOD.LBL_TEST_BUTTON_KEY}" class="button" onclick="this.form.return_module.value=\'Campaigns\'; this.form.return_action.value=\'TrackDetailView\';this.form.action.value=\'Schedule\';this.form.mode.value=\'test\'" type="{$ADD_BUTTON_STATE}" name="button" value="{$MOD.LBL_TEST_BUTTON_LABEL}">',
        ),
        4 => 
        array (
          'customCode' => '<input title="{$MOD.LBL_QUEUE_BUTTON_TITLE}" accessKey="{$MOD.LBL_QUEUE_BUTTON_KEY}" class="button" onclick="this.form.return_module.value=\'Campaigns\'; this.form.return_action.value=\'TrackDetailView\';this.form.action.value=\'Schedule\'" type="{$ADD_BUTTON_STATE}" name="button" value="{$MOD.LBL_QUEUE_BUTTON_LABEL}">',
        ),
        5 => 
        array (
          'customCode' => '<input title="{$APP.LBL_MAILMERGE}" accessKey="{$APP.LBL_MAILMERGE_KEY}" class="button" onclick="this.form.return_module.value=\'Campaigns\'; this.form.return_action.value=\'TrackDetailView\';this.form.action.value=\'MailMerge\'" type="submit" name="button" value="{$APP.LBL_MAILMERGE}">',
        ),
        6 => 
        array (
          'customCode' => '<input title="{$MOD.LBL_MARK_AS_SENT}" class="button" onclick="this.form.return_module.value=\'Campaigns\'; this.form.return_action.value=\'TrackDetailView\';this.form.action.value=\'DetailView\';this.form.mode.value=\'set_target\'" type="{$TARGET_BUTTON_STATE}" name="button" value="{$MOD.LBL_MARK_AS_SENT}"><input title="mode" class="button" id="mode" name="mode" type="hidden" value="">',
        ),
      ),
      'links' => 
      array (
        0 => '<a class="listViewTdLinkS1" href="index.php?module=Campaigns&action=WizardHome&record={$fields.id.value}">{$MOD.LBL_TO_WIZARD_TITLE}</a>',
        1 => '<a class="listViewTdLinkS1" href="index.php?module=Campaigns&action=TrackDetailView&record={$fields.id.value}">{$MOD.LBL_TRACK_BUTTON_LABEL}</a>',
        2 => '<a class="listViewTdLinkS1" href="index.php?module=Campaigns&action=RoiDetailView&record={$fields.id.value}">{$MOD.LBL_TRACK_ROI_BUTTON_LABEL}</a>',
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
    'useTabs' => false,
  ),
  'panels' => 
  array (
    'lbl_campaign_information' => 
    array (
      0 => 
      array (
        0 => 
        array (
          'name' => 'name',
          'label' => 'LBL_CAMPAIGN_NAME',
        ),
        1 => 
        array (
          'name' => 'campaign_rating_c',
          'label' => 'campaign_rating_c',
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          'name' => 'status',
          'label' => 'LBL_CAMPAIGN_STATUS',
        ),
        1 => 
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_TO',
        ),
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'start_date',
          'label' => 'LBL_CAMPAIGN_START_DATE',
        ),
        1 => 
        array (
          'name' => 'team_name',
          'label' => 'LBL_TEAM',
        ),
      ),
      3 => 
      array (
        0 => 
        array (
          'name' => 'end_date',
          'label' => 'LBL_CAMPAIGN_END_DATE',
        ),
        1 => 
        array (
          'name' => 'date_modified',
          'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}&nbsp;',
          'label' => 'LBL_DATE_MODIFIED',
        ),
      ),
      4 => 
      array (
        0 => '',
        1 => 
        array (
          'name' => 'date_entered',
          'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
          'label' => 'LBL_DATE_ENTERED',
        ),
      ),
      5 => 
      array (
        0 => '',
        1 => '',
      ),
      6 => 
      array (
        0 => 
        array (
          'name' => 'campaign_type',
          'label' => 'LBL_CAMPAIGN_TYPE',
        ),
        1 => 
        array (
          'name' => 'product_offered_c',
          'label' => 'product_offered_c',
        ),
      ),
      7 => 
      array (
        0 => 
        array (
          'name' => 'media_c',
          'label' => 'media_c',
        ),
        1 => 
        array (
          'name' => 'offer_c',
          'label' => 'offer_c',
        ),
      ),
      8 => 
      array (
        0 => 
        array (
          'name' => 'contact_medium_c',
          'label' => 'contact_medium_c',
        ),
        1 => 
        array (
          'name' => 'offer_url_c',
          'label' => 'offer_url_c',
        ),
      ),
      9 => 
      array (
        0 => 
        array (
          'name' => 'target_audience_c',
          'label' => 'target_audience_c',
        ),
        1 => 
        array (
          'name' => 'message_c',
          'label' => 'message_c',
        ),
      ),
      10 => 
      array (
        0 => '',
        1 => '',
      ),
      11 => 
      array (
        0 => 
        array (
          'name' => 'budget',
          'label' => '{$MOD.LBL_CAMPAIGN_BUDGET} ({$CURRENCY})',
        ),
        1 => 
        array (
          'name' => 'actual_cost',
          'label' => '{$MOD.LBL_CAMPAIGN_ACTUAL_COST} ({$CURRENCY})',
        ),
      ),
      12 => 
      array (
        0 => 
        array (
          'name' => 'expected_revenue',
          'label' => '{$MOD.LBL_CAMPAIGN_EXPECTED_REVENUE} ({$CURRENCY})',
        ),
        1 => 
        array (
          'name' => 'expected_cost',
          'label' => '{$MOD.LBL_CAMPAIGN_EXPECTED_COST} ({$CURRENCY})',
        ),
      ),
      13 => 
      array (
        0 => 
        array (
          'name' => 'impressions',
          'label' => 'LBL_CAMPAIGN_IMPRESSIONS',
        ),
        1 => '',
      ),
      14 => 
      array (
        0 => '',
        1 => '',
      ),
      15 => 
      array (
        0 => 
        array (
          'name' => 'objective',
          'label' => 'LBL_CAMPAIGN_OBJECTIVE',
        ),
      ),
      16 => 
      array (
        0 => 
        array (
          'name' => 'content',
          'label' => 'LBL_CAMPAIGN_CONTENT',
        ),
      ),
      17 => 
      array (
        0 => '',
        1 => '',
      ),
      18 => 
      array (
        0 => 
        array (
          'name' => 'creative_c',
          'label' => 'creative_c',
        ),
        1 => 
        array (
          'name' => 'promo_url_c',
          'label' => 'promo_url_c',
        ),
      ),
      19 => 
      array (
        0 => '',
        1 => 
        array (
          'name' => 'reg_page_url_c',
          'label' => 'reg_page_url_c',
        ),
      ),
      20 => 
      array (
        0 => 
        array (
          'name' => 'plc_campaign_c',
          'label' => 'LBL_PLC_CAMPAIGN',
        ),
        1 => 
        array (
          'name' => 'display_in_leads_dropdown_c',
          'label' => 'Display_in_Leads_Dropdown__c',
        ),
      ),
    ),
  ),
);
?>
