<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
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
 */



$listViewDefs['Subscriptions'] = array(
	'SUBSCRIPTION_ID' => array(
		'width' => '15%', 
		'label' => 'LBL_SUBSCRIPTION_ID',
        'default' => true,
		'link' => true,
	), 
	'ACCOUNT_NAME' => array(
		'width' => '34%', 
		'label' => 'LBL_LIST_ACCOUNT_NAME', 
		'module' => 'Accounts',
		'id' => 'ACCOUNT_ID',
		'link' => true,
        'contextMenu' => array('objectType' => 'sugarAccount', 
                               'metaData' => array(
									'return_module' => 'Subscriptions', 
									'return_action' => 'ListView', 
									'module' => 'Accounts',
									'return_action' => 'ListView', 
									'parent_id' => '{$ACCOUNT_ID}', 
									'parent_name' => '{$ACCOUNT_NAME}', 
									'account_id' => '{$ACCOUNT_ID}', 
									'account_name' => '{$ACCOUNT_NAME}'
								),
							),
        'sortable'=> false,
        'ACLTag' => 'ACCOUNT',
        'related_fields' => array('account_id'),
		'default' => true,
	),
    'EXPIRATION_DATE' => array(
        'width' => '10', 
        'label' => 'LBL_EXPIRATION_DATE',
        'default' => 'true',
	),
    'STATUS' => array(
        'width' => '10', 
        'label' => 'LBL_STATUS',
        'default' => 'true',
	),
    'AUDITED' => array(
        'width' => '5', 
        'label' => 'LBL_AUDITED',
        'default' => 'true',
	),
    'DEBUG' => array(
        'width' => '5', 
        'label' => 'LBL_DEBUG',
        'default' => 'true',
	),
    'PERPETUAL' => array(
        'width' => '5', 
        'label' => 'LBL_PERPETUAL',
        'default' => 'true',
	),
    'CREATED_BY_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_CREATED',
	),
    'TEAM_NAME' => array(
        'width' => '8', 
        'label' => 'LBL_LIST_TEAM',
	),
    'ASSIGNED_USER_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_LIST_ASSIGNED_USER',
	),
    'MODIFIED_USER_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_MODIFIED',
	),
);
?>
