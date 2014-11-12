<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');



$module_name = 'pmse_Business_Rules';
$viewdefs[$module_name]['base']['view']['businessrules-import-headerpane'] = array(
    'template' => 'headerpane',
    'title' => "LNK_IMPORT_BUSINESSRULES",
    'buttons' => array(
        array(
            'name'    => 'businessrules_cancel_button',
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'    => 'businessrules_finish_button',
            'type'    => 'button',
            'label'   => 'LBL_IMPORT_BUTTON_LABEL',
            'acl_action' => 'create',
            'css_class' => 'btn-primary',
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);
