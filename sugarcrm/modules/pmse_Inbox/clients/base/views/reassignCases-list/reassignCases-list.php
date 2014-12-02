<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['reassignCases-list'] = array(
    'template'   => 'list',
    'selection' => array(
    ),
    'rowactions' => array(
    ),
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'act_name',
                    'label' => 'LBL_PMSE_LABEL_CURRENT_ACTIVITY',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_delegate_date',
                    'label' => 'LBL_PMSE_LABEL_ACTIVITY_DELEGATE_DATE',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_expected_time',
                    'label' => 'LBL_PMSE_LABEL_EXPECTED_TIME',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_due_date',
                    'label' => 'LBL_PMSE_LABEL_DUE_DATE',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
//                array(
//                    'name' => 'assigned_user',
//                    'label' => 'LBL_ASSIGNED_USER',
//                    'default' => true,
//                    'enabled' => true,
//                    'link' => false,
//                ),
                array(
                    'name' => 'assigned_user',
//                    'label' => 'LBL_NEW_ASSIGNED_USER',
                    'label' => 'LBL_ASSIGNED_USER',
                    'link' => 'assigned_user_link',
                    'vname' => 'LBL_ASSIGNED_TO',
                    'rname' => 'full_name',
                    'type' => 'relate',
                    'reportable' => false,
                    'source' => 'non-db',
                    'table' => 'users',
                    'id_name' => 'id',
                    'module' => 'Users',
                    'duplicate_merge' => 'disabled',
                    'duplicate_on_record_copy' => 'always',
                    'sort_on' =>
                        array (
                            0 => 'last_name',
                        ),
                    'view' => 'edit',
                ),
//                array(
//                    'name' => 'cas_reassign_user_combo_box',
//                    'label' => 'New User',
//                    'default' => true,
//                    'enabled' => true,
//                    'link' => false,
//                    'combo_user'=> true,
//                )
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'act_name',
        'direction' => 'desc',
    ),
);
