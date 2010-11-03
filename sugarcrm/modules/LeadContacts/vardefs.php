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

$dictionary['LeadContact'] = array(
    'table' => 'leadcontacts',
    'audited'=>true, 
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
            'massupdate' => false,
            ),
        'score' => array(
			'name' => 'score',
			'vname' => 'LBL_SCORE',
			'type' => 'score',
			'dbType' => 'decimal',
			'len' => '12,2',
			),
        'last_interaction_date' => array(
			'name' => 'last_interaction_date',
			'vname' => 'LBL_LAST_INTERACTION_DATE',
			'type' => 'datetime',
            'massupdate' => false,
			),
        'status' => array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'enum',
			'len' => '100',
			'options' => 'leadcontact_status_dom',
			'audited' => true,
			'comment' => 'Status of the lead',
			'merge_filter' => 'enabled', 
			),
		'contact_id' => array (
			'name' => 'contact_id',
			'type' => 'id',
			'reportable'=>false,
			'vname'=>'LBL_CONTACT_ID',
			'comment' => 'If converted, Contact ID resulting from the conversion'    
			),
        'leadaccount_id' => array(
			'name' => 'leadaccount_id',
			'type' => 'id',
			'vname' => 'LBL_LEADACCOUNT_ID',
			),
        'leadaccount_name' => array(
			'name' => 'leadaccount_name',
			'rname' => 'name',
			'id_name' => 'leadaccount_id',
			'vname' => 'LBL_LEADACCOUNT_NAME',
			'join_name'=>'leadaccounts',
			'type' => 'relate',
			'link' => 'leadaccounts',
			'table' => 'leadaccounts',
			'isnull' => 'true',
			'module' => 'LeadAccounts',
			'dbType' => 'varchar',
			'len' => '255',
			'source' => 'non-db',
			'unified_search' => true,
            'importable' => 'required',
			),
        'leadaccount_status' => array(
			'name' => 'leadaccount_status',
			'vname' => 'LBL_LEADACCOUNT_STATUS',
			'type' => 'varchar',
			'len' => '255',
			'source' => 'non-db',
			'importable' => 'false',
			),
		'interactions' => array(
            'name' => 'interactions',
            'type' => 'link',
            'relationship' => 'leadcontact_interactions',
            'source'=>'non-db',
            'vname'=>'LBL_INTERACTIONS_SUBPANEL_TITLE',
            ),
        'tasks' => array (
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'leadcontact_tasks',
            'source'=>'non-db',
            'vname'=>'LBL_TASKS',
            ),  
        'notes' => array (
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'leadcontact_notes',
            'source'=>'non-db',
            'vname'=>'LBL_NOTES',
            ),  
        'meetings' => array (
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'leadcontact_meetings',
            'source'=>'non-db',
            'vname'=>'LBL_MEETINGS',
            ), 
        'calls' => array (
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'leadcontact_calls',
            'source'=>'non-db',
            'vname'=>'LBL_CALLS',
            ),   
        'emails' => array (
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'emails_leadcontacts_rel',
            'source'=>'non-db',
            'vname'=>'LBL_EMAILS',
            ),
'email_addresses_primary' 
=> array(
  'name' => 'email_addresses_primary',
  'type' => 'link',
  'relationship' => 'leadcontacts_email_addresses_primary',
  'source' => 'non-db',
  'vname' => 'LBL_EMAIL_ADDRESS_PRIMARY',
  'unified_search' => true,
  'duplicate_merge'=> 'disabled',
  ),                               
        'campaign_leads' => array (
  			'name' => 'campaign_leads',
    		'type' => 'link',
    		'relationship' => 'leadcontact_campaign',
    		'module'=>'Campaigns',
    		'bean_name'=>'Campaign',
    		'source'=>'non-db',
			'vname'=>'LBL_CAMPAIGNS',
            ),
        'campaignlog' => array (
  			'name' => 'campaignlog',
    		'type' => 'link',
    		'relationship' => 'leadcontact_campaign_log',
    		'module'=>'CampaignLog',
    		'bean_name'=>'CampaignLog',
    		'source'=>'non-db',
			'vname'=>'LBL_CAMPAIGN_LOG',
            ),
        'prospect_lists' => array (
            'name' => 'prospect_lists',
            'type' => 'link',
            'relationship' => 'prospect_list_lead_contacts',
            'module'=>'ProspectLists',
            'source'=>'non-db',
            'vname'=>'LBL_PROSPECT_LIST',
            ),

        'portal_name' => array (
			'name' => 'portal_name',
			'vname' => 'LBL_PORTAL_NAME',
			'type' => 'varchar',
			'len' => '255',
			'comment' => 'Portal user name when lead created via lead portal'
			), 

        'portal_app' => array (
			'name' => 'portal_app',
			'vname' => 'LBL_PORTAL_APP',
			'type' => 'varchar',
			'len' => '255',
			'comment' => 'Portal application that resulted in created of lead'
			),
        'leadaccounts' => array(
            'name' => 'leadaccounts',
            'type' => 'link',
            'relationship' => 'leadaccount_leadcontacts',
            'source'=>'non-db',
            'vname'=>'LBL_COMPANY_NAME',
            ),			
        'status' => array (
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'enum',
			'len' => '100',
			'options' => 'leadcontact_status_dom',
			'audited'=>true,
			'comment' => 'Status of the leadcontact',
			'merge_filter' => 'enabled', 
			),
		'touchpoint' =>  array (
            'name' => 'touchpoint',
            'vname' => 'LBL_TOUCHPOINTS',
            'type' => 'link',
            'relationship' => 'leadcontact_touchpoints',
            'source' => 'non-db',
            ),     
        'scrub_flag' => array (
            'name' => 'scrub_flag',
            'type' => 'bool',
            'massupdate' => false,
            'default' => '0',
            'comment' => 'Flag coming from the scrub screen',
            'source' => 'non-db',
            ),                                        
        ),
    'indices' => array (
        array(
            'name' =>'idx_leadcontacts_last_first', 
            'type'=>'index', 
            'fields'=>array('last_name','first_name','deleted')
            ),
        array(
            'name' =>'idx_leads_contacts_tem_del_conv', 
            'type'=>'index', 
            'fields'=>array('team_id','deleted','converted')
            ),
        array(
            'name' =>'idx_leadcontacts_assigned', 
            'type'=>'index', 
            'fields'=>array('assigned_user_id')
            ),
        array(
            'name' =>'idx_leadcontacts_contact', 
            'type'=>'index', 
            'fields'=>array('contact_id')
            ),
        array(
            'name' =>'idx_leadcontacts_leadaccount', 
            'type'=>'index', 
            'fields'=>array('leadaccount_id')
            ),
        ), 
    'relationships' => array (
        'leadcontact_calls' => array(
            'lhs_module'=> 'LeadContacts', 
            'lhs_table'=> 'leadcontacts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Calls', 
            'rhs_table'=> 'calls', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadContacts'
            ),	
        'leadcontact_meetings' => array(
            'lhs_module'=> 'LeadContacts', 
            'lhs_table'=> 'leadcontacts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Meetings', 
            'rhs_table'=> 'meetings', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadContacts'
            ),	
        'leadcontact_tasks' => array(
            'lhs_module'=> 'LeadContacts', 
            'lhs_table'=> 'leadcontacts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Tasks', 
            'rhs_table'=> 'tasks', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadContacts'
            ),	
        'leadcontact_notes' => array(
            'lhs_module'=> 'LeadContacts', 
            'lhs_table'=> 'leadcontacts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Notes', 
            'rhs_table'=> 'notes', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadContacts'
            ),
        'leadcontact_emails' => array(
            'lhs_module'=> 'LeadContacts', 
            'lhs_table'=> 'leadcontacts', 
            'lhs_key' => 'id',
            'rhs_module'=> 'Emails', 
            'rhs_table'=> 'emails', 
            'rhs_key' => 'parent_id',	
            'relationship_type'=>'one-to-many', 
            'relationship_role_column'=>'parent_type',
            'relationship_role_column_value'=>'LeadContacts'
            ),	
        'leadcontact_campaign_log' => array(
            'lhs_module'		=>	'LeadContacts', 
            'lhs_table'			=>	'leadcontacts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'CampaignLog', 
            'rhs_table'			=>	'campaign_log', 
            'rhs_key' 			=> 	'target_id',	
            'relationship_type'	=>'one-to-many'
            ),
        'leadcontact_campaign' => array(
            'lhs_module'		=>	'LeadContacts', 
            'lhs_table'			=>	'leadcontacts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'Campaigns', 
            'rhs_table'			=>	'campaigns', 
            'rhs_key' 			=> 	'id',	
            'relationship_type'	=>'one-to-many'
            ),
         'leadcontact_interactions' => array(
            'lhs_module'		=>	'LeadContacts', 
            'lhs_table'			=>	'leadcontacts', 
            'lhs_key' 			=> 	'id',
            'rhs_module'		=>	'Interactions', 
            'rhs_table'			=>	'interactions', 
            'rhs_key'          => 'parent_id',	
            'relationship_type'=> 'one-to-many', 
            'relationship_role_column' => 'parent_type',
            'relationship_role_column_value' => 'LeadContacts'
            ),
        ),
	//This enables optimistic locking for Saves From EditView
    'optimistic_locking'=>true,
    );
VardefManager::createVardef('LeadContacts','LeadContact', array('default', 'assignable',
// BEGIN SUGARCRM PRO ONLY
'team_security',
// END SUGARCRM PRO ONLY
'person'));
