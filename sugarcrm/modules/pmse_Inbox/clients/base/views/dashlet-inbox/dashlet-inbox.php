<?php

$viewdefs['pmse_Inbox']['base']['view']['dashlet-inbox'] = array (
    'dashlets' => array(
        array(
            'label' => 'PMSE Inbox',
            'description' => 'ProcessMaker Inbox',
            'config' => array(
                'limit' => 10,
                'date' => 'true',
                'visibility' => 'user',
            ),
            'preview' => array(
                'limit' => 10,
                'date' => 'true',
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
//                    'pmse_Inbox',
//                    'pmse_BpmProcessDefinition',
                ),
                'view' => 'record',
            ),
        ),
    ),
    'custom_toolbar' => array(
        'buttons' => array(
//            array(
//                'type' => 'actiondropdown',
//                'no_default_action' => true,
//                'icon' => 'fa-plus',
//                'buttons' => array(
//                    array(
//                        'type' => 'dashletaction',
//                        'action' => 'createRecord',
//                        'params' => array(
//                            'module' => 'pmse_Project',
//                            'link' => '#pmse_Project',
//                        ),
//                        'label' => 'LBL_PMSE_BUTTON_CREATEPROCESS',
//                        'acl_action' => 'create',
//                        'acl_module' => 'pmse_Project',
//                    ),
//                    array(
//                        'type' => 'dashletaction',
//                        'action' => 'createRecord',
//                        'params' => array(
//                            'module' => 'pmse_Project',
//                            'link' => 'Process',
//                        ),
//                        'label' => 'LBL_PMSE_BUTTON_IMPORTPROCESS',
//                        'acl_action' => 'create',
//                        'acl_module' => 'pmse_Project',
//                    ),
//                ),
//            ),
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
//    'panels' => array(
//        array(
//            'name' => 'panel_body',
//            'columns' => 2,
//            'labelsOnTop' => true,
//            'placeholders' => true,
//            'fields' => array(
//                array(
//                    'name' => 'visibility',
//                    'label' => 'LBL_DASHLET_CONFIGURE_MY_ITEMS_ONLY',
//                    'type' => 'enum',
//                    'options' => 'tasks_visibility_options',
//                ),
//                array(
//                    'name' => 'limit',
//                    'label' => 'LBL_DASHLET_CONFIGURE_DISPLAY_ROWS',
//                    'type' => 'enum',
//                    'options' => 'tasks_limit_options',
//                ),
//            ),
//        ),
//    ),
//comment for fail


    /*'filter' => array(
        array(
            'name' => 'filter',
            'label' => 'LBL_FILTER',
            'type' => 'enum',
            'options' => 'history_filter_options'
        ),
    ),*/


    'panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
//                array(
//                    'name' => 'date',
//                    'label' => 'LBL_DASHLET_CONFIGURE_FILTERS',
//                    'type' => 'enum',
//                    'options' => 'planned_activities_filter_options',
//                ),
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
    'tabs' => array(
        array(
            'active' => true,
            'filter_applied_to' => 'in_time',
            'filters' => array(
                //'custom_source' => 'PMSE',
                //'cas_ownership' => array('$equals' => 'MYCASES'),
                //'created_by' => array('$not_in' => array('1')),
                //'assignment_method' => array('$not_in' => 'selfservice'),
                'act_assignment_method' => array('$equals' => 'static'),
            ),
            'label' => 'My Cases',
            'link' => 'pmse_Inbox',
            'module' => 'pmse_Inbox',
            'order_by' => 'date_entered:asc',
            'record_date' => 'date_entered',
            'include_child_items' => true,
        ),
        array(
            'filter_applied_to' => 'in_time',
            'filters' => array(
                //'custom_source' => 'PMSE',
                //'assignment_method' => array('$equals' => 'selfservice'),
                'act_assignment_method' => array('$equals' =>array('selfservice','BALANCED')),
                //'assigned_user_id' => array('$not_in' => array('1')),
            ),
            //'fields' => array('cas_id','cas_enrique'),
            'label' => 'Self Service',
            'link' => 'pmse_Inbox',
            'module' => 'pmse_Inbox',
            'order_by' => 'date_entered:asc',
            'record_date' => 'date_entered',
            'include_child_items' => true,
        ),
    ),
);
