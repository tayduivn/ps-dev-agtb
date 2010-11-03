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




$listViewDefs['SugarInstallations'] = array(
	'LICENSE_KEY' => array(
		'width' => '32',
		'label' => 'LBL_LIST_LICENSE_KEY',
		'link' => true,
        'default' => true), 
	'LAST_TOUCH' => array(
		'width' => '30',
		'label' => 'LBL_LIST_LAST_UPDATE',
		'default' => true,
        'link' => true),
    'INSTALLATION_AGE' => array(
		'width' => '10',
		'label' => 'LBL_LIST_INSTALLATION_AGE',
		'default' => true),
    'SUGAR_VERSION' => array(
		'width' => '10',
		'label' => 'LBL_LIST_SUGAR_VERSION',
		'default' => true),
    'SUGAR_FLAVOR' => array(
		'width' => '15',
		'label' => 'LBL_LIST_SUGAR_FLAVOR',
		'default' => true),
    'USERS' => array(
		'width' => '5',
		'label' => 'LBL_LIST_USERS',
		'default' => true),
    'LICENSE_USERS' => array(
		'width' => '5',
		'label' => 'LBL_LIST_LICENSE_USERS',
		'default' => true),
    'USERS_ACTIVE_30_DAYS' => array(
		'width' => '5',
		'label' => 'LBL_LIST_USERS_ACTIVE_30_DAYS_SHORT',
		'default' => true),
    'LATEST_TRACKER_ID' => array(
		'width' => '6',
		'label' => 'LBL_LIST_LATEST_TRACKER_ID',
		'default' => true),
    'STATUS' => array(
		'width' => '10',
		'label' => 'LBL_LIST_STATUS',
		'default' => true),
    'ACCOUNT_NAME'  => array(
		'width' => '25',
		'label' => 'LBL_LIST_ACCOUNT_NAME',
		'module' => 'Accounts',
		'id' => 'ACCOUNT_ID',
		'link' => true,
        'default' => true,
        'related_fields' => array('account_id')),
);
