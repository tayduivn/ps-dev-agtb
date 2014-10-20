<?php

$viewdefs['pmse_Inbox']['base']['view']['config-log-headerpane'] = array(
    'template' => 'headerpane',
    'title'    => 'ProcessMaker Config Log',
    'buttons'  => array(
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
