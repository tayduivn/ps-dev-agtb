<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav!=sales ONLY
$dictionary['prospect_lists_prospects'] = array ( 

	'table' => 'prospect_lists_prospects',

	'fields' => array (
		array (
			'name' => 'id',
			'type' => 'varchar',
			'len' => '36',
		),
		array (
			'name' => 'prospect_list_id',
			'type' => 'varchar',
			'len' => '36',
		),
		array (
			'name' => 'related_id',
			'type' => 'varchar',
			'len' => '36',
		),
		array (
			'name' => 'related_type',
			'type' => 'varchar',
			'len' => '25',  //valid values are Prospect, Contact, Lead, User
		),
        array (
			'name' => 'date_modified',
			'type' => 'datetime'
		),
		array (
			'name' => 'deleted',
			'type' => 'bool',
			'len' => '1',
			'default' => '0'
		),
	),
	
	'indices' => array (
		array (
			'name' => 'prospect_lists_prospectspk',
			'type' => 'primary',
			'fields' => array ( 'id' )
		),
		array (
			'name' => 'idx_plp_pro_id',
			'type' => 'index',
			'fields' => array ('prospect_list_id')
		),
		array (
			'name' => 'idx_plp_rel_id',
			'type' => 'alternate_key',
			'fields' => array (	'related_id',
								'related_type',
								'prospect_list_id'
						)
		),
	),
	
 	'relationships' => array (
		'prospect_list_contacts' => array(	'lhs_module'=> 'ProspectLists', 
											'lhs_table'=> 'prospect_lists', 
											'lhs_key' => 'id',
											'rhs_module'=> 'Contacts', 
											'rhs_table'=> 'contacts', 
											'rhs_key' => 'id',
											'relationship_type'=>'many-to-many',
											'join_table'=> 'prospect_lists_prospects', 
											'join_key_lhs'=>'prospect_list_id', 
											'join_key_rhs'=>'related_id',
											'relationship_role_column'=>'related_type',
											'relationship_role_column_value'=>'Contacts'
									),

		'prospect_list_prospects' =>array(	'lhs_module'=> 'ProspectLists', 
											'lhs_table'=> 'prospect_lists', 
											'lhs_key' => 'id',
											'rhs_module'=> 'Prospects', 
											'rhs_table'=> 'prospects', 
											'rhs_key' => 'id',
											'relationship_type'=>'many-to-many',
											'join_table'=> 'prospect_lists_prospects', 
											'join_key_lhs'=>'prospect_list_id', 
											'join_key_rhs'=>'related_id',
											'relationship_role_column'=>'related_type',
											'relationship_role_column_value'=>'Prospects'
									),

		'prospect_list_leads' =>array(	'lhs_module'=> 'ProspectLists', 
										'lhs_table'=> 'prospect_lists', 
										'lhs_key' => 'id',
										'rhs_module'=> 'Leads', 
										'rhs_table'=> 'leads', 
										'rhs_key' => 'id',
										'relationship_type'=>'many-to-many',
										'join_table'=> 'prospect_lists_prospects', 
										'join_key_lhs'=>'prospect_list_id', 
										'join_key_rhs'=>'related_id',
										'relationship_role_column'=>'related_type',
										'relationship_role_column_value'=>'Leads',
								),

		'prospect_list_users' =>array(	'lhs_module'=> 'ProspectLists', 
										'lhs_table'=> 'prospect_lists', 
										'lhs_key' => 'id',
										'rhs_module'=> 'Users', 
										'rhs_table'=> 'users', 
										'rhs_key' => 'id',
										'relationship_type'=>'many-to-many',
										'join_table'=> 'prospect_lists_prospects', 
										'join_key_lhs'=>'prospect_list_id', 
										'join_key_rhs'=>'related_id',
										'relationship_role_column'=>'related_type',
										'relationship_role_column_value'=>'Users',
								),

		'prospect_list_accounts' =>array(	'lhs_module'=> 'ProspectLists', 
											'lhs_table'=> 'prospect_lists', 
											'lhs_key' => 'id',
											'rhs_module'=> 'Accounts', 
											'rhs_table'=> 'accounts', 
											'rhs_key' => 'id',
											'relationship_type'=>'many-to-many',
											'join_table'=> 'prospect_lists_prospects', 
											'join_key_lhs'=>'prospect_list_id', 
											'join_key_rhs'=>'related_id',
											'relationship_role_column'=>'related_type',
											'relationship_role_column_value'=>'Accounts',
								)
	)
	
)
?>
