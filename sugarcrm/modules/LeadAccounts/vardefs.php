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
$dictionary['LeadAccount'] = array(
    'table' => 'leadaccounts',
    'audited' => true, 
    'unified_search' => true, 
    /* SADEK CUSTOMIZATION - BEGIN NEW CODE FOR FULL TEXT SEARCH */ 
    'full_text_unified' => true, 
    /* SADEK CUSTOMIZATION - END NEW CODE FOR FULL TEXT SEARCH */ 
    'duplicate_merge'=>true, 
    'comment' => 'Leads are persons of interest early in a sales cycle', 
    'fields' => array (
        'converted' => array (
			'name' => 'converted',
			'vname' => 'LBL_CONVERTED',
			'type' => 'bool',
			'massupdate' => false,
			'default' => '0',
			'comment' => 'Has Lead been converted to a Contact (and other Sugar objects)'
			),
        'referred_by' => array (
			'name' => 'referred_by',
			'vname' => 'LBL_REFERED_BY',
			'type' => 'varchar',
			'len' => '100',
			'comment' => 'Identifies who refered the lead',
			'merge_filter' => 'enabled', 
			),  
        'campaign_id' => array (
            'name' => 'campaign_id',
            'type' => 'enum',
			'dbtype'=>'id',
            'reportable'=>true,
            'vname'=>'LBL_CAMPAIGN_ID',
            'comment' => 'Campaign that generated lead'    ,
			'options' => 'campaign_list',
			'massupdate' => false,
            ),
        'campaign_name' =>  array (
            'name' => 'campaign_name',
            'rname' => 'name',
            'id_name' => 'campaign_id',
            'vname' => 'LBL_CAMPAIGN',
            'type' => 'relate',
            'link' => 'campaign_leads',
            'table' => 'campaigns',
            'isnull' => 'true',
            'module' => 'Campaigns',
            'source' => 'non-db',
            ),
        'lead_source' => array (
			'name' => 'lead_source',
			'vname' => 'LBL_LEAD_SOURCE',
			'type' => 'enum',
			'options'=> 'lead_source_dom',
			'len' => '100',
			'audited'=>true,
			'comment' => 'Lead source (ex: Web, print)',
			'merge_filter' => 'enabled', 
            'massupdate' => false,
			),
        'lead_source_description' => array (
			'name' => 'lead_source_description',
			'vname' => 'LBL_LEAD_SOURCE_DESCRIPTION',
			'type' => 'text',
			'comment' => 'Description of the lead source'
			),
        'status' => array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'enum',
			'len' => '100',
			'options' => 'lead_status_dom',
			'audited'=>true,
			'comment' => 'Status of the lead',
			'merge_filter' => 'enabled', 
			),
        'account_id' => array (
			'name' => 'account_id',
			'type' => 'id',
			'reportable'=>false,
			'vname'=>'LBL_ACCOUNT_ID',
			'comment' => 'If converted, Account ID resulting from the conversion'    
			),
        'account_name' => array (
			'name' => 'account_name',
			'vname' => 'LBL_ACCOUNT_NAME',
			'type' => 'varchar',
			'source' => 'non-db',
			'reportable'=>false,
			'massupdate' => false,
			'len' => 255,
			),
        'opportunity_id' => array (
			'name' => 'opportunity_id',
			'type' => 'id',
			'reportable'=>false,
			'vname'=>'LBL_OPPORTUNITY_ID',
			'comment' => 'If converted, Opportunity ID resulting from the conversion'    
			),
		// SADEK BEGIN SUGARINTERNAL CUSTOMIZATION - ADDING RELATIONSHIP FOR OPPORTUNITIES FROM LEAD ACCOUNTS
		'opportunities' => array (
			'name' => 'opportunities',
			'type' => 'link',
			'relationship' => 'la_opportunity_leadaccounts',
			'source'=>'non-db',
			'vname'=>'LBL_OPPORTUNITIES',
		),
		'accounts' => array (
			'name' => 'accounts',
			'type' => 'link',
			'relationship' => 'leadaccounts_accounts',
			'source'=>'non-db',
			'vname'=>'LBL_ACCOUNTS',
		),
		// SADEK END SUGARINTERNAL CUSTOMIZATION - ADDING RELATIONSHIP FOR OPPORTUNITIES FROM LEAD ACCOUNTS
        'last_interaction_date' => array(
			'name' => 'last_interaction_date',
			'vname' => 'LBL_LAST_INTERACTION_DATE',
			'type' => 'datetime',
            'massupdate' => false,
			),
		'conversion_date' => array(
			'name' => 'conversion_date',
			'vname' => 'LBL_CONVERSION_DATE',
			'type' => 'datetime',
			'massupdate' => false,
			),
		'score' => array(
			'name' => 'score',
			'vname' => 'LBL_SCORE',
			'type' => 'score',
			'dbType' => 'decimal',
			'len' => '12,2',
			),
		'leadcontact_id' => array(
			'name' => 'leadcontact_id',
			'type' => 'varchar',
			'len' => '36',
			'source' => 'non-db',
            'vname' => 'LBL_LEADCONTACT_ID',
			),
		'leadcontact_name' => array (
			'name' => 'leadcontact_name',
			'vname' => 'LBL_CONTACT_NAME',
			'type' => 'varchar',
			'source'=>'non-db',
			'reportable'=>false,
			'massupdate' => false,
			'len' => 255,
          ),
         'leadcontact_title' => array(
			'name' => 'leadcontact_title',
			'vname' => 'LBL_LEADCONTACT_TITLE',
			'type' => 'varchar',
			'source'=>'non-db',
			'reportable'=>false,
			'massupdate' => false,
			'len' => '100',
			),
         'leadcontact_department' => array(
			'name' => 'leadcontact_department',
			'vname' => 'LBL_LEADCONTACT_DEPARTMENT',
			'type' => 'varchar',
			'source'=>'non-db',
			'reportable'=>false,
			'massupdate' => false,
			'len' => '255',
			),
         'parent_id' => array(
			'name' => 'parent_id',
			'vname' => 'LBL_PARENT_ACCOUNT_ID',
			'type' => 'id',
			'required'=>false,
			'reportable'=>false,
			'audited'=>true,
			'comment' => 'Lead Company ID of the parent of this account',
			),
         'parent_name' => array(
			'name' => 'parent_name',
			'rname' => 'name',
			'id_name' => 'parent_id',
			'vname' => 'LBL_MEMBER_OF',
			'type' => 'relate',
			'table' => 'leadaccount_leadaccounts',
			'isnull' => 'true',
			'module' => 'LeadAccounts',
			'massupdate' => false,
			'source'=>'non-db',
			'len' => 36,
			'link'=>'leadaccounts',
			'unified_search' => true,
			'importable' => 'false',
			),
         'employees' => array(
			'name' => 'employees',
			'vname' => 'LBL_EMPLOYEES',
			'type' => 'enum',
			'options'=> 'employee_qty_dom',
			'len' => '100',
			'massupdate' => false,
			),
         'interactions' => array(
            'name' => 'interactions',
            'type' => 'link',
            'relationship' => 'leadaccount_interactions',
            'source'=>'non-db',
            'vname'=>'LBL_TASKS',
            ),
         'tasks' => array (
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'leadaccount_tasks',
            'source'=>'non-db',
            'vname'=>'LBL_TASKS',
            ),  
         'notes' => array (
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'leadaccount_notes',
            'source'=>'non-db',
            'vname'=>'LBL_NOTES',
            ),  
         'meetings' => array (
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'leadaccount_meetings',
            'source'=>'non-db',
            'vname'=>'LBL_MEETINGS',
            ), 
         'calls' => array (
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'leadaccount_calls',
            'source'=>'non-db',
            'vname'=>'LBL_CALLS',
            ),   
         'emails' => array (
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'emails_leadaccounts_rel',
            'source'=>'non-db',
            'vname'=>'LBL_EMAILS',
            ),
        'campaign_leads' => array (
  			'name' => 'campaign_leads',
    		'type' => 'link',
    		'relationship' => 'leadaccount_campaign',
    		'module'=>'Campaigns',
    		'bean_name'=>'Campaign',
    		'source'=>'non-db',
			'vname'=>'LBL_CAMPAIGNS',
            ),
         'leadcontacts' => array(
            'name' => 'leadcontacts',
            'type' => 'link',
            'relationship' => 'leadaccount_leadcontacts',
            'source'=>'non-db',
            'vname'=>'LBL_LEADCONTACTS_SUBPANEL_TITLE',
            ),
         'leadaccounts' => array(
            'name' => 'leadaccounts',
            'type' => 'link',
            'relationship' => 'leadaccount_leadaccounts',
            'source'=>'non-db',
            'vname'=>'LBL_MEMBER_ORGANIZATIONS',
            ),
        'touchpoint' =>  array (
            'name' => 'touchpoint',
            'vname' => 'LBL_OR_TOUCHPOINT',
            'type' => 'link',
            'relationship' => 'leadaccount_touchpoints',
            'source' => 'non-db',
            ),                        
        ),
    'indices' => array (
       array(
           'name' =>'idx_lead_del_stat', 
           'type'=>'index', 
           'fields'=>array('name','status','deleted')
           ),
       array(
           'name' =>'idx_lead_stat_del', 
           'type'=>'index', 
           'fields'=>array('status','deleted','team_id','converted')
           ),
       array(
           'name' =>'idx_leads_tem_del_conv', 
           'type'=>'index', 
           'fields'=>array('team_id','deleted','converted')
           ),
       array(
           'name' =>'idx_lead_assigned', 
           'type'=>'index', 
           'fields'=>array('assigned_user_id')
           ),
       array(
           'name' =>'idx_leadaccount_parent', 
           'type'=>'index', 
           'fields'=>array('parent_id','deleted')
           ),
       ),
    'relationships' => array (
        'leadaccount_tasks' => array(
            'lhs_module'=> 'LeadAccounts', 
            'lhs_table'=> 'leadaccounts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Tasks', 
            'rhs_table'=> 'tasks', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadAccounts'
            ),	
        'leadaccount_notes' => array(
            'lhs_module'=> 'LeadAccounts', 
            'lhs_table'=> 'leadaccounts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Notes', 
            'rhs_table'=> 'notes', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadAccounts'
            ),
        'leadaccount_meetings' => array(
            'lhs_module'=> 'LeadAccounts', 
            'lhs_table'=> 'leadaccounts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Meetings', 
            'rhs_table'=> 'meetings', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadAccounts'
            ),
        'leadaccount_calls' => array(
            'lhs_module'=> 'LeadAccounts', 
            'lhs_table'=> 'leadaccounts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Calls', 
            'rhs_table'=> 'calls', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadAccounts'
            ),
        'leadaccount_emails' => array(
            'lhs_module'=> 'LeadAccounts', 
            'lhs_table'=> 'leadaccounts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Emails', 
            'rhs_table'=> 'emails', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadAccounts'
            ),
        'leadaccount_campaign_log' => array(
            'lhs_module'		=>	'LeadAccounts', 
            'lhs_table'			=>	'leadaccounts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'CampaignLog', 
            'rhs_table'			=>	'campaign_log', 
            'rhs_key' 			=> 	'target_id',	
            'relationship_type'	=>'one-to-many'
            ),
        'leadaccount_campaign' => array(
            'lhs_module'		=>	'LeadAccounts', 
            'lhs_table'			=>	'leadaccounts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'Campaigns', 
            'rhs_table'			=>	'campaigns', 
            'rhs_key' 			=> 	'id',	
            'relationship_type'	=>  'one-to-many'
            ),
        'leadaccount_leadcontacts' => array(
            'lhs_module'		=>	'LeadAccounts', 
            'lhs_table'			=>	'leadaccounts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'LeadContacts', 
            'rhs_table'			=>	'leadcontacts', 
            'rhs_key' 			=> 	'leadaccount_id',	
            'relationship_type'	=>  'one-to-many'
            ),
        'leadaccount_leadaccounts' => array(
            'lhs_module'		=>	'LeadAccounts', 
            'lhs_table'			=>	'leadaccounts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'LeadAccounts', 
            'rhs_table'			=>	'leadaccounts', 
            'rhs_key' 			=> 	'parent_id',	
            'relationship_type'	=>  'one-to-many'
            ),
        'leadaccount_interactions' => array(
            'lhs_module'		=>	'LeadAccounts', 
            'lhs_table'			=>	'leadaccounts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'Interactions', 
            'rhs_table'			=>	'interactions', 
            'rhs_key'          =>  'parent_id',	
            'relationship_type'=>  'one-to-many', 
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'LeadAccounts'
            ),
		// SADEK BEGIN SUGARINTERNAL CUSTOMIZATION - ADDING RELATIONSHIP FOR OPPORTUNITIES AND ACCOUNTS FROM LEAD ACCOUNTS
		'leadaccounts_accounts' => array(
				'lhs_module'=> 'LeadAccounts', 'lhs_table'=> 'leadaccounts', 'lhs_key' => 'account_id',
				'rhs_module'=> 'Accounts', 'rhs_table'=> 'accounts', 'rhs_key' => 'id',
				'relationship_type'=>'one-to-many'
			),
		'la_opportunity_leadaccounts' => array(
				'lhs_module'=> 'LeadAccounts', 'lhs_table'=> 'leadaccounts', 'lhs_key' => 'opportunity_id',
				'rhs_module'=> 'Opportunities', 'rhs_table'=> 'opportunities', 'rhs_key' => 'id',
				'relationship_type'=>'one-to-many'
			),
		// SADEK END SUGARINTERNAL CUSTOMIZATION - ADDING RELATIONSHIP FOR OPPORTUNITIES AND ACCOUNTS FROM LEAD ACCOUNTS
        ),
    //This enables optimistic locking for Saves From EditView
	'optimistic_locking' => true,
);
VardefManager::createVardef('LeadAccounts','LeadAccount', array('default', 'assignable',
// BEGIN SUGARCRM PRO ONLY
'team_security',
// END SUGARCRM PRO ONLY
'company'));

unset($dictionary['LeadAccount']['fields']['leadaccount_type']);
$dictionary['LeadAccount']['fields']['name']['importable'] = 'required';
$dictionary['LeadAccount']['fields']['annual_revenue']['massupdate'] = false;
$dictionary['LeadAccount']['fields']['industry']['massupdate'] = false;
