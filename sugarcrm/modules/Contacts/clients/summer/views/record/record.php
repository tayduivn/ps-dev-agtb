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
 * $Id$
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Contacts']['summer']['view']['record'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
        ),
    ),
    'buttons' => array(
        array(
            'name' => 'edit_button',
            'type' => 'button',
            'label' => 'Edit',
            'route' => array(
                'action' => 'edit'
            ),
            'primary' => true,
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_head',
            'label' => 'LBL_PANEL_1',
            'columns' => 3,
            'labels' => false,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                array('name' => 'img', 'noedit' => true, 'span' => 2, 'label' => ''),
                array('name' => 'first_name', 'css' => 'big', 'span' => 5, 'label' => '', 'placeholder' => 'LBL_FIRST_NAME'),
                array('name' => 'last_name', 'css' => 'big', 'span' => 5, 'label' => '', 'placeholder' => 'LBL_LAST_NAME'),
            )
        ),
        array(
            'label' => 'panel_body',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'title',
                'account_name',
                'email1',
                'primary_address_street',
                'phone_mobile',
                array(
                    'name' => 'fieldset_address',
                    'type' => 'fieldset',
                    'label' => 'Primary Address',
                    'fields' => array('primary_address_city', 'primary_address_state', 'primary_address_postalcode')
                ),
                'phone_work',
                'primary_address_country',

                'assigned_user_name',
            ),
        ),
        array(
            'label' => 'panel_hidden',
            'fields' => array(
                'twitter',
            ),
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'hide' => true
        )
    )
);
