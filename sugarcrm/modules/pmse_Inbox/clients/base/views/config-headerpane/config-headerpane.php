<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['config-headerpane'] = array(
    'template' => 'headerpane',
    'title' => 'LBL_PMSE_SETTINGS',
    'buttons' => array(
        array(
            'name'      => 'cancel_button',
            'type'      => 'button',
            'label'     => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'      => 'save_button',
            'type'      => 'button',
            'label'     => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn-primary',
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);