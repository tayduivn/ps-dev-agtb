<?php

$viewdefs['pmse_Emails_Templates']['base']['view']['dashlet-email'] = array(
    'dashlets' => array(
        array(
            'label' => 'PMSE Email Templates',
            'description' => 'ProcessMaker Email Templates',
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
//                    'Accounts',
//                    'Bugs',
//                    'Cases',
//                    'Contacts',
                    'Home',
//                    'Leads',
//                    'Opportunities',
//                    'Prospects',
                    'pmse_Emails_Templates',
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
                'icon' => 'fa-plus',
                'buttons' => array(
                    array(
                        'type' => 'dashletaction',
                        'action' => 'createRecord',
                        'params' => array(
                            'module' => 'pmse_Emails_Templates',
                            'link' => '#pmse_Emails_Templates',
                        ),
                        'label' => 'Create Email Template',
                        'acl_action' => 'create',
                        'acl_module' => 'pmse_Emails_Templates',
                    ),
                    array(
                        'type' => 'dashletaction',
                        'action' => 'importRecord',
                        'params' => array(
                            'module' => 'pmse_Emails_Templates',
                            'link' => '#pmse_Emails_Templates/layout/emailtemplates-import'
                        ),
                        'label' => 'Import Email Template',
                        'acl_action' => 'importRecord',
                        'acl_module' => 'pmse_Emails_Templates',
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
    'filter' => array(
        array(
            'name' => 'filter',
            'label' => 'LBL_FILTER',
            'type' => 'enum',
            'options' => 'history_filter_options'
        ),
    ),
    'tabs' => array(
        array(
            'active' => true,
            'filters' => array(
                //'assigned_user_id' => array('$not_in' => array('')),
                //'cas_assigned_status' => array('$equals' => 'ASSIGNED'),
            ),
//            'label' => 'LBL_PMSE_BUTTON_ASSIGNED',
            'label' => 'PMSE Email Templates',
            'link' => 'pmse_Emails_Templates',
            'module' => 'pmse_Emails_Templates',
            'order_by' => 'date_entered:desc',
            'record_date' => 'date_entered',
            'row_actions' => array(
                array(
                    'type' => 'rowaction',
                    'icon' => 'fa-pencil',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-email:edit:fire',
                    'target' => 'view',
                    'tooltip' => 'Edit',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'fa-times',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-email:delete-record:fire',
                    'target' => 'view',
                    'tooltip' => 'Delete',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'fa-download',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-email:download:fire',
                    'target' => 'view',
                    'tooltip' => 'Export',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'icon' => 'fa-info-circle',
                    'css_class' => 'btn btn-mini',
                    'event' => 'dashlet-email:description-record:fire',
                    'target' => 'view',
                    'tooltip' => 'Description',
                    'acl_action' => 'edit',
                ),
            ),
        ),
    ),
);
