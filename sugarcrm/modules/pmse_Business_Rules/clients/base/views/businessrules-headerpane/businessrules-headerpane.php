<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Business_Rules';
$viewdefs[$module_name]['base']['view']['businessrules-headerpane'] = array(
    'template' => 'headerpane',
    'title' => "LBL_PMSE_TITLE_BUSINESS_RULES_BUILDER",
    'buttons' => array(
        array(
            'name'    => 'project_cancel_button',
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'    => 'project_save_button',
            'type'    => 'button',
            'label'   => 'LBL_SAVE_BUTTON_LABEL',
            'acl_action' => 'save',
            'css_class' => 'btn-primary',
        ),
        array(
            'name'    => 'project_finish_button',
            'type'    => 'button',
            'label'   => 'LBL_PMSE_SAVE_EXIT_BUTTON_LABEL',
            'acl_action' => 'create',
            'css_class' => 'btn-primary',
        ),
        array(
            'name' => 'sidebar_toggle_local',
            'type' => 'sidebartoggle',
        ),
    ),
);
