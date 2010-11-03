<?php 
 //WARNING: The contents of this file are auto-generated

 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/bugs_e1_escalationsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/ps_timesheets_tasksMetaData.php');



/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/

$dictionary['bugs_users'] = array ( 'table' => 'bugs_users'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'bug_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'bugs_userspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_bug_usr_bug', 'type' =>'index', 'fields'=>array('bug_id'))
      , array('name' =>'idx_bug_usr_usr', 'type' =>'index', 'fields'=>array('user_id'))
      , array('name' => 'idx_bug_usr', 'type'=>'alternate_key', 'fields'=>array('bug_id','user_id'))            
      
                                                      )
 	  , 'relationships' => array ('bugs_users' => array('lhs_module'=> 'Bugs', 'lhs_table'=> 'bugs', 'lhs_key' => 'id',
							  'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'bugs_users', 'join_key_lhs'=>'bug_id', 'join_key_rhs'=>'user_id'))
                                                      
                                  );



 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/opportunities_accountsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/bugs_bugsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_opportunitiesMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_itrequestsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include_once('metadata/leads_leadsMetaData.php');



 
 //WARNING: The contents of this file are auto-generated
include_once('metadata/kbdocuments_views_ratingsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include_once('metadata/users_holidaysMetaData.php');
include_once('metadata/project_bugsMetaData.php');
include_once('metadata/project_casesMetaData.php');
include_once('metadata/project_productsMetaData.php');
include_once('metadata/projects_accountsMetaData.php');
include_once('metadata/projects_contactsMetaData.php');
include_once('metadata/projects_opportunitiesMetaData.php');
include_once('metadata/projects_quotesMetaData.php');



 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/e1_escalations_casesMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_activities_notesMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/spec_usecases_bugsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/contacts_ordersMetaData.php');



if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['tracker'] = array(
    'table' => 'tracker',
    'fields' => array(
        array(
            'name' => 'id',
            'type' => 'int',
            'len' => '11',
            'isnull' => 'false',
            'auto_increment' => true
        ) ,
        array(
            'name' => 'user_id',
            'type' => 'varchar',
            'len' => '36',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'module_name',
            'type' => 'varchar',
            'len' => '255',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'item_id',
            'type' => 'varchar',
            'len' => '36',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'item_summary',
            'type' => 'varchar',
            'len' => '255',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'date_modified',
            'type' => 'datetime',
            'isnull' => 'false',
        ) ,
        array(
            'name' => 'action',
            'type' => 'varchar',
            'len' => '255',
            'isnull' => 'false',
        ) ,
	array(
            'name' => 'session_id',
            'vname' => 'LBL_SESSION_ID',
            'type' => 'varchar',
            'len' => '36',
            'isnull' => 'true',
	),
        array(
            'name' => 'visible',
            'type' => 'bool',
            'len' => '1',
            'default' => '0'
        ) ,
    ) ,
    'indices' => array(
        array(
            'name' => 'trackerpk',
            'type' => 'primary',
            'fields' => array(
                'id'
            )
        ) ,
        array(
            'name' => 'idx_tracker_iid',
            'type' => 'index',
            'fields' => array(
                'item_id',
            ),
        ),
        array(
            // shortened name to comply with Oracle length restriction
            'name' => 'idx_tracker_userid_vis_id',
            'type' => 'index',
            'fields' => array(
                'user_id',
                'visible',
                'id',
            ),
        ),
        array(
        	// shortened name to comply with Oracle length restriction
            'name' => 'idx_tracker_userid_itemid_vis',
            'type' => 'index',
            'fields' => array(
                'user_id',
                'item_id',
                'visible'
            ),
        ),
    )
);


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['emails_leadaccounts'] = 
array (
	'table' => 'emails_leadaccounts',
	'fields' => array (
		array(
			'name'		=> 'id',
			'type'		=> 'varchar',
			'len'		=> '36'
		),
		array(
			'name'		=> 'email_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'leadaccount_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'date_modified',
			'type'		=>	'datetime'
		),
	       array(  'name'      => 'campaign_data',
       	              'type'      => 'text',
	       ),
		array(
			'name'		=> 'deleted',
			'type'		=> 'bool',
			'len'		=> '1',
			'default'	=> '0',
			'required'	=> true
		)
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		), 
		'leadaccounts' => array(
			'id'			=> 'leadaccount_id',
			'type'			=> 'many'
		),
	),
	'indices' => array(
		array(
			'name'		=> 'emails_leadaccountspk',
			'type'		=> 'primary',
			'fields'	=> array('id')
		),
		array(
			'name'		=> 'idx_leadaccount_email_email',
			'type'		=> 'index',
			'fields'	=> array('email_id')
		),
		array(
			'name'		=> 'idx_leadaccount_email_leadaccount',
			'type'		=> 'index',
			'fields'	=> array('leadaccount_id')
		),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_leadaccounts_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'LeadAccounts',
			'rhs_table'			=> 'leadaccounts',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_leadaccounts',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'leadaccount_id'
		)
	)
);
//<--



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['itrequests_itrequests'] = array (
	'table' => 'itrequests_itrequests',
	'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required' => true)
      , array('name' =>'itrequest_one', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'itrequest_two', 'type' =>'varchar', 'len'=>'36')
	),
	'indices' => array (
       array('name' =>'itrequest_itrequestpk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'id', 'type' =>'index', 'fields'=>array('id'))
      , array('name' =>'itrequest_one', 'type' =>'index', 'fields'=>array('itrequest_one'))
      , array('name' =>'itrequest_two', 'type' =>'index', 'fields'=>array('itrequest_two'))
	),

 	'relationships' => array (
		'itrequests_itrequests' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
		'rhs_module'=> 'ITRequests', 'rhs_table'=> 'itrequests', 'rhs_key' => 'id',
		'relationship_type'=>'many-to-many',
		'join_table'=> 'itrequests_itrequests', 'join_key_lhs'=>'itrequest_one', 'join_key_rhs'=>'itrequest_two','reverse'=>'1'))
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_project_task'] = array ('table' => 'emails_project_tasks',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',	
				'len'		=> '36',
		),
		array(	'name'		=> 'project_task_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
	    	array('name' =>'emails_project_taskpk', 'type' =>'primary', 'fields'=>array('id')),
			array('name' =>'idx_ept_email', 'type' =>'index', 'fields'=>array('email_id')),
			array('name' =>'idx_ept_project_task', 'type' =>'index', 'fields'=>array('project_task_id')),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_project_task_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'ProjectTask',
			'rhs_table'			=> 'project_task',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_project_tasks',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'project_task_id'
		)
	)
);




if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['itrequests_accounts'] = array ( 'table' => 'itrequests_accounts'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'itrequest_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'account_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'itrequests_accountspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_itrequest', 'type' =>'index', 'fields'=>array('itrequest_id'))
      , array('name' =>'idx_account', 'type' =>'index', 'fields'=>array('account_id'))
      , array('name' => 'idx_itrequest_account', 'type'=>'alternate_key', 'fields'=>array('itrequest_id','account_id'))            
      
                                                      )
 	  , 'relationships' => array ('itrequests_accounts' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
							  'rhs_module'=> 'Cases', 'rhs_table'=> 'accounts', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'itrequests_accounts', 'join_key_lhs'=>'itrequest_id', 'join_key_rhs'=>'account_id'))
                                                      
                                  );



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['emails_accounts'] = array ('table' => 'emails_accounts',
	'fields' => array (
		array(
			'name'		=> 'id',
			'type'		=> 'varchar',
			'len'		=> '36'
		),
		array(
			'name'		=> 'email_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'account_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'date_modified',
			'type'		=>	'datetime'
		),
		array(
			'name'		=> 'deleted',
			'type'		=> 'bool',
			'len'		=> '1',
			'default'	=> '0',
			'required'	=> true
		)
	),
	'indices' => array(
		array(
			'name'		=> 'emails_accountspk',
			'type'		=> 'primary',
			'fields'	=> array('id')
		),
		array(
			'name'		=> 'idx_acc_email_email',
			'type'		=> 'index',
			'fields'	=> array('email_id')
		),
		array(
			'name'		=> 'idx_acc_email_acc',
			'type'		=> 'index',
			'fields'	=> array('account_id')
		),
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		),
		'accounts' => array(
			'id'			=> 'account_id',
			'type'			=> 'many'
		),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_accounts_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Accounts',
			'rhs_table'			=> 'accounts',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_accounts',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'account_id'
		)
	)
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/





$dictionary['emails_quotes'] = array ('table' => 'emails_quotes',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',	
				'len'		=> '36',
		),
		array(	'name'		=> 'quote_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
	    	array('name' =>'emails_quotespk', 'type' =>'primary', 'fields'=>array('id')),
			array('name' =>'idx_quote_email_email', 'type' =>'index', 'fields'=>array('email_id')),
			array('name' =>'idx_quote_email_quote', 'type' =>'index', 'fields'=>array('quote_id')),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_quotes' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Quotes',
			'rhs_table'			=> 'quotes',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_quotes',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'quote_id'
		)
	)
);




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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['calls_leadcontacts'] = array ( 'table' => 'calls_leadcontacts'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'call_id', 'type' =>'varchar', 'len'=>'36', )
      , array('name' =>'leadcontacts_id', 'type' =>'varchar', 'len'=>'36', )
      , array('name' =>'required', 'type' =>'varchar', 'len'=>'1', 'default'=>'1')
      , array('name' =>'accept_status', 'type' =>'varchar', 'len'=>'25', 'default'=>'none')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )     
                                  , 'indices' => array (
       array('name' =>'calls_leadcontactsspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_lead_call_call', 'type' =>'index', 'fields'=>array('call_id'))
      , array('name' =>'idx_lead_call_leadcontacts', 'type' =>'index', 'fields'=>array('leadcontacts_id'))
      , array('name' => 'idx_call_leadcontacts', 'type'=>'alternate_key', 'fields'=>array('call_id','leadcontacts_id'))            
                                                      )

 	  , 'relationships' => array ('calls_leadcontacts' => array('lhs_module'=> 'Calls', 'lhs_table'=> 'calls', 'lhs_key' => 'id',
							  'rhs_module'=> 'LeadContacts', 'rhs_table'=> 'leadcontacts', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'calls_leadcontacts', 'join_key_lhs'=>'call_id', 'join_key_rhs'=>'leadcontacts_id'))

);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['team_memberships'] = array(
	'table' => 'team_memberships',
	'fields' => array(
		array(
			'name' => 'id',
			'type' => 'id',
			'required' => true
		),
		array(
			'name' => 'team_id', 
			'type' => 'id', 
			'required' => true
		),
		array(
			'name' => 'user_id',
			'type' => 'id',
			'required' => true
		),
		array(
			'name' => 'explicit_assign',
			'type' => 'bool',
			'len' => '1',
			'default' => 0,
			'required' => true
		),
		array(
			'name' => 'implicit_assign',
			'type' => 'bool', 
			'len' => '1', 
			'default' => '0',
			'required' => true
		),
		array(
			'name' => 'date_modified',
			'type' => 'datetime'
		),
		array(
			'name' => 'deleted', 
			'type' => 'bool', 
			'len'=> '1', 
			'default'=> 0, 
			'required' => true
		),
	),
	'indices' => array(
		array(
			'name' => 'team_membershipspk', 
			'type' => 'primary', 
			'fields' => array('id')
		),
		array(
			'name' => 'idx_team_membership', 
			'type' => 'index', 
			'fields' => array('user_id','team_id')
		),
		array(
			'name' => 'idx_teammemb_team_user', 
			'type' => 'alternate_key', 
			'fields' => array('team_id','user_id')
		)
	)
);


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['itrequests_cases'] = array ( 'table' => 'itrequests_cases'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'itrequest_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'case_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'itrequests_casespk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_cas_case_cas', 'type' =>'index', 'fields'=>array('itrequest_id'))
      , array('name' =>'idx_cas_case_case', 'type' =>'index', 'fields'=>array('case_id'))
      , array('name' => 'idx_itrequest_case', 'type'=>'alternate_key', 'fields'=>array('itrequest_id','case_id'))            
      
                                                      )
 	  , 'relationships' => array ('itrequests_cases' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
							  'rhs_module'=> 'Cases', 'rhs_table'=> 'cases', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'itrequests_cases', 'join_key_lhs'=>'itrequest_id', 'join_key_rhs'=>'case_id'))
                                                      
                                  );



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['itrequests_bugs'] = array ( 'table' => 'itrequests_bugs'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'itrequest_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'bug_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'itrequests_bugspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_cas_bug_cas', 'type' =>'index', 'fields'=>array('itrequest_id'))
      , array('name' =>'idx_cas_bug_bug', 'type' =>'index', 'fields'=>array('bug_id'))
      , array('name' => 'idx_itrequest_bug', 'type'=>'alternate_key', 'fields'=>array('itrequest_id','bug_id'))            
      
                                                      )
 	  , 'relationships' => array ('itrequests_bugs' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
							  'rhs_module'=> 'Bugs', 'rhs_table'=> 'bugs', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'itrequests_bugs', 'join_key_lhs'=>'itrequest_id', 'join_key_rhs'=>'bug_id'))
                                                      
                                  );



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_cases'] = array ('table' => 'emails_cases',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',	
				'len'		=> '36',
		),
		array(	'name'		=> 'case_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
	    	array('name' =>'emails_casespk', 'type' =>'primary', 'fields'=>array('id')),
			array('name' =>'idx_case_email_email', 'type' =>'index', 'fields'=>array('email_id')),
			array('name' =>'idx_case_email_case', 'type' =>'index', 'fields'=>array('case_id')),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_cases_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Cases',
			'rhs_table'			=> 'cases',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_cases',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'case_id'
		)
	)
);




if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['itrequests_users'] = array ( 'table' => 'itrequests_users'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'itrequest_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'itrequests_userspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_itr_usr_itr', 'type' =>'index', 'fields'=>array('itrequest_id'))
      , array('name' =>'idx_itr_usr_usr', 'type' =>'index', 'fields'=>array('user_id'))
      , array('name' => 'idx_itrequest_usr', 'type'=>'alternate_key', 'fields'=>array('itrequest_id','user_id'))            
      
                                                      )
 	  , 'relationships' => array ('itrequests_users' => array('lhs_module'=> 'ITRequests', 'lhs_table'=> 'itrequests', 'lhs_key' => 'id',
							  'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'itrequests_users', 'join_key_lhs'=>'itrequest_id', 'join_key_rhs'=>'user_id'))
                                                      
                                  );



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_bugs'] = array ('table' => 'emails_bugs',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',	
				'len'		=> '36',
		),
		array(	'name'		=> 'bug_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
	    	array('name' =>'emails_bugspk', 'type' =>'primary', 'fields'=>array('id')),
			array('name' =>'idx_bug_email_email', 'type' =>'index', 'fields'=>array('email_id')),
			array('name' =>'idx_bug_email_bug', 'type' =>'index', 'fields'=>array('bug_id')),
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		),
		'bugs' => array(
			'id'			=> 'bug_id',
			'type'			=> 'many'
		),
	),
	'role_field' 			=> '',
	'owner_module'			=> 'bugs',

/* added to support InboundEmail */
	'relationships' => array (
		'emails_bugs_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Bugs',
			'rhs_table'			=> 'bugs',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_bugs',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'bug_id'
		)
	)
);




if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$dictionary['subscriptions_distgroups'] = array (
	'table' => 'subscriptions_distgroups',
	'fields' => array (
		array('name' => 'id', 'type' =>'varchar', 'len'=>'36'),
		array('name' => 'subscription_id', 'type' =>'varchar', 'len'=>'36', ),
		array('name' => 'distgroup_id', 'type' =>'varchar', 'len'=>'36', ),
		array('name' => 'quantity', 'type' =>'int', 'len'=>'20', ),
		array('name' => 'date_modified','type' => 'datetime'),
		array('name' => 'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0','required'=>true),
	),
	'indices' => array (
		array('name' => 'subscriptions_distgroups_pk', 'type' =>'primary', 'fields'=>array('id')),
		array('name' => 'idx_sub_dist_sub', 'type' =>'index', 'fields'=>array('subscription_id')),
		array('name' => 'idx_sub_dist_dist', 'type' =>'index', 'fields'=>array('distgroup_id')),
		array('name' => 'idx_sub_dist', 'type'=>'alternate_key', 'fields'=>array('subscription_id','distgroup_id')),
	),
	'relationships' => array (
		'subscriptions_distgroups' => array(
			'lhs_module' => 'Subscriptions', 'lhs_table'=> 'subscriptions', 'lhs_key' => 'id',
			'rhs_module' => 'DistGroups', 'rhs_table'=> 'distgroups', 'rhs_key' => 'id',
			'relationship_type' => 'many-to-many',
			'join_table' => 'subscriptions_distgroups', 'join_key_lhs'=>'subscription_id', 'join_key_rhs'=>'distgroup_id'
		),
	),
);




if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_users'] = array('table' => 'emails_users',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'user_id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
	       array(  'name'      => 'campaign_data',
       	              'type'      => 'text',
	       ),

		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array(
		array(	'name'		=> 'emails_userspk',
				'type'		=> 'primary',
				'fields'	=> array('id')
		),
		array(	'name'		=> 'idx_usr_email_email',
				'type'		=> 'index',
				'fields'	=> array('email_id')
		),
		array(	'name'		=> 'idx_usr_email_usr',
				'type'		=> 'index',
				'fields'	=> array('user_id')
		),
	),
	'relationships' => array (
		'emails_users_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Users',
			'rhs_table'			=> 'users',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_users',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'user_id'
		),
	),
);



$dictionary['cases_kbdocuments'] =
	array ( 'table' => 'cases_kbdocuments',
		'fields' => array (
       		array('name' =>'id', 'type' =>'varchar', 'len'=>'36'),
       		array('name' =>'case_id', 'type' =>'varchar', 'len'=>'36'),
       		array('name' =>'kbdocument_id', 'type' =>'varchar', 'len'=>'36'),
       		array ('name' => 'date_modified','type' => 'datetime'),
       		array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
		),
		'indices' => array (
       		array('name' =>'cases_kbdocumentspk', 'type' =>'primary', 'fields'=>array('id')),
       		array('name' =>'idx_cas_doc_cas', 'type' =>'index', 'fields'=>array('case_id')),
       		array('name' =>'idx_cas_doc_doc', 'type' =>'index', 'fields'=>array('kbdocument_id')),
       		array('name' => 'idx_case_doc', 'type'=>'alternate_key', 'fields'=>array('case_id','kbdocument_id'))
		),
		'relationships' => array (
			'cases_kbdocuments' => array(
				'lhs_module'=> 'Cases',
				'lhs_table'=> 'cases',
				'lhs_key' => 'id',
				'rhs_module'=> 'KBDocuments',
				'rhs_table'=> 'kbdocuments',
				'rhs_key' => 'id',
				'relationship_type'=>'many-to-many',
				'join_table'=> 'cases_kbdocuments',
				'join_key_lhs'=>'case_id',
				'join_key_rhs'=>'kbdocument_id'
			),
		),
	);


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['emails_leadcontacts'] = 
array (
	'table' => 'emails_leadcontacts',
	'fields' => array (
		array(
			'name'		=> 'id',
			'type'		=> 'varchar',
			'len'		=> '36'
		),
		array(
			'name'		=> 'email_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'leadcontact_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'date_modified',
			'type'		=>	'datetime'
		),
	       array(  'name'      => 'campaign_data',
       	              'type'      => 'text',
	       ),
		array(
			'name'		=> 'deleted',
			'type'		=> 'bool',
			'len'		=> '1',
			'default'	=> '0',
			'required'	=> true
		)
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		), 
		'leadcontacts' => array(
			'id'			=> 'leadcontact_id',
			'type'			=> 'many'
		),
	),
	'indices' => array(
		array(
			'name'		=> 'emails_leadcontactspk',
			'type'		=> 'primary',
			'fields'	=> array('id')
		),
		array(
			'name'		=> 'idx_leadcontact_email_email',
			'type'		=> 'index',
			'fields'	=> array('email_id')
		),
		array(
			'name'		=> 'idx_leadcontact_email_leadcontact',
			'type'		=> 'index',
			'fields'	=> array('leadcontact_id')
		),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_leadcontacts_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'LeadContacts',
			'rhs_table'			=> 'leadcontacts',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_leadcontacts',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'leadcontact_id'
		)
	)
);



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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['meetings_leadcontacts'] = array ( 'table' => 'meetings_leadcontacts'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'meeting_id', 'type' =>'varchar', 'len'=>'36', )
      , array('name' =>'leadcontact_id', 'type' =>'varchar', 'len'=>'36', )
      , array('name' =>'required', 'type' =>'varchar', 'len'=>'1', 'default'=>'1')
      , array('name' =>'accept_status', 'type' =>'varchar', 'len'=>'25', 'default'=>'none')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )     
                                  , 'indices' => array (
       array('name' =>'meetings_leadcontactspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_lead_meeting_meeting', 'type' =>'index', 'fields'=>array('meeting_id'))
      , array('name' =>'idx_lead_meeting_leadcontact', 'type' =>'index', 'fields'=>array('leadcontact_id'))
      , array('name' => 'idx_meeting_leadcontact', 'type'=>'alternate_key', 'fields'=>array('meeting_id','leadcontact_id'))            
                                                      )

 	  , 'relationships' => array ('meetings_leadcontacts' => array('lhs_module'=> 'Meetings', 'lhs_table'=> 'meetings', 'lhs_key' => 'id',
							  'rhs_module'=> 'LeadContacts', 'rhs_table'=> 'leadcontacts', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'meetings_leadcontacts', 'join_key_lhs'=>'meeting_id', 'join_key_rhs'=>'leadcontact_id'))

);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['files'] = array(
	'table' => 'files',
	'fields' => array(
		array(
			'name' =>'id',
			'type' =>'varchar',
			'len'=>'36'
		),
		array(
			'name' =>'name',
			'type' =>'varchar',
			'len'=>'36',
		),
		array(
			'name' =>'content',
			'type' =>'blob'
		),
		array(
			'name' => 'date_modified',
			'type' => 'datetime',
			'len' => '',
		),
		array(
			'name' =>'deleted',
			'type' =>'bool',
			'len'=>'1',
			'default'=>'0',
			'required'=>true
		),
		array(
			'name' => 'date_entered',
			'type' => 'datetime',
			'len' => '',
			'required' => true
		),
		array(
			'name' =>'assigned_user_id',
			'type' =>'varchar',
			'len'=>'36',
		),
	),
	'indices' => array(
		array(
			'name' => 'filespk',
			'type' => 'primary',
			'fields' => array('id')
		),
		array(
			'name' => 'idx_cont_owner_id_and_name',
			'type' =>'index',
			'fields' => array(
				'assigned_user_id',
				'name',
				'deleted')
		),
	),
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['emails_contacts'] = array('table' => 'emails_contacts',
	'fields' => array(
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'contact_id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
	       array(  'name'      => 'campaign_data',
       	              'type'      => 'text',
	       ),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array(
		array(	'name'		=> 'emails_contactspk',
				'type'		=> 'primary',
				'fields'	=> array('id')
		),
		array(	'name'		=> 'idx_con_email_email',
				'type'		=> 'index',
				'fields'	=> array('email_id')
		),
		array(	'name'		=> 'idx_con_email_con',
				'type'		=> 'index',
				'fields'	=> array('contact_id')
		),
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		),
		'contacts' => array(
			'id'			=> 'contact_id',
			'type'			=> 'many'
		),
	),
	'role_field' 			=> '',
	'owner_module'			=> 'contacts',
	'relationships' => array (
		'emails_contacts_rel'	=> array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Contacts',
			'rhs_table'			=> 'contacts',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_contacts',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'contact_id'
		)
	) // end relationships metadata
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['emails_prospects'] = 
array (
	'table' => 'emails_prospects',
	'fields' => array (
		array(
			'name'		=> 'id',
			'type'		=> 'varchar',
			'len'		=> '36'
		),
		array(
			'name'		=> 'email_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'prospect_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'date_modified',
			'type'		=>	'datetime'
		),
	       array(  'name'      => 'campaign_data',
       	              'type'      => 'text',
	       ),
		array(
			'name'		=> 'deleted',
			'type'		=> 'bool',
			'len'		=> '1',
			'default'	=> '0',
			'required'	=> true
		)
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		), 
		'prospects' => array(
			'id'			=> 'prospect_id',
			'type'			=> 'many'
		),
	),
	'indices' => array(
		array(
			'name'		=> 'emails_prospectspk',
			'type'		=> 'primary',
			'fields'	=> array('id')
		),
		array(
			'name'		=> 'idx_prospect_email_email',
			'type'		=> 'index',
			'fields'	=> array('email_id')
		),
		array(
			'name'		=> 'idx_prospect_email_prospect',
			'type'		=> 'index',
			'fields'	=> array('prospect_id')
		),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_prospects_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Prospect',
			'rhs_table'			=> 'prospects',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_prospects',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'prospect_id'
		)
	)
);


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/accounts_ordersMetaData.php');



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_opportunities'] = array('table' => 'emails_opportunities',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'opportunity_id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
		array(	'name'		=> 'emails_opportunitiespk',
				'type'		=> 'primary',
				'fields'	=> array('id')
		),
		array(	'name'		=> 'idx_opp_email_email',
				'type'		=> 'index',
				'fields'	=> array('email_id')
		),
		array(	'name'		=> 'idx_opp_email_opp',
				'type'		=> 'index',
				'fields'	=> array('opportunity_id')
		),
	), // end indices metadata
/* added to support InboundEmail */
	'relationships' => array (
		'emails_opportunities_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Opportunities',
			'rhs_table'			=> 'opportunities',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_opportunities',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'opportunity_id'
		)
	)
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_tasks'] = array('table' => 'emails_tasks',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'task_id',
				'type'		=> 'varchar',
				'len'		=> '36'
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array(
		array(	'name'		=> 'emails_taskspk',
				'type'		=> 'primary',
				'fields'	=> array('id')
		),
		array(	'name'		=> 'idx_task_email_email',
				'type'		=> 'index',
				'fields'	=> array('email_id')
		),
		array(	'name'		=> 'idx_task_email_task',
				'type'		=> 'index',
				'fields'	=> array('task_id')
		),
	),
	'relationships' => array (
		'emails_tasks_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Tasks',
			'rhs_table'			=> 'tasks',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_tasks',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'task_id'
		),
	),
);



/*
 @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17275
** Description: Add database relationship between Cases and User
*/

$dictionary['cases_users'] = array ( 'table' => 'cases_users'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'case_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'cases_users', 'type' =>'primary', 'fields'=>array('id'))          
      
                                                      )
 	  , 'relationships' => array ('cases_users' => array('lhs_module'=> 'cases', 'lhs_table'=> 'cases', 'lhs_key' => 'id',
							  'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'cases_users', 'join_key_lhs'=>'case_id', 'join_key_rhs'=>'user_id'))
                                                      
                                  );




if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['emails_leads'] = 
array (
	'table' => 'emails_leads',
	'fields' => array (
		array(
			'name'		=> 'id',
			'type'		=> 'varchar',
			'len'		=> '36'
		),
		array(
			'name'		=> 'email_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'lead_id',
			'type'		=> 'varchar',
			'len'		=> '36',
		),
		array(
			'name'		=> 'date_modified',
			'type'		=>	'datetime'
		),
	       array(  'name'      => 'campaign_data',
       	              'type'      => 'text',
	       ),
		array(
			'name'		=> 'deleted',
			'type'		=> 'bool',
			'len'		=> '1',
			'default'	=> '0',
			'required'	=> true
		)
	),
	'related_tables' => array(
		'emails'			=> array(
			'id'			=> 'email_id',
			'type'			=> 'many'
		), 
		'leads' => array(
			'id'			=> 'lead_id',
			'type'			=> 'many'
		),
	),
	'indices' => array(
		array(
			'name'		=> 'emails_leadspk',
			'type'		=> 'primary',
			'fields'	=> array('id')
		),
		array(
			'name'		=> 'idx_lead_email_email',
			'type'		=> 'index',
			'fields'	=> array('email_id')
		),
		array(
			'name'		=> 'idx_lead_email_lead',
			'type'		=> 'index',
			'fields'	=> array('lead_id')
		),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_leads_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Leads',
			'rhs_table'			=> 'leads',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_leads',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'lead_id'
		)
	)
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$dictionary['opps_opps'] = array (
	'table' => 'opps_opps',
	'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required' => true)
      , array('name' =>'parent_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'child_id', 'type' =>'varchar', 'len'=>'36')
	),
	'indices' => array (
       array('name' =>'opp_opppk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_pp_parent', 'type' =>'index', 'fields'=>array('parent_id'))
      , array('name' =>'idx_pp_child', 'type' =>'index', 'fields'=>array('child_id'))
	),

 	'relationships' => array (
		'opps_opps' => array('lhs_module'=> 'Opportunities', 'lhs_table'=> 'opportunities', 'lhs_key' => 'id',
		'rhs_module'=> 'Opportunities', 'rhs_table'=> 'opportunities', 'rhs_key' => 'id',
		'relationship_type'=>'many-to-many',
		'join_table'=> 'opps_opps', 'join_key_lhs'=>'parent_id', 'join_key_rhs'=>'child_id'))
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['emails_projects'] = array ('table' => 'emails_projects',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',	
				'len'		=> '36',
		),
		array(	'name'		=> 'project_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
	    	array('name' =>'emails_projectspk', 'type' =>'primary', 'fields'=>array('id')),
			array('name' =>'idx_project_email_email', 'type' =>'index', 'fields'=>array('email_id')),
			array('name' =>'idx_project_email_project', 'type' =>'index', 'fields'=>array('project_id')),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_projects_rel' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Project',
			'rhs_table'			=> 'project',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_projects',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'project_id'
		)
	)
);




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

									/*
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
								*/
		//SUGARINTERNAL CUSTOMIZATION
		'prospect_list_lead_contacts' =>array(	'lhs_module'=> 'ProspectLists', 
										'lhs_table'=> 'prospect_lists', 
										'lhs_key' => 'id',
										'rhs_module'=> 'LeadContacts', 
										'rhs_table'=> 'leadcontacts', 
										'rhs_key' => 'id',
										'relationship_type'=>'many-to-many',
										'join_table'=> 'prospect_lists_prospects', 
										'join_key_lhs'=>'prospect_list_id', 
										'join_key_rhs'=>'related_id',
										'relationship_role_column'=>'related_type',
										'relationship_role_column_value'=>'LeadContacts',
								),
		//END SUGARINTERNAL CUSTOMIZATION
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
	
);


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/cr_customer_reference_accountsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/cr_customer_reference_contactsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_activities_callsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_activities_callsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_activities_meetingsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_activities_notesMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_activities_tasksMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/sales_seticket_activities_emailsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_opportunitiesMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_activities_emailsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_documentsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_productsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_subscriptionsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_activities_meetingsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_contractsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/producttemplates_contractsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/products_contractsMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/discountcodes_ordersMetaData.php');


 
 //WARNING: The contents of this file are auto-generated
include('custom/metadata/orders_activities_tasksMetaData.php');


?>