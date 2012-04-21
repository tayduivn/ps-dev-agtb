<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
$module_name = '<module_name>';
$OBJECT_NAME = '<_object_name>';
$viewdefs[$module_name]['mobile']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_ACCOUNT_NAME',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                    'width' => '40',
                ),
                array(
                    'name' => 'billing_address_city',
                    'label' => 'LBL_CITY',
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE',
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => $OBJECT_NAME . '_type',
                    'label' => 'LBL_TYPE',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'industry',
                    'label' => 'LBL_INDUSTRY',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'annual_revenue',
                    'label' => 'LBL_ANNUAL_REVENUE',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'phone_fax',
                    'label' => 'LBL_PHONE_FAX',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'billing_address_street',
                    'label' => 'LBL_BILLING_ADDRESS_STREET',
                    'enabled' => true,
                    'width' => '15',
                ),
                array(
                    'name' => 'billing_address_state',
                    'label' => 'LBL_BILLING_ADDRESS_STATE',
                    'enabled' => true,
                    'width' => '7',
                ),
                array(
                    'name' => 'billing_address_postalcode',
                    'label' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'billing_address_country',
                    'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'shipping_address_street',
                    'label' => 'LBL_SHIPPING_ADDRESS_STREET',
                    'enabled' => true,
                    'width' => '15',
                ),
                array(
                    'name' => 'shipping_address_city',
                    'label' => 'LBL_SHIPPING_ADDRESS_CITY',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'shipping_address_state',
                    'label' => 'LBL_SHIPPING_ADDRESS_STATE',
                    'enabled' => true,
                    'width' => '7',
                ),
                array(
                    'name' => 'shipping_address_postalcode',
                    'label' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'shipping_address_country',
                    'label' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'phone_alternate',
                    'label' => 'LBL_PHONE_ALTERNATE',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'website',
                    'label' => 'LBL_WEBSITE',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'ownership',
                    'label' => 'LBL_OWNERSHIP',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'employees',
                    'label' => 'LBL_EMPLOYEES',
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'ticker_symbol',
                    'label' => 'LBL_TICKER_SYMBOL',
                    'enabled' => true,
                    'width' => '10',
                ),
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    'name' => 'team_name',
                    'label' => 'LBL_TEAM',
                    'default' => true,
                    'enabled' => true,
                    'width' => '2',
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_USER_NAME',
                    'default' => true,
                    'enabled' => true,
                    'width' => '2',
                ),
            ),
        ),
    ),
);