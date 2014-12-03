<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['casesList-headerpane'] = array(
    'template' => 'headerpane',
    'title' => "LBL_PMSE_TITLE_PROCESSESS_LIST",
    'buttons' => array(
//        array(
//            'name'    => 'project_cancel_button',
//            'type'    => 'button',
//            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
//            'css_class' => 'btn-invisible btn-link',
//        ),
//        array(
//            'name'    => 'project_finish_button',
//            'type'    => 'button',
//            'label'   => 'LBL_IMPORT_BUTTON_LABEL',
//            'acl_action' => 'create',
//            'css_class' => 'btn-primary',
//        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);