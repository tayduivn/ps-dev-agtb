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

$dictionary['Touchpoint'] = array(
	'table' => 'touchpoints',
    'audited'=>true, 
    'unified_search' => 'true',
	'fields' => array(
		'id' => array(
			'name' => 'id',
			'type' => 'varchar',
			'len' => '36',
			'vname' => 'LBL_ID',
			),
		'first_name' =>
			array (
				'name' => 'first_name',
				'vname' => 'LBL_FIRST_NAME',
				'type' => 'varchar',
				'len' => '100',
				'comment' => 'Entered first name of the touchpoint',
                'unified_search' => true,
			),
		'last_name' =>
			array (
				'name' => 'last_name',
				'vname' => 'LBL_LAST_NAME',
				'type' => 'varchar',
				'len' => '100',
				'required' => true,
				'comment' => 'Entered last name of the touchpoint',
                'unified_search' => true,
			),
		'name' =>
			array (
				'name' => 'name',
				'rname' => 'name',
				'vname' => 'LBL_NAME',
				'type' => 'name',
				'fields' => array('first_name', 'last_name'),
				'sort_on' => 'last_name',
				'source' => 'non-db',
				'group'=>'last_name',
				'len' => '255',
				'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),
	            'importable' => 'false',
			),
		'full_name' =>
			array (
				'name' => 'full_name',
				'rname' => 'full_name',
				'vname' => 'LBL_NAME',
				'type' => 'name',
				'fields' => array('first_name', 'last_name'),
				'sort_on' => 'last_name',
				'source' => 'non-db',
				'group'=>'last_name',
				'len' => '510',
				'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),
			),
		'parent_lead_id' =>
			array (
				'name' => 'parent_lead_id',
				'rname' => 'parent_lead_id',
				'vname' => 'LBL_PARENT_LEAD_ID',
				'type' => 'id',
				'source' => 'non-db',
	            'importable' => 'false',
			),
		'parent_lead_action' =>
			array (
				'name' => 'parent_lead_action',
				'rname' => 'parent_lead_action',
				'vname' => 'LBL_PARENT_LEAD_ACTION',
				'type' => 'varchar',
				'source' => 'non-db',
				'len' => '255',
				'required' => true,
	            'importable' => 'false',
			),
		'purchasing_timeline' =>
		    array(
				'name' => 'purchasing_timeline',
				'vname' => 'LBL_PURCHASING_TIMELINE',
				'type' => 'enum',
				'options' => 'Purchasing Timeline',
				'comment' => 'Estimated purchasing timeline',
			),
		'company_name' =>
			array (
				'name' => 'company_name',
				'vname' => 'LBL_COMPANY_NAME',
				'type' => 'varchar',
				'len' => '100',
				'comment' => 'The name of the company',
                'unified_search' => true,
                'importable' => 'required',
			),												
		'title' =>
			array (
				'name' => 'title',
				'vname' => 'LBL_TITLE',
				'type' => 'varchar',
				'len' => '100',
				'comment' => 'The title of the contact'
			),			
		'raw_data' => array(
			'name' => 'raw_data',
			'type' => 'text',
            'vname' => 'LBL_RAW_DATA',
			),
		'source_type' => array(
			'name' => 'source_type',
			'type' => 'varchar',
			'len' => '255',
			'vname' => 'LBL_SOURCE_TYPE',
			),
		'score' => array(
			'name' => 'score',
			'vname' => 'LBL_SCORE',
			'type' => 'score',
			'dbType' => 'decimal',
			'len' => '12,2',
			'reportable' => true,
			),
		'scrub_result' => array(
			'name' => 'scrub_result',
			'vname' => 'LBL_SCRUB_RESULT',			
			'type' => 'enum',
			'len' => '255',
			'options'=>'scrub_result_dom',
            'audited'=>true,
			),		
		'scrub_relation_type' => array(
			'name' => 'scrub_relation_type',
			'vname' => 'LBL_SCRUB_RELATION_TYPE',			
			'type' => 'enum',
			'len' => '255',
			'options'=>'scrub_relation_type_dom',
			),		
			'scrubbed' =>
			  array (
			    'name' => 'scrubbed',
			    'vname' => 'LBL_SCRUBBED',
			    'type' => 'bool',
			    'default' => '0',
            'audited'=>true,
			  ),
		  'email1' => 
		  array (
		    'name' => 'email1',
		    'vname' => 'LBL_EMAIL_ADDRESS',
		    'type' => 'email',
		    'dbType' => 'varchar',
		    'len' => '100',
			'audited'=>true,
			'unified_search' => true, 
			'comment' => 'Main email address of lead',
		    'merge_filter' => 'enabled', 
		  ),
		  'portal_name' => 
		  array (
		    'name' => 'portal_name',
		    'vname' => 'LBL_PORTAL_NAME',
		    'type' => 'varchar',
		    'len' => '255',
		    'comment' => 'Portal user name when lead created via lead portal'
		  ),
		'discrepancies' => array(
			'name' => 'discrepancies',
			'type' => 'text',
            'vname' => 'LBL_DISCREPANCIES',
			),		  
        'campaign_id' => array (
            'name' => 'campaign_id',
            'type' => 'enum',
			'dbtype'=>'id',
            'reportable'=>true,
            'vname'=>'LBL_CAMPAIGN_NAME',
            'comment' => 'Campaign that generated lead'    ,
			'options' => 'campaign_list',
			'massupdate' => false,
            ),
        'campaign_name' =>
        array (
            'name' => 'campaign_name',
            'rname' => 'name',
            'id_name' => 'campaign_id',
            'vname' => 'LBL_CAMPAIGN_NAME',
            'type' => 'relate',
            'table' => 'campaigns',
            'join_name'=>'campaigns',
            'isnull' => 'true',
            'module' => 'Campaigns',
            'dbType' => 'varchar',
            'link'=>'campaigns',
            'len' => '255',
            'source'=>'non-db',
            ),
		// Sadek Pardot: added relationship to touchpoints for reporting purposes 
		'interactions' =>
		array (
			'name' => 'interactions',
			'type' => 'link',
			'relationship' => 'touchpoints_interactions',
			'source'=>'non-db',
			'link_type'=>'one',
			'module'=>'Interactions',
			'bean_name'=>'Interaction',
			'vname'=>'LBL_INTERACTIONS',
		),
        'campaigns' =>
        array (
            'name' => 'campaigns',
            'type' => 'link',
            'relationship' => 'campaigns_touchpoints',
            'source'=>'non-db',
            'link_type'=>'one',
            'module'=>'Campaigns',
            'bean_name'=>'Campaign',
            'vname'=>'LBL_CAMPAIGNS',
            ),
        'new_leadaccount_id' => array (
            'name' => 'new_leadaccount_id',
			'type'=>'id',
            'reportable'=>true,
            'vname'=>'LBL_NEWLEADACCOUNT_ID',
            'comment' => 'set if the touch point resulted in creation of new lead acount record.',
            'importable' => 'false',
            ),
        'new_leadaccount_name' =>  array (
            'name' => 'new_leadaccount_name',
            'vname' => 'LBL_LEADACCOUNT_NAME',
            'type' => 'link',
            'relationship' => 'leadaccount_touchpoints',
            'source' => 'non-db',
            'importable' => 'false',
            ),            
        'new_leadcontact_id' => array (
            'name' => 'new_leadcontact_id',
			'type'=>'id',
            'reportable'=>true,
            'vname'=>'LBL_NEWLEADCONTACT_ID',
            'comment' => 'If the touch point resulted in creation of new lead contact record.'    ,
            'importable' => 'false',
            ),     
        'new_leadcontact_name' =>  array (
            'name' => 'new_leadcontact_name',
            'vname' => 'LBL_LEADCONTACT_NAME',
            'type' => 'link',
            'relationship' => 'leadcontact_touchpoints',
            'source' => 'non-db',
            'importable' => 'false',
            ),
	    'lead_source' => 
	    array (
	      'name' => 'lead_source',
	      'vname' => 'LBL_LEAD_SOURCE',
	      'type' => 'enum',
	      'options' => 'lead_source_dom',
	      'len' => '100',
	      'audited' => true,
	      'comment' => 'Lead source (ex: Web, print)',
	      'merge_filter' => 'enabled',
            'massupdate' => false,
	    ),
	    // LEAD ACCOUNT FIELDS
	    
	    // from COMPANY OBJECT
	    'industry' => 
  array (
    'name' => 'industry',
    'vname' => 'LBL_INDUSTRY',
    'type' => 'enum',
    'options' => 'industry_dom',
    'len'=>50,
    'comment' => 'The company belongs in this industry',
    'merge_filter' => 'enabled',
            'massupdate' => false,
  ),
    'annual_revenue' => 
  array (
    'name' => 'annual_revenue',
    'vname' => 'LBL_ANNUAL_REVENUE',
    'type' => 'varchar',
    'len' => 25,
    'comment' => 'Annual revenue for this company',
    'merge_filter' => 'enabled',
            'massupdate' => false,
  ),
  'phone_fax' => 
  array (
    'name' => 'phone_fax',
    'vname' => 'LBL_FAX',
    'type' => 'phone',
    'dbType' => 'varchar',
    'len' => 25,
    'unified_search' => true,
    'comment' => 'The fax phone number of this company',
  ), 
   'rating' => 
  array (
    'name' => 'rating',
    'vname' => 'LBL_RATING',
    'type' => 'varchar',
    'len' => 25,
    'comment' => 'An arbitrary rating for this company for use in comparisons with others',
  ),
    'phone_office' => 
  array (
    'name' => 'phone_office',
    'vname' => 'LBL_PHONE_OFFICE',
    'type' => 'phone',
    'dbType' => 'varchar',
    'len' => 25,
    'audited'=>true,
    'unified_search' => true,  
    'comment' => 'The office phone number',
    'merge_filter' => 'enabled',
    'importable' => false,
  ),
    'phone_alternate' => 
  array (
    'name' => 'phone_alternate',
    'vname' => 'LBL_PHONE_ALT',
    'type' => 'phone',
    'group'=>'phone_office',
    'dbType' => 'varchar',
    'len' => 25,
    'unified_search' => true,
    'comment' => 'An alternate phone number',
    'merge_filter' => 'enabled',
  ),
   'website' => 
  array (
    'name' => 'website',
    'vname' => 'LBL_WEBSITE',
    'type' => 'varchar',
    'len' => 255,
    'comment' => 'URL of website for the company',
  ),
   'ownership' => 
  array (
    'name' => 'ownership',
    'vname' => 'LBL_OWNERSHIP',
    'type' => 'varchar',
    'len' => 100,
    'comment' => '',
  ),
   'employees' => 
  array (
    'name' => 'employees',
    'vname' => 'LBL_EMPLOYEES',
    'type' => 'enum',
    'options'=> 'employee_qty_dom',
    'len' => '100',  
            'massupdate' => false,
  ),
  'ticker_symbol' => 
  array (
    'name' => 'ticker_symbol',
    'vname' => 'LBL_TICKER_SYMBOL',
    'type' => 'varchar',
    'len' => 10,
    'comment' => 'The stock trading (ticker) symbol for the company',
    'merge_filter' => 'enabled',
  ),
  		// from LEADACCOUNT OBJECT
        'referred_by' => array (
			'name' => 'referred_by',
			'vname' => 'LBL_REFERED_BY',
			'type' => 'varchar',
			'len' => '100',
			'comment' => 'Identifies who refered the lead',
			'merge_filter' => 'enabled', 
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
	    'conversion_date' => array(
			'name' => 'conversion_date',
			'vname' => 'LBL_CONVERSION_DATE',
			'type' => 'datetime',
			'massupdate' => false,
            'massupdate' => false,
			),

		// LEAD CONTACT FIELDS
		// from PERSON OBJECT
        'salutation' =>
		array (
			'name' => 'salutation',
			'vname' => 'LBL_SALUTATION',
			'type' => 'enum',
			'options' => 'salutation_dom',
			'massupdate' => false,
			'len' => '25',
			'comment' => 'Contact salutation (e.g., Mr, Ms)'            
		),
'department' =>
		array (
			'name' => 'department',
			'vname' => 'LBL_DEPARTMENT',
			'type' => 'varchar',
			'len' => '255',
			'comment' => 'The department of the contact',
            'merge_filter' => 'enabled',
		),
		'do_not_call' =>
		array (
			'name' => 'do_not_call',
			'vname' => 'LBL_DO_NOT_CALL',
			'type' => 'bool',
			'default' => '0',
			'audited'=>true,
			'comment' => 'An indicator of whether contact can be called'
		),
	'phone_home' =>
		array (
			'name' => 'phone_home',
			'vname' => 'LBL_HOME_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => '25',
			'unified_search' => true, 
			'comment' => 'Home phone number of the contact',
            'merge_filter' => 'enabled',
		),
	'phone_mobile' =>
		array (
			'name' => 'phone_mobile',
			'vname' => 'LBL_MOBILE_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => '25',
			'unified_search' => true,
			'comment' => 'Mobile phone number of the contact',
            'merge_filter' => 'enabled',
		),
	'phone_work' =>
		array (
			'name' => 'phone_work',
			'vname' => 'LBL_OFFICE_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => '25',
			'audited'=>true,
			'unified_search' => true,
			'comment' => 'Work phone number of the contact',
            'merge_filter' => 'enabled',
		),
	'phone_other' =>
		array (
			'name' => 'phone_other',
			'vname' => 'LBL_OTHER_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => '25',
			'unified_search' => true,
			'comment' => 'Other phone number for the contact',
            'merge_filter' => 'enabled',
		),
	'phone_fax' =>
		array (
			'name' => 'phone_fax',
			'vname' => 'LBL_FAX_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => '25',
			'unified_search' => true,
			'comment' => 'Contact fax number',
            'merge_filter' => 'enabled',
		),
		
	'email_opt_out' =>
		array (
			'name'		=> 'email_opt_out',
			'vname'     => 'LBL_EMAIL_OPT_OUT',
			'type' => 'bool',
			'default' => '0',
			'audited'=>true,
			'comment' => 'email opt out'
		),		
	'primary_address_street' =>
		array (
			'name' => 'primary_address_street',
			'vname' => 'LBL_PRIMARY_ADDRESS_STREET',
			'type' => 'varchar',
			'len' => '150',
			'group'=>'primary_address',
			'comment' => 'Street address for primary address',
            'merge_filter' => 'enabled',
		),
	'primary_address_street_2' =>
		array (
			'name' => 'primary_address_street_2',
			'vname' => 'LBL_PRIMARY_ADDRESS_STREET_2',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),
	'primary_address_street_3' =>
		array (
			'name' => 'primary_address_street_3',
			'vname' => 'LBL_PRIMARY_ADDRESS_STREET_3',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),		
	'primary_address_city' =>
		array (
			'name' => 'primary_address_city',
			'vname' => 'LBL_PRIMARY_ADDRESS_CITY',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'primary_address',
			'comment' => 'City for primary address',
            'merge_filter' => 'enabled',
		),
	'primary_address_state' =>
		array (
			'name' => 'primary_address_state',
			'vname' => 'LBL_PRIMARY_ADDRESS_STATE',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'primary_address',
			'comment' => 'State for primary address',
            'merge_filter' => 'enabled',
		),
	'primary_address_postalcode' =>
		array (
			'name' => 'primary_address_postalcode',
			'vname' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
			'type' => 'varchar',
			'len' => '20',
			'group'=>'primary_address',
			'comment' => 'Postal code for primary address',
            'merge_filter' => 'enabled',
            
		),
	'primary_address_country' =>
		array (
			'name' => 'primary_address_country',
			'vname' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
			// SADEK - BEGIN IT REQUEST 5026 - BUG 17408 - BEGIN SUGARINTERNAL CUSTOMIZATION - DISPLAY COUNTRY FIELDS AS DROPDOWNS
			'type' => 'enum',
			'options' => 'countries_dom',
			// SADEK - END IT REQUEST 5026 - BUG 17408 - BEGIN SUGARINTERNAL CUSTOMIZATION - DISPLAY COUNTRY FIELDS AS DROPDOWNS
			'group'=>'primary_address',
			'comment' => 'Country for primary address',
            'merge_filter' => 'enabled',
		),
	'alt_address_street' =>
		array (
			'name' => 'alt_address_street',
			'vname' => 'LBL_ALT_ADDRESS_STREET',
			'type' => 'varchar',
			'len' => '150',
			'group'=>'alt_address',
			'comment' => 'Street address for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_street_2' =>
		array (
			'name' => 'alt_address_street_2',
			'vname' => 'LBL_ALT_ADDRESS_STREET_2',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),
	'alt_address_street_3' =>
		array (
			'name' => 'alt_address_street_3',
			'vname' => 'LBL_ALT_ADDRESS_STREET_3',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),			
	'alt_address_city' =>
		array (
			'name' => 'alt_address_city',
			'vname' => 'LBL_ALT_ADDRESS_CITY',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'alt_address',
			'comment' => 'City for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_state' =>
		array (
			'name' => 'alt_address_state',
			'vname' => 'LBL_ALT_ADDRESS_STATE',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'alt_address',
			'comment' => 'State for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_postalcode' =>
		array (
			'name' => 'alt_address_postalcode',
			'vname' => 'LBL_ALT_ADDRESS_POSTALCODE',
			'type' => 'varchar',
			'len' => '20',
			'group'=>'alt_address',
			'comment' => 'Postal code for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_country' =>
		array (
			'name' => 'alt_address_country',
			'vname' => 'LBL_ALT_ADDRESS_COUNTRY',
			// SADEK - BEGIN IT REQUEST 5026 - BUG 17408 - BEGIN SUGARINTERNAL CUSTOMIZATION - DISPLAY COUNTRY FIELDS AS DROPDOWNS
			'type' => 'enum',
			'options' => 'countries_dom',
			// SADEK - END IT REQUEST 5026 - BUG 17408 - BEGIN SUGARINTERNAL CUSTOMIZATION - DISPLAY COUNTRY FIELDS AS DROPDOWNS
			'group'=>'alt_address',
			'comment' => 'Country for alternate address',
            'merge_filter' => 'enabled',
		),
		'assistant' =>
		array (
			'name' => 'assistant',
			'vname' => 'LBL_ASSISTANT',
			'type' => 'varchar',
			'len' => '75',
			'unified_search' => true,
			'comment' => 'Name of the assistant of the contact',
            'merge_filter' => 'enabled',
		),
	'assistant_phone' =>
		array (
			'name' => 'assistant_phone',
			'vname' => 'LBL_ASSISTANT_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => '25',
			'group'=>'assistant',
			'unified_search' => true,
			'comment' => 'Phone number of the assistant of the contact',
            'merge_filter' => 'enabled',
		),
		
		
		
		// from LEADCONTACT OBJECT
        'portal_app' => array (
			'name' => 'portal_app',
			'vname' => 'LBL_PORTAL_APP',
			'type' => 'varchar',
			'len' => '255',
			'comment' => 'Portal application that resulted in created of lead'
			),
			
			

	      
       ),
        'indices' => array(
            array('name' =>'idx_touchpoint_deleted', 'type'=>'index', 'fields'=>array('deleted')),
            array('name' =>'idx_touchpoint_leadcontact', 'type'=>'index', 'fields'=>array('new_leadcontact_id','deleted')),
            array('name' =>'idx_touchpoint_leadaccount', 'type'=>'index', 'fields'=>array('new_leadaccount_id','deleted')),
        ),
    	'relationships' => array (
	        'leadaccount_touchpoints' => array(
	            'lhs_module'=> 'LeadAccounts', 
	            'lhs_table'=> 'leadaccounts', 
	            'lhs_key' => 'id',
	            'rhs_module'=> 'Touchpoints', 
	            'rhs_table'=> 'touchpoints', 
	            'rhs_key' => 'new_leadaccount_id',	
	            'relationship_type'=>'one-to-many', 
	            ),
	        'leadcontact_touchpoints' => array(
	            'lhs_module'=> 'LeadContacts', 
	            'lhs_table'=> 'leadcontacts', 
	            'lhs_key' => 'id',
	            'rhs_module'=> 'Touchpoints', 
	            'rhs_table'=> 'touchpoints', 
	            'rhs_key' => 'new_leadcontact_id',	
	            'relationship_type'=>'one-to-many', 
	            ),
	        'touchpoints_interactions' => array(
	            'lhs_module'=> 'Touchpoints', 
	            'lhs_table'=> 'touchpoints', 
	            'lhs_key' => 'id',
	            'rhs_module'=> 'Interactions', 
	            'rhs_table'=> 'interactions', 
	            'rhs_key' => 'source_id',	
	            'relationship_type'=>'one-to-many', 
	            ),
	        'campaigns_touchpoints' => array(
	            'lhs_module'=> 'Campaigns', 
	            'lhs_table'=> 'campaigns', 
	            'lhs_key' => 'id',
	            'rhs_module'=> 'Touchpoints', 
	            'rhs_table'=> 'touchpoints', 
	            'rhs_key' => 'campaign_id',	
	            'relationship_type'=>'one-to-many', 
	            ),
	     )	      				
	);
VardefManager::createVardef('Touchpoints','Touchpoint', array('basic', 'assignable', 'team_security'));
?>
