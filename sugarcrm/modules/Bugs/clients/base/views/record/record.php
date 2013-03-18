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

$viewdefs['Bugs']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
        ),
        array(
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => array(
                array(
                    'type' => 'rowaction',
                    'event' => 'button:edit_button:click',
                    'name' => 'edit_button',
                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                    'primary' => true,
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:delete_button:click',
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:duplicate_button:click',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'acl_action' => 'create',
                ),
                /*
                array(
                    'type'  => 'rowaction',
                    'event' => 'button:find_duplicates_button:click',
                    'name'  => 'find_duplicates_button',
                    'label' => 'LBL_DUP_MERGE',
                    'acl_action' => 'edit',
                ),
                array(
                    'type'  => 'rowaction',
                    'event' => 'button:create_related_button:click',
                    'name'  => 'create_related_button',
                    'label' => 'LBL_CREATE_RELATED_RECORD',
                    'acl_action' => 'create',
                ),
                array(
                    'type'  => 'rowaction',
                    'event' => 'button:link_related_button:click',
                    'name'  => 'link_related_button',
                    'label' => 'LBL_ASSOC_RELATED_RECORD',
                    'acl_action' => 'edit',
                ),
                */
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    'type' => 'rowaction',
                    'route' => '#bwc/index.php?module=KBDocuments&action=EditView&return_module=KBDocuments&return_action=DetailView',
                    'name' => 'create_kbdocument_button',
                    'label' => 'LBL_CREATE_KB_DOCUMENT',
                    'acl_module' => 'KBDocuments',
                    'acl_action' => 'create',
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'type' => 'rowaction',
                    'event' => 'button:change_log_button:click',
                    'name' => 'change_log_button',
                    'label' => 'LNK_VIEW_CHANGE_LOG',
                    'acl_action' => 'view'
                ),
            ),
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
                'name',
                array(
                    'type' => 'favorite',
                    'readonly' => true,
                ),
                array(
                    'type' => 'follow',
                    'readonly' => true,
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'bug_number',
                    'readonly' => true,
                ),
                'priority',
                'status',
                'type',
                'source',
                'product_category',
                'resolution',
                //BEGIN SUGARCRM flav=ent ONLY
                'assigned_user_name',
                //END SUGARCRM flav=ent ONLY
                array(
                    'name' => 'description',
                    'nl2br' => true,
                    'span' => 12,
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'columns' => 2,
            'hide' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'found_in_release',
                'fixed_in_release',
                /**
                 * TODO: Did we want this? In the biz card it looked like we didn't have this
                 */
                // array('name'=>'portal_viewable', 'label' => 'LBL_SHOW_IN_PORTAL', 'hideIf' => 'empty($PORTAL_ENABLED)'),

                //BEGIN SUGARCRM flav=pro ONLY
                // hideIf is a legacy smarty thing .. seems that hideIf is mainly used for this specific check
                // semantically meaning: "hide unless portal enabled" .. TODO: implement equivalent functionality in sidecar
                // perhaps create an hbt helper that can leverage app.cofig.on
                array(
                    'name' => 'team_name',
                    'required' => true,
                ),
                array(
                    'type' => 'html',
                    'default_value' => '',
                    'readonly' => true,
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'date_entered_by',
                    'readonly' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_DATE_ENTERED',
                    'fields' => array(
                        array(
                            'name' => 'date_entered',
                        ),
                        array(
                            'type' => 'label',
                            'default_value' => 'LBL_BY'
                        ),
                        array(
                            'name' => 'created_by_name',
                        ),
                    ),
                ),
                array(
                    'name' => 'date_modified_by',
                    'readonly' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_DATE_MODIFIED',
                    'fields' => array(
                        array(
                            'name' => 'date_modified',
                        ),
                        array(
                            'type' => 'label',
                            'default_value' => 'LBL_BY'
                        ),
                        array(
                            'name' => 'modified_by_name',
                        ),
                    ),
                ),
                array(
                    'name' => 'work_log',
                    'nl2br' => true,
                    'span' => 12,
                ),
            ),
        ),
    ),
);
