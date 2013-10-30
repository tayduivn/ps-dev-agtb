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
$viewdefs['Styleguide']['base']['view']['field'] = array(
    'template_values' => array(

        // email widget mock data
        'email' => array(
            array(
                'email_address' => 'kid.phone.sugar@example.info',
                'primary_address' => '0',
                'opt_out' => '0',
                'invalid_email' => '0',
            ),
            array(
                'email_address' => 'kid.phone.sugar@example.info',
                'primary_address' => '1',
                'opt_out' => '0',
                'invalid_email' => '0',
            ),
            array(
                'email_address' => 'kid.phone.sugar@example.info',
                'primary_address' => '0',
                'opt_out' => '1',
                'invalid_email' => '0',
            ),
            array(
                'email_address' => 'kid.phone.sugar@example.info',
                'primary_address' => '0',
                'opt_out' => '0',
                'invalid_email' => '1',
            ),
        ),

        // datetimecombo field mock data
        'datetimecombo' => '2013-05-06T22:47:00+00:00',

        // date field mock data
        'date' => '2013-05-06T22:47:00+00:00',

        // currency field mock data
        'currency' => array(
            'list_price' => 12345.7,
            'currency_id' => -99,
            'list_price_ERROR' => 'xyc',
        ),

        // date field mock data
        'bool' => array(
            'do_not_call' => 1,
            'do_not_call_ERROR' => 0,
        ),

        // date field mock data
        'text' => array(
            'description' => 'This is a description of the styleguide module.',
            'description_ERROR' => 'This description is too long.',
        ),
    ),
);
