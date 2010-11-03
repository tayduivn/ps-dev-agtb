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
$dictionary['SugarUpdate'] = array(
	'table' => 'sugar_updates',
	'fields' => array (
		'id' => array (
			'name' => 'id',
			'vname' => 'LBL_NAME',
			'type' => 'id',
			'dbType' => 'int',
			'len' => '11',
			'auto_increment' => true,
			'required'=>true,
			'reportable'=>false,
		),
		'time_stamp' =>  array (
			'name' => 'time_stamp',
			'vname' => 'LBL_TIME_STAMP',
			'type' => 'datetime',
		),
		'beat_id' =>  array (
			'name' => 'beat_id',
			'vname' => 'LBL_BEAT_ID',
			'type' => 'varchar',
			'len' => '255',
		),
		'application_key' =>  array (
			'name' => 'application_key',
			'vname' => 'LBL_APPLICATION_KEY',
			'type' => 'varchar',
			'len' => '255',
		),
		'ip_address' =>  array (
			'name' => 'ip_address',
			'vname' => 'LBL_IP_ADDRESS',
			'type' => 'varchar',
			'len' => '30',
		),
		'sugar_version' =>  array (
			'name' => 'sugar_version',
			'vname' => 'LBL_SUGAR_VERSION',
			'type' => 'varchar',
			'len' => '25',
		),
		'sugar_db_version' =>  array (
			'name' => 'sugar_db_version',
			'vname' => 'LBL_SUGAR_DB_VERSION',
			'type' => 'varchar',
			'len' => '25',
		),
		'sugar_flavor' =>  array (
			'name' => 'sugar_flavor',
			'vname' => 'LBL_SUGAR_FLAVOR',
			'type' => 'enum',
			'options' => 'sugar_edition_dom',
			'len' => '10',
		),
        'name' => array(
            'name' => 'name',
            'type' => 'string',
            'source' => 'non-db',
            'len' => '255'
        ),
		'db_type' =>  array (
			'name' => 'db_type',
			'vname' => 'LBL_DB_TYPE',
			'type' => 'varchar',
			'len' => '255',
		),
		'db_version' =>  array (
			'name' => 'db_version',
			'vname' => 'LBL_DB_VERSION',
			'type' => 'varchar',
			'len' => '255',
		),
		'users' =>  array (
			'name' => 'users',
			'vname' => 'LBL_USERS',
			'type' => 'int',
			'len' => '11',
		),
		'admin_users' =>  array (
			'name' => 'admin_users',
			'vname' => 'LBL_ADMIN_USERS',
			'type' => 'int',
			'len' => '11',
		),
		'registered_users' =>  array (
			'name' => 'registered_users',
			'vname' => 'LBL_REGISTERED_USERS',
			'type' => 'int',
			'len' => '11',
		),
		'users_active_30_days' =>  array (
			'name' => 'users_active_30_days',
			'vname' => 'LBL_USERS_ACTIVE_30_DAYS',
			'type' => 'int',
			'len' => '11',
		),
		'latest_tracker_id' =>  array (
			'name' => 'latest_tracker_id',
			'vname' => 'LBL_LATEST_TRACKER_ID',
			'type' => 'int',
			'len' => '11',
		),
		'license_users' =>  array (
			'name' => 'license_users',
			'vname' => 'LBL_LICENSE_USERS',
			'type' => 'int',
			'len' => '11',
		),
		'license_expire_date' =>  array (
			'name' => 'license_expire_date',
			'vname' => 'LBL_LICENSE_EXPIRE_DATE',
			'type' => 'varchar',
			'len' => '255',
		),
		'license_key' =>  array (
			'name' => 'license_key',
			'vname' => 'LBL_LICENSE_KEY',
			'type' => 'varchar',
			'len' => '255',
		),
		'soap_client_ip' =>  array (
			'name' => 'soap_client_ip',
			'vname' => 'LBL_SOAP_CLIENT_IP',
			'type' => 'varchar',
			'len' => '30',
		),
		'php_version' =>  array (
			'name' => 'php_version',
			'vname' => 'LBL_PHP_VERSION',
			'type' => 'varchar',
			'len' => '30',
		),
		'license_num_lic_oc' =>  array (
			'name' => 'license_num_lic_oc',
			'vname' => 'LBL_LICENSE_NUM_LIC_OC',
			'type' => 'int',
			'len' => '11',
		),
                'server_software' =>  array (
                        'name' => 'server_software',
                        'vname' => 'LBL_SERVER_SOFTWARE',
                        'type' => 'varchar',
                        'len' => '255',
                ),
        'os' => array(
            'name' => 'os',
            'vname' => 'LBL_OS',
            'type' => 'varchar',
            'len' => '25',
            'reportable' => true,
        ),
        'os_version' => array(
            'name' => 'os_version',
            'vname' => 'LBL_OS_VERSION',
            'type' => 'varchar',
            'len' => '25',
            'reportable' => true,
        ),
        'distro_name' => array(
            'name' => 'distro_name',
            'vname' => 'LBL_DISTRO_NAME',
            'type' => 'varchar',
            'len' => '50',
            'reportable' => true,
        ),
        'timezone' => array(
            'name' => 'timezone',
            'vname' => 'LBL_TIMEZONE',
            'type' => 'varchar',
            'len' => '40',
            'reportable' => true,
        ),
        'timezone_u' => array(
            'name' => 'timezone_u',
            'vname' => 'LBL_TIMEZONE_U',
            'type' => 'varchar',
            'len' => '40',
            'reportable' => true,
        ),
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_CREATED_BY',
			'type' => 'bool',
			'required' => true,
			'reportable'=>false,
		),
		'installation_id' => array (
			'name' => 'installation_id',
			'type' => 'id',
			'dbType' => 'int',
			'len' => '11',
			'required' => true,
			'reportable'=> false,
            'vname' => 'LBL_INSTALLATION'
		),
		'sugar_installation' => array (
			'name' => 'sugar_installation',
			'type' => 'link',
			'relationship' => 'sugar_updates_sugar_installation',
			'link_type'=>'one',
			'side'=>'right',
			'source'=>'non-db',
			'vname'=>'LBL_SUGAR_INSTALLATION',
		),
	)
);
?>
