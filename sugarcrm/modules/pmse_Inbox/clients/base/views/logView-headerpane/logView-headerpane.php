<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['logView-headerpane'] = array(
    'template' => 'headerpane',
    'title' => 'LBL_PMSE_TITLE_LOG_VIEWER',
    'buttons' => array(
        array(
            'name'    => 'log_pmse_button',
            'type'    => 'button',
            'label'   => 'LBL_PMSE_BUTTON_PROCESS_AUTHOR_LOG',
            'acl_action' => 'create',
            'css_class' => 'btn-primary',
        ),
        array(
            'name'    => 'log_sugarcrm_button',
            'type'    => 'button',
            'label'   => 'LBL_PMSE_BUTTON_SUGARCRM_LOG',
            'acl_action' => 'create',
            'css_class' => 'btn-primary',
        ),
//        array(
////            'name'    => 'Config',
//            'type'    => 'button',
////            'label'   => 'LBL_SUGAR_CRM_LOG',
//            'icon' => 'icon-cog',
//            'acl_action' => 'create',
//            'tooltip'=> 'Config PMSE Log',
//            'events' =>
//                array (
//                    'click' => 'configLog:fire',
//                ),
////            'css_class' => 'btn-primary',
//        ),
//        array(
//            'name'    => 'log_cron_button',
//            'type'    => 'button',
//            'label'   => 'LBL_CRON_LOG',
//            'acl_action' => 'create',
//            'css_class' => 'btn-primary',
//        ),
//        array(
//            'name' => 'sidebar_toggle',
//            'type' => 'sidebartoggle',
//        ),
    ),
);
