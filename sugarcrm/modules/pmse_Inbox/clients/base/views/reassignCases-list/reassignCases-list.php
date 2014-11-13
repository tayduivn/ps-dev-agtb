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
                    'label' => 'Current Task',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_delegate_date',
                    'label' => 'Task Delegate Data',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_expected_time',
                    'label' => 'Expected Time',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_due_date',
                    'label' => 'Due Date',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'assigned_user',
                    'label' => 'LBL_ASSIGNED_USER',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_reassign_user_combo_box',
                    'label' => 'LBL_NEW_ASSIGNED_USER',
                    'type' => 'enum',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
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
