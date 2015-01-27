<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['casesList-list'] = array(
    'template' => 'list',
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'rowaction',
                'icon' => 'fa-eye',
                'event' => 'list:preview:fire',
                'css_class'=>'overflow-visible',
                'tooltip'=> 'Status',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'History',
                'label' => 'LBL_PMSE_LABEL_HISTORY',
                'event' => 'case:history',
                'css_class'=>'overflow-visible',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'viewNotes',
                'label' => 'LBL_PMSE_LABEL_NOTES',
                'event' => 'case:notes',
                'css_class'=>'overflow-visible',
            ),
            array(
                'type' => 'reassignbutton',
                'name' => 'reassignButton',
                'label' => 'LBL_PMSE_LABEL_REASSIGN',
                'event' => 'case:reassign',
                'css_class'=>'overflow-visible',
            ),
            array(
                'type' => 'executebutton',
                'name' => 'executeButton',
                'label' => 'LBL_PMSE_LABEL_EXECUTE',
                'event' => 'case:execute',
            ),
            array(
                'type' => 'cancelcasebutton',
                'name' => 'cancelButton',
                'label' => 'LBL_PMSE_LABEL_CANCEL',
                'event' => 'list:cancelCase:fire',
            ),
        ),
    ),
//    'selection' =>
//        array (
//            'type' => 'multi',
//            'actions' =>
//                array (
//                    0 =>
//                        array (
//                            'name' => 'edit_button',
//                            'type' => 'button',
//                            'label' => 'LBL_CANCEL_CASE',
//                            'primary' => true,
//                            'events' =>
//                                array (
//                                    'click' => 'list:cancelCase:fire',
//                                ),
//                            'acl_action' => 'massupdate',
//                        ),
////                    1 =>
////                        array (
////                            'name' => 'execute_cases_button',
////                            'type' => 'button',
////                            'label' => 'LBL_EXECUTE_CASE',
////                            'primary' => true,
////                            'events' =>
////                                array (
////                                    'click' => 'list:executeCase:fire',
////                                ),
////                            'acl_action' => 'massupdate',
////                        ),
//                ),
//        ),
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'cas_id',
                    'label' => 'LBL_CAS_ID',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                ),
                array(
                    'name' => 'pro_title',
                    'label' => 'LBL_PROCESS_DEFINITION_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                ),
                array(
                    'name' => 'cas_title',
                    'label' => 'LBL_RECORD_NAME',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'cas_status',
                    'label' => 'LBL_STATUS',
                    'type' => 'html',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'cas_create_date',
                    'readonly' => true,
                ),
                array(
                    'label' => 'LBL_OWNER',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'last_name',
                    'readonly' => true,
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'cas_create_date',
        'direction' => 'desc',
    ),
);
