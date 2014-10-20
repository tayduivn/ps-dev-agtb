<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['unattendedCases-list'] = array(
    'favorite' => false,
    'following' => false,
    'selection' => array(
    ),
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'rowaction',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'icon-eye-open',
                'acl_action' => 'view',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'edit_button',
                'label' => 'LBL_PMSE_LABEL_REASSIGN',
                'event' => 'list:reassign:fire',
                'acl_action' => 'view',
            ),
        ),
    ),
    'last_state' => array(
        'id' => 'record-list',
    ),
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'cas_id',
                    'label' => 'LBL_CASE_ID',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_title',
                    'label' => 'LBL_CASE_TITLE',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'pro_title',
                    'label' => 'LBL_PROCESS_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_status',
                    'label' => 'LBL_STATUS',
                    'default' => false,
                    'enabled' => false,
                    'link' => false,
                ),
                array(
                    'name' => 'cas_init_user',
                    'label' => 'LBL_OWNER',
                    'width' => 9,
                    'default' => true,
                    'enabled' => true,
                    'link' => false,
                ),
                array(
                    'label' => 'LBL_DATE_CREATED',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'date_entered',
                    'readonly' => true,
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'cas_id',
        'direction' => 'desc',
    ),
);
