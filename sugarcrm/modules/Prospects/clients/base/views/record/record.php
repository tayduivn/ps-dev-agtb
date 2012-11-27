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

$viewdefs['Prospects']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type'    => 'button',
            'label'   => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'hide btn-primary record-save',
        ),
        array(
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'hide record-cancel',
        ),
        array(
            'type'    => 'button',
            'label'   => 'LBL_EDIT_BUTTON_LABEL',
            'css_class' => 'record-edit',
        ),
        array(
            'type'    => 'button',
            'label'   => 'LBL_DELETE_BUTTON_LABEL',
            'css_class' => 'record-delete',
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'fieldset_name',
                    'type' => 'fieldset',
                    'fields' => array('salutation', 'first_name', 'last_name'),
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'account_name',
                    'span' => 12,
                ),
                'title',
                'email',
                'department',
                'phone_mobile',
                array(
                    'name' => 'fieldset_address',
                    'type' => 'fieldset',
                    'label' => 'Primary Address',
                    'fields' => array(
                        'primary_address_street',
                        'primary_address_city',
                        'primary_address_state',
                        'primary_address_postalcode',
                        'primary_address_country',
                    ),
                ),
                'phone_work',
                'do_not_call',
                'email_opt_out',
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
                'phone_other',
                'referred_by', //TODO: does not exist so the columns are thrown off. remove and make phone_other a span of 12 and the columns dispaly correctly
                'assigned_user_name',
                'date_modified',
                'team_name',
                'date_entered', //TODO: this won't show until the teams widget is fixed
            ),
        ),
    ),
);
