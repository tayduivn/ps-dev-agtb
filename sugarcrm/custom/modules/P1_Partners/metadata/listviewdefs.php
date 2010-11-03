<?php
$module_name = 'P1_Partners';
$OBJECT_NAME = 'P1_PARTNERS';
$listViewDefs [$module_name] = 
array (
  'NAME' => 
  array (
    'width' => '4%',
    'label' => 'LBL_OPPORTUNITY_NAME',
    'default' => true,
    'customCode' => '<a href="index.php?module=Opportunities&action=DetailView&record={$ID}" target="_blank">{$NAME}</a>',
  ),
  'ACCOUNT_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_ACCOUNT_NAME',
    'id' => 'ACCOUNT_ID',
    'module' => 'Accounts',
    'link' => true,
    'default' => true,
    'sortable' => true,
    'ACLTag' => 'ACCOUNT',
    'contextMenu' => 
    array (
      'objectType' => 'sugarAccount',
      'metaData' => 
      array (
        'return_module' => 'Contacts',
        'return_action' => 'ListView',
        'module' => 'Accounts',
        'parent_id' => '{$ACCOUNT_ID}',
        'parent_name' => '{$ACCOUNT_NAME}',
        'account_id' => '{$ACCOUNT_ID}',
        'account_name' => '{$ACCOUNT_NAME}',
      ),
    ),
    'related_fields' => 
    array (
      0 => 'account_id',
    ),
    'customCode' => '<a href="index.php?module=Accounts&action=DetailView&record={$ACCOUNT_ID}" target="_blank">{$ACCOUNT_NAME}</a>',
  ),
  'PARTNER_ASSIGNED_TO_C' => 
  array (
    'width' => '4%',
    'label' => 'LBL_ACCOUNTS_OPPORTUNITIES_1_FROM_ACCOUNTS_TITLE',
    'link' => true,
    'default' => true,
    'module' => 'Accounts',
    'customCode' => '<a href="index.php?module=Accounts&action=DetailView&record={$PARTNER_ASSIGNED_TO_C}" target="_blank">{$PARTNER_ASSIGNED_TO_NAME}</a>',
  ),
  'ACCEPTED_BY_PARTNER_C' => 
  array (
    'width' => '4%',
    'label' => 'LBL_INLINE_ACCEPTED_BY_PARTNER',
    'default' => true,
  ),
  'AMOUNT_USDOLLAR' => 
  array (
    'width' => '4%',
    'label' => 'LBL_LIST_AMOUNT',
    'align' => 'right',
    'default' => true,
    'save_as_field_name' => 'amount',
    'currency_format' => true,
    'inline_editable' => true,
  ),
  'OPPORTUNITY_TYPE' => 
  array (
    'width' => '5%',
    'label' => 'LBL_TYPE',
    'default' => true,
    'inline_editable' => true,
  ),
  'USERS' => 
  array (
    'width' => '5%',
    'label' => 'LBL_INLINE_USERS',
    'default' => true,
    'align' => 'center',
    'inline_editable' => true,
  ),
  'SALES_STAGE' => 
  array (
    'width' => '8%',
    'label' => 'LBL_SALES_STAGE',
    'default' => true,
    'inline_editable' => true,
  ),
  'DATE_CLOSED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_DATE_CLOSED',
    'default' => true,
    'inline_editable' => true,
  ),
  'NEXT_STEP_DUE_DATE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_INLINE_NEXT_STEP_DUE_DATE',
    'default' => true,
    'inline_editable' => false,
  ),
  'CAMPAIGN_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_CAMPAIGN',
    'id' => 'CAMPAIGN_ID',
    'module' => 'Campaigns',
    'link' => true,
    'default' => true,
    'sortable' => true,
    'ACLTag' => 'CAMPAIGN',
    'contextMenu' => 
    array (
      'objectType' => 'sugarObject',
      'metaData' => 
      array (
        'return_module' => 'Campaigns',
        'return_action' => 'ListView',
        'module' => 'Campaigns',
        'parent_id' => '{$CAMPAIGN_ID}',
        'parent_name' => '{$CAMPAIGN_NAME}',
        'campaign_id' => '{$CAMPAIGN_ID}',
        'campaign_name' => '{$CAMPAIGN_NAME}',
      ),
    ),
    'related_fields' => 
    array (
      0 => 'campaign_id',
    ),
    'customCode' => '<a href="index.php?module=Campaigns&action=DetailView&record={$CAMPAIGN_ID}" target="_blank">{$CAMPAIGN_NAME}</a>',
  ),
  'ACCOUNT_BILLING_CITY' => 
  array (
    'width' => '4%',
    'label' => 'LBL_ACCOUNT_BILLING_CITY',
    'default' => true,
  ),
  'ACCOUNT_BILLING_STATE' => 
  array (
    'width' => '4%',
    'label' => 'LBL_ACCOUNT_BILLING_STATE',
    'default' => true,
  ),
  'ACCOUNT_BILLING_COUNTRY' => 
  array (
    'width' => '4%',
    'label' => 'LBL_ACCOUNT_BILLING_COUNTRY',
    'default' => true,
  ),
  'DATE_ENTERED' => 
  array (
    'width' => '4%',
    'label' => 'LBL_DATE_ENTERED',
    'default' => true,
  ),
  'CLOSE_WIZARD_LINK' => 
  array (
    'width' => '2%',
    'label' => '',
    'customCode' => '<a title="{$LBL_LNK_CLOSED_WON}" href="index.php?module=Opportunities&action=OpportunityWizard&record={$ID}&return_module=P1_Partners&return_action=index" target="_blank">{$LBL_TO_WIZARD_TITLE}</a>',
    'default' => true,
    'sortable' => false,
  ),
  'DETAIL_VIEW_LINK' => 
  array (
    'width' => '2%',
    'label' => '',
    'customCode' => '<a title="{$LBL_LNK_DETAIL_VIEW}" href="#" target="_blank" onMouseOver="javascript:lvg_nav(\'Opportunities\', \'{$ID}\', \'d\', 16, this)" onFocus="javascript:lvg_nav(\'Opportunities\', \'{$ID}\', \'d\', 16, this)"> <img border=0 src="themes/default/images/view_inline.gif">',
    'default' => true,
    'sortable' => false,
  ),
  'EVAL_WIZARD_LINK' => 
  array (
    'width' => '2%',
    'label' => 'LBL_LNK_EVALWIZARD_VIEW',
    'customCode' => '<a title=\'Create New Eval Instance\' href="javascript: void(0);"   onclick="getformContentsEvalWiz(\'{$ID}\');YAHOO.example.container.panel3.show();">E</a> ',
    'default' => true,
    'sortable' => false,
  ),
  'SCORE_C' => 
  array (
    'default' => false,
    'label' => 'LBL_SCORE',
    'width' => '10%',
  ),
  'EVALUATION_CLOSE_DATE_C' => 
  array (
    'width' => '3%',
    'label' => 'Evaluation_Close_Date__c',
    'default' => false,
    'inline_editable' => false,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'default' => false,
    'label' => 'LBL_ASSIGNED_USER_NAME',
    'width' => '15%',
  ),
);
?>
