<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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

/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Opportunities']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'mode' => 'edit',
        ),
        array(
            'type' => 'buttondropdown',
            'name' => 'edit_dropdown',
            'default' => array(
                'name' => 'edit_button',
                'label' => 'LBL_EDIT_BUTTON_LABEL',
            ),
            'dropdown' => array(
                array(
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                ),
                array(
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                ),
            ),
            'mode' => 'view',
        ),
        array(
            'type' => 'buttondropdown',
            'name' => 'save_dropdown',
            'default' => array(
                'name' => 'save_button',
                'label' => 'LBL_SAVE_BUTTON_LABEL',
            ),
            'dropdown' => array(
                array(
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                ),
            ),
            'mode' => 'edit',
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
                    'fields' => array('name'),
                ),
                array(
                    'type' => 'favorite',
                    'noedit' => true,
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_2',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'account_name',
                'date_closed',
                array(
                    'name' => 'fieldset_amount',
                    'type' => 'fieldset',
                    'label' => 'LBL_LIST_AMOUNT',
                    'fields' => array('amount'),
                ),
                array('name'=>'htmlfield', 'type'=>'html', 'default_value'=>'&nbsp;'),
            )
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'columns' => 2,
            'fields' => array(
                'campaign_id',
                'lead_source',
                'opportunity_type',
                'assigned_user_name',
                'team_id',
                'next_step',
                'description',
            )
        )
    ),
);
