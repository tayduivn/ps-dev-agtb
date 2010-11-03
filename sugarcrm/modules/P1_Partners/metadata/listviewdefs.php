<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$module_name = 'P1_Partners';
$OBJECT_NAME = 'P1_PARTNERS';
$listViewDefs[$module_name] = array(

'NAME' => array(
        'width'   => '4',
        'label'   => 'LBL_OPPORTUNITY_NAME',
        'default' => true,
	'customCode' => '<a href="index.php?module=Opportunities&action=DetailView&record={$ID}" target="_blank">{$NAME}</a>',
),      
'ACCOUNT_NAME' => array(
	'width'   => '10',
	'label'   => 'LBL_LIST_ACCOUNT_NAME',
	'id'      => 'ACCOUNT_ID',
	'module'  => 'Accounts',
	'link'    => true,
	'default' => true,
	'sortable'=> true,
	'ACLTag' => 'ACCOUNT',
	'contextMenu' => array('objectType' => 'sugarAccount',
		'metaData' => array('return_module' => 'Contacts',
		'return_action' => 'ListView',
		'module' => 'Accounts',
		'return_action' => 'ListView',
		'parent_id' => '{$ACCOUNT_ID}',
		'parent_name' => '{$ACCOUNT_NAME}',
		'account_id' => '{$ACCOUNT_ID}',
		'account_name' => '{$ACCOUNT_NAME}',
		),
	),
	'related_fields' => array('account_id'),
	'customCode' => '<a href="index.php?module=Accounts&action=DetailView&record={$ACCOUNT_ID}" target="_blank">{$ACCOUNT_NAME}</a>',
),

'PARTNER_ASSIGNED_TO_C' => array(
        'width'   => '4',
        'label'   => 'LBL_ACCOUNTS_OPPORTUNITIES_1_FROM_ACCOUNTS_TITLE',
        'link'    => true,
        'default' => true,
        'module'  => 'Accounts',
        'customCode' => '<a href="index.php?module=Accounts&action=DetailView&record={$PARTNER_ASSIGNED_TO_C}" target="_blank">{$PARTNER_ASSIGNED_TO_NAME}</a>',
),
	
'ACCEPTED_BY_PARTNER_C' => array(
        'width'   => '4',
        'label'   => 'LBL_INLINE_ACCEPTED_BY_PARTNER',
        'default' => true,
),	

'AMOUNT_USDOLLAR' => array(
	'width'   => '4',
	'label'   => 'LBL_LIST_AMOUNT',
	'align'   => 'right',
	'default' => true,
	'save_as_field_name' => 'amount',
	'currency_format' => true,
	'inline_editable' => true,
),

'OPPORTUNITY_TYPE' => array(
	'width' => '5',
	'label' => 'LBL_TYPE',
	'default' => true,
	'inline_editable' => true,
),

'USERS' => array(
	'width' => '5',
	'label' => 'LBL_INLINE_USERS',
	'default' => true,
	'align' => 'center',
	'inline_editable' => true,
),

'SALES_STAGE' => array(
	'width'   => '8',
	'label'   => 'LBL_SALES_STAGE',
	'default' => true,
	'inline_editable' => true,
),

'DATE_CLOSED' => array(
	'width' => '10', 
	'label' => 'LBL_LIST_DATE_CLOSED',
 	'default' => true,
 	'inline_editable' => true,
),

'NEXT_STEP_DUE_DATE' => array(
        'width' => '10',
        'label' => 'LBL_INLINE_NEXT_STEP_DUE_DATE',
        'default' => true,
        'inline_editable' => false,
),

'CAMPAIGN_NAME' => array(
        'width'   => '10',
        'label'   => 'LBL_CAMPAIGN',
        'id'      => 'CAMPAIGN_ID',
        'module'  => 'Campaigns',
        'link'    => true,
        'default' => true,
        'sortable'=> true,
        'ACLTag' => 'CAMPAIGN',
        'contextMenu' => array('objectType' => 'sugarObject',
                'metaData' => array('return_module' => 'Campaigns',
                'return_action' => 'ListView',
                'module' => 'Campaigns',
                'return_action' => 'ListView',
                'parent_id' => '{$CAMPAIGN_ID}',
                'parent_name' => '{$CAMPAIGN_NAME}',
                'campaign_id' => '{$CAMPAIGN_ID}',
                'campaign_name' => '{$CAMPAIGN_NAME}',
                ),
        ),
        'related_fields' => array('campaign_id'),
        'customCode' => '<a href="index.php?module=Campaigns&action=DetailView&record={$CAMPAIGN_ID}" target="_blank">{$CAMPAIGN_NAME}</a>',
),

'ACCOUNT_BILLING_CITY' => array(
        'width'   => '4',
        'label'   => 'LBL_ACCOUNT_BILLING_CITY',
        'default' => true,
),
'ACCOUNT_BILLING_STATE' => array(
        'width'   => '4',
        'label'   => 'LBL_ACCOUNT_BILLING_STATE',
        'default' => true,
),
'ACCOUNT_BILLING_COUNTRY' => array(
        'width'   => '4',
        'label'   => 'LBL_ACCOUNT_BILLING_COUNTRY',
        'default' => true,
),

'DATE_ENTERED' => array(
	'width' => '4',
	'label' => 'LBL_DATE_ENTERED',
	'default' => true,
),

'CLOSE_WIZARD_LINK' => array (
    	'width' => '2',
    	'label' => '',
    	'customCode' => '<a title="{$LBL_LNK_CLOSED_WON}" href="index.php?module=Opportunities&action=OpportunityWizard&record={$ID}&return_module=P1_Partners&return_action=index" target="_blank">{$LBL_TO_WIZARD_TITLE}</a>',
    	'default' => true,
    	'sortable' => false,
),
   
'DETAIL_VIEW_LINK' => array (
    	'width' => '2',
    	'label' => '',
    	'customCode' => '<a title="{$LBL_LNK_DETAIL_VIEW}" href="#" target="_blank" onMouseOver="javascript:lvg_nav(\'Opportunities\', \'{$ID}\', \'d\', 16, this)" onFocus="javascript:lvg_nav(\'Opportunities\', \'{$ID}\', \'d\', 16, this)"> <img border=0 src="themes/default/images/view_inline.gif">',
    	'default' => true,
    	'sortable' => false,
),
'EVAL_WIZARD_LINK' => array (
    	'width' => '2',
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

'ASSIGNED_USER_NAME' => array(
	'default' => false,
	'label' => 'LBL_ASSIGNED_USER_NAME',
	'width' => '15%',
),


);
?>
