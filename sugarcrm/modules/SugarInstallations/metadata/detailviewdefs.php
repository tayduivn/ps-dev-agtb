<?php
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

$viewdefs['SugarInstallations']['DetailView'] = array(
    'templateMeta' => array(
        'form' => array('buttons' => array()),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),
    'panels' => array (
        'default' => array (
            array(
                'users',
                array (
                  'name' => 'soap_client_ip',
                  'customCode' => '{if $fields.soap_client_ip.value != ""}<a href="http://ip-lookup.net/index.php?ip={$fields.soap_client_ip.value}" target="_blank">{$fields.soap_client_ip.value}</a> &nbsp; <i>(click for IPWHOIS)</i>{/if}',
                  'label' => 'LBL_SOAP_CLIENT_IP',
                )
            ),
            array(
                'registered_users',
                array (
                  'name' => 'ip_address',
                  'customCode' => '{if $fields.ip_address.value != ""}<a href="http://ip-lookup.net/index.php?ip={$fields.ip_address.value}" target="_blank">{$fields.ip_address.value}</a> &nbsp; <i>(click for IPWHOIS)</i>{/if}',
                  'label' => 'LBL_IP_ADDRESS',
                )
            ),
            array('admin_users', 'auth_level'),
            array('users_active_30_days', 'sugar_version'),
            array('latest_tracker_id', 'sugar_db_version'),
            array('', 'sugar_flavor'),
            array('license_users', ''),
            array('license_num_lic_oc', 'php_version'),
            array('license_key', 'db_type'),
            array('license_expire_date', 'db_version'),
            array('', 'server_software'),
            array('', 'os'),
            array('', 'os_version'),
            array('', 'distro_name'),
            array('', 'timezone'),
            array('', 'timezone_u'),
            array('update_count', ''),
            array('first_update', 'account_name'),
            array('last_update', 'application_key'),
            array('', 'status')
        )
    ),
);