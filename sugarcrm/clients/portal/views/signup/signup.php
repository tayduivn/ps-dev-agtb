<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

$viewdefs['portal']['view']['signup'] = array(
    'action' => 'list',
    'buttons' =>
    array(
        array(
            'name' => 'signup_button',
            'type' => 'button',
            'label' => 'LBL_SIGNUP_BUTTON_LABEL',
            'primary' => true,
        ),
        array(
            'name' => 'cancel_button',
            'type' => 'button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'pull-left',
        ),
    ),
    'panels' =>
    array(
        array(
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' =>
            array(
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                    'placeholder' => "LBL_PORTAL_SIGNUP_FIRST_NAME",
                    'required' => true,
                ),
                array(
                    'name' => 'last_name',
                    'type' => 'varchar',
                    'placeholder' => "LBL_PORTAL_SIGNUP_LAST_NAME",
                    'required' => true,
                ),
                array(
                    'name' => 'hr1',
                    'type' => 'hr',
                    'view' => 'default',
                ),
                array(
                    'name' => 'email',
                    'type' => 'email',
                    'placeholder' => "LBL_PORTAL_SIGNUP_EMAIL",
                    'required' => true,
                ),
                array(
                    'name' => 'phone_work',
                    'type' => 'phone',
                    'placeholder' => "LBL_PORTAL_SIGNUP_PHONE",
                ),
                array(
                    'name' => 'country',
                    'type' => 'enum',
                    'placeholder' => "LBL_PORTAL_SIGNUP_COUNTRY",
                    "options" => "countries_dom",
                    'required' => true,
                ),
                array(
                    'name' => 'state',
                    'type' => 'enum',
                    'placeholder' => "LBL_PORTAL_SIGNUP_STATE",
                    "options" => "state_dom",
                ),
                array(
                    'name' => 'hr2',
                    'type' => 'hr',
                    'view' => 'default',
                ),
                array(
                    'name' => 'company',
                    'type' => 'varchar',
                    'placeholder' => "LBL_PORTAL_SIGNUP_COMPANY",
                    'required' => true,
                ),
                array(
                    'name' => 'title',
                    'type' => 'varchar',
                    'placeholder' => "LBL_PORTAL_SIGNUP_JOBTITLE",
                ),
            ),
        ),
    ),
);
