<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Accounts']['base']['view']['create'] = array(
    'type' => 'record',
    'buttons' => array(
        array(
            'name'    => 'cancel_button',
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'    => 'restore_button',
            'type'    => 'button',
            'label'   => 'LBL_RESTORE',
            'css_class' => 'hide btn-invisible btn-link',
        ),
        array(
            'name'    => 'save_create_button',
            'type'    => 'button',
            'label'   => 'LBL_SAVE_AND_CREATE_ANOTHER',
            'css_class' => 'hide btn-invisible btn-link',
        ),
        array(
            'name'    => 'save_view_button',
            'type'    => 'button',
            'label'   => 'LBL_SAVE_AND_VIEW',
            'css_class' => 'hide btn-invisible btn-link',
        ),
        array(
            'name'    => 'save_button',
            'type'    => 'button',
            'label'   => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'disabled',
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'placeholders' => true,
            'header' => true,
            'labels' => false,
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => '',
                    'placeholder' => 'LBL_NAME'
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labels' => false,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'assigned_user_name',
                'billing_address_street',
                'industry',
                array(
                    'name' => 'fieldset_address',
                    'type' => 'fieldset',
                    'label' => 'Billing Address',
                    'fields' => array('billing_address_city', 'billing_address_state', 'billing_address_postalcode')
                ),
                'website',
                'billing_address_country',
                'phone_office',
                'email1'
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'twitter',
            )
        )
    ),
);