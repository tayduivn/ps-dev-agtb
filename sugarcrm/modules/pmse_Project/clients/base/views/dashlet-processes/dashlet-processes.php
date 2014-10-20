<?php

$viewdefs['pmse_Project']['base']['view']['dashlet-processes'] = array(
    'dashlets' => array(
        array(
            'label' => 'PMSE Processes',
            'description' => 'ProcessMaker Processes',
            'config' => array(
                'limit' => 10,
                'visibility' => 'user',
            ),
            'preview' => array(
                'limit' => 10,
                'visibility' => 'user',
            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                    'Bugs',
                    'Cases',
                    'Contacts',
                    'Home',
                    'Leads',
                    'Opportunities',
                    'Prospects',
                    'RevenueLineItems',
//                    'pmse_Project',
//                    'pmse_BpmProcessDefinition',
                ),
                'view' => 'record',
            ),
        ),
    ),
    'custom_toolbar' => array(
        'buttons' => array(
            array(
                'type' => 'actiondropdown',
                'no_default_action' => true,
                'icon' => 'icon-plus',
                'buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'action' => 'createRecord',
                        'params' => array(
                            'module' => 'pmse_Project',
                            'link' => '#pmse_Project',
                        ),
                        'label' => 'Create Process',
                        'acl_action' => 'create',
                        'acl_module' => 'pmse_Project',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'importRecord',
                        'params' => array(
                            'module' => 'pmse_Project',
                            'link' => '#pmse_Project/layout/project-import',
                        ),
                        'label' => 'Import Process',
                        'acl_action' => 'create',
                        'acl_module' => 'pmse_Project',
                    ),
                ),
            ),
            array(
                'dropdown_buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'action' => 'editClicked',
                        'label' => 'LBL_DASHLET_CONFIG_EDIT_LABEL',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'refreshClicked',
                        'label' => 'LBL_DASHLET_REFRESH_LABEL',
                    ),
//                    array(
//                        'type' => 'dashletaction',
//                        'action' => 'toggleClicked',
//                        'label' => 'LBL_DASHLET_MINIMIZE',
//                        'event' => 'minimize',
//                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'removeClicked',
                        'label' => 'LBL_DASHLET_REMOVE_LABEL',
                    ),
                ),
            ),
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'visibility',
                    'label' => 'LBL_DASHLET_CONFIGURE_MY_ITEMS_ONLY',
                    'type' => 'enum',
                    'options' => 'tasks_visibility_options',
                ),
                array(
                    'name' => 'limit',
                    'label' => 'LBL_DASHLET_CONFIGURE_DISPLAY_ROWS',
                    'type' => 'enum',
                    'options' => 'tasks_limit_options',
                ),
            ),
        ),
    ),
//    'filter' => array(
//        array(
//            'name' => 'filter',
//            'label' => 'LBL_FILTER',
//            'type' => 'enum',
//            'options' => 'history_filter_options'
//        ),
//    ),
    'tabs' => array(
        array(
            'active' => true,
            'filters' => array(
                'prj_status' => array('$not_in' => array('INACTIVE')),
//                'prj_status' => array('$equals' => 'ACTIVE'),
            ),
            'label' => 'LBL_PMSE_BUTTON_ENABLE',
            'link' => 'pmse_Project',
            'module' => 'pmse_Project',
            'order_by' => 'date_entered:desc',
            'record_date' => 'date_entered',
            'row_actions' => array(
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-edit',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-processes:designer:fire',
                    'target' => 'view',
                    'tooltip' => 'Designer',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-remove',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-processes:delete-record:fire',
                    'target' => 'view',
                    'tooltip' => 'Delete',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-download-alt',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-processes:download:fire',
                    'target' => 'view',
                    'tooltip' => 'Export',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
//                    'icon' => 'icon-eye-open',
                    'icon' => 'icon-eye-close',
                    'css_class' => 'btn btn-mini',
//                    'event' => 'dashlet-processes:enable-record:fire',
                    'event' => 'dashlet-processes:disable-record:fire',
                    'target' => 'view',
//                    'tooltip' => 'Enable',
                    'tooltip' => 'Disable',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-info-sign',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-processes:description-record:fire',
                    'target' => 'view',
                    'tooltip' => 'Description',
                    'acl_action' => 'edit',
                ),
            ),
        ),
        array(
            'filters' => array(
                'prj_status' => array('$not_in' => array('ACTIVE')),
//                'prj_status' => array('$equals' => 'INACTIVE'),
            ),
            'label' => 'LBL_PMSE_BUTTON_DISABLE',
            'link' => 'pmse_Project',
            'module' => 'pmse_Project',
            'order_by' => 'date_entered:desc',
            'record_date' => 'date_entered',
            'row_actions' => array(
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-edit',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-processes:designer:fire',
                    'target' => 'view',
                    'tooltip' => 'Designer',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-remove',
                    'css_class' => 'btn btn-mini',
                    'event' => '',
                    'target' => 'view',
                    'tooltip' => 'Delete',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-download-alt',
                    'css_class' => 'btn btn-mini',
                    'event' => '',
                    'target' => 'view',
                    'tooltip' => 'Export',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
//                    'icon' => 'icon-eye-close',
                    'icon' => 'icon-eye-open',
                    'css_class' => 'btn btn-mini',
//                    'event' => 'dashlet-processes:disable-record:fire',
                    'event' => 'dashlet-processes:enable-record:fire',
                    'target' => 'view',
//                    'tooltip' => 'Disable',
                    'tooltip' => 'Enable',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'icon-info-sign',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-processes:description-record:fire',
                    'target' => 'view',
                    'tooltip' => 'Description',
                    'acl_action' => 'edit',
                ),
            ),
        ),
    ),
    'visibility_labels' => array(
        'user' => 'LBL_ACTIVE_TASKS_DASHLET_USER_BUTTON_LABEL',
        'group' => 'LBL_ACTIVE_TASKS_DASHLET_GROUP_BUTTON_LABEL',
    ),
);
