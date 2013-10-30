<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$viewdefs['Accounts']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'account_type' => array(),
        'industry' => array(),
        'annual_revenue' => array(),
        'address_street' => array(
            'dbFields' => array(
                'billing_address_street',
                'shipping_address_street',
            ),
            'vname' => 'LBL_STREET',
            'type' => 'text',
        ),
        'address_city' => array(
            'dbFields' => array(
                'billing_address_city',
                'shipping_address_city',
            ),
            'vname' => 'LBL_CITY',
            'type' => 'text',
        ),
        'address_state' => array(
            'dbFields' => array(
                'billing_address_state',
                'shipping_address_state',
            ),
            'vname' => 'LBL_STATE',
            'type' => 'text',
        ),
        'address_postalcode' => array(
            'dbFields' => array(
                'billing_address_postalcode',
                'shipping_address_postalcode',
            ),
            'vname' => 'LBL_POSTAL_CODE',
            'type' => 'text',
        ),
        'address_country' => array(
            'dbFields' => array(
                'billing_address_country',
                'shipping_address_country',
            ),
            'vname' => 'LBL_COUNTRY',
            'type' => 'text',
        ),
        'rating' => array(),
        'phone_office' => array(),
        'website' => array(),
        'ownership' => array(),
        'employees' => array(),
        'sic_code' => array(),
        'ticker_symbol' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        '$owner' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
        'assigned_user_name' => array(),
        '$favorite' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_FAVORITES_FILTER',
        ),
    ),
);
