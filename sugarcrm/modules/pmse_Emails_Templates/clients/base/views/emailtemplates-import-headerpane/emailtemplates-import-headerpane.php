<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


$module_name = 'pmse_Emails_Templates';
$viewdefs[$module_name]['base']['view']['emailtemplates-import-headerpane'] = array(
    'template' => 'headerpane',
    'title' => "LBL_IMPORT",
    'buttons' => array(
        array(
            'name'    => 'emailtemplates_cancel_button',
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'    => 'emailtemplates_finish_button',
            'type'    => 'button',
            'label'   => 'LBL_PMSE_IMPORT_BUTTON_LABEL',
            'acl_action' => 'create',
            'css_class' => 'btn-primary',
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);
