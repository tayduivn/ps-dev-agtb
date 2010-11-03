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
$dictionary['Prospect'] = array(

	'table' => 'prospects',
	'fields' => array (
	 'tracker_key' => array (
		'name' => 'tracker_key',
		'vname' => 'LBL_TRACKER_KEY',
		'type' => 'int',
		'len' => '11',
		'required'=>true,
		'auto_increment' => true,
		'importable' => 'false',
		'studio' => array('editview' => false),
		),
	  'birthdate' =>
	  array (
	    'name' => 'birthdate',
	    'vname' => 'LBL_BIRTHDATE',
	    'massupdate' => false,
	    'type' => 'date',
	  ),
	  'do_not_call' =>
	  array (
	    'name' => 'do_not_call',
	    'vname' => 'LBL_DO_NOT_CALL',
	    'type'=>'bool',
	    'default' =>'0',
	  ),
	  'lead_id' =>
	  array (
		'name' => 'lead_id',
		'type' => 'id',
		'reportable'=>false,
		'vname'=>'LBL_LEAD_ID',
	  ),
	  'account_name' =>
	  array (
    	'name' => 'account_name',
    	'vname' => 'LBL_ACCOUNT_NAME',
    	'type' => 'varchar',
    	'len' => '150',
  	),
     'campaign_id' =>
      array (
            'name' => 'campaign_id',
        'comment' => 'Campaign that generated lead',
        'vname'=>'LBL_CAMPAIGN_ID',
        'rname' => 'id',
        'id_name' => 'campaign_id',
        'type' => 'id',
        'table' => 'campaigns',
        'isnull' => 'true',
        'module' => 'Campaigns',
        //'dbType' => 'char',
        'reportable'=>false,
        'massupdate' => false,
            'duplicate_merge'=> 'disabled',
      ),
	'email_addresses' =>
	array (
		'name' => 'email_addresses',
        'type' => 'link',
		'relationship' => 'prospects_email_addresses',
        'source' => 'non-db',
		'vname' => 'LBL_EMAIL_ADDRESSES',
		'reportable'=>false,
	),
	'email_addresses_primary' =>
	array (
		'name' => 'email_addresses_primary',
        'type' => 'link',
		'relationship' => 'prospects_email_addresses_primary',
        'source' => 'non-db',
		'vname' => 'LBL_EMAIL_ADDRESS_PRIMARY',
		'duplicate_merge'=> 'disabled',
	),
	  'campaigns' =>
	  array (
  		'name' => 'campaigns',
    	'type' => 'link',
    	'relationship' => 'prospect_campaign_log',
    	'module'=>'CampaignLog',
    	'bean_name'=>'CampaignLog',
    	'source'=>'non-db',
		'vname'=>'LBL_CAMPAIGNLOG',
	  ),
      'prospect_lists' =>
      array (
        'name' => 'prospect_lists',
        'type' => 'link',
        'relationship' => 'prospect_list_prospects',
        'module'=>'ProspectLists',
        'source'=>'non-db',
        'vname'=>'LBL_PROSPECT_LIST',
      ),

	),

	'indices' =>
			array (
				array(
						'name' => 'prospect_auto_tracker_key' ,
						'type'=>'index' ,
						'fields'=>array('tracker_key')
				),
       			array(	'name' 	=>	'idx_prospects_last_first',
						'type' 	=>	'index',
						'fields'=>	array(
										'last_name',
										'first_name',
										'deleted'
									)
				),
       			array(
						'name' =>	'idx_prospecs_del_last',
						'type' =>	'index',
						'fields'=>	array(
										'last_name',
										'deleted'
										)
				),
    		),

	'relationships' => array (

		'prospect_campaign_log' => array(
									'lhs_module'		=>	'Prospects',
									'lhs_table'			=>	'prospects',
									'lhs_key' 			=> 	'id',
						  			'rhs_module'		=>	'CampaignLog',
									'rhs_table'			=>	'campaign_log',
									'rhs_key' 			=> 	'target_id',
						  			'relationship_type'	=>'one-to-many'
						  		),

	)
);
VardefManager::createVardef('Prospects','Prospect', array('default', 'assignable',
//BEGIN SUGARCRM flav=pro ONLY
'team_security',
//END SUGARCRM flav=pro ONLY
'person'));

//BEGIN SUGARCRM flav!=com ONLY
if(isset($dictionary['Prospect']['fields']['picture'])) {
   unset($dictionary['Prospect']['fields']['picture']);
}
//END SUGARCRM flav!=com ONLY

?>