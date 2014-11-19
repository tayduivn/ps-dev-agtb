<?php

$viewdefs['pmse_Emails_Templates']['base']['view']['compose-varbook-list'] = array(
    'template'   => 'list',
    'selection'  => array(
        'type'                     => 'multi',
        'actions'                  => array(),
        'disable_select_all_alert' => true,
    ),
    'panels'     => array(
        array(
            'fields' => array(
                array(
                    'name'    => 'name',
                    'label'   => 'LBL_LIST_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name'     => '_module',
                    'label'    => 'LBL_MODULE',
                    'sortable' => false,
                    'enabled'  => true,
                    'default'  => true,
                ),
            ),
        ),
//        'orderBy' =>
//            array (
//                'field' => 'name',
//                'direction' => 'desc',
//            ),
    ),
//    'rowactions' => array(
//        'css_class' => 'pull-right',
//        'actions'   => array(
//            array(
//                'type'       => 'rowaction',
//                'css_class'  => 'btn',
//                'tooltip'    => 'LBL_PREVIEW',
//                'event'      => 'list:preview:fire',
//                'icon'       => 'fa-eye',
//                'acl_action' => 'view',
//            ),
//        ),
//    ),
);
