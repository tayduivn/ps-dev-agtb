<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['reassignCases-headerpane'] = array(
    'template' => 'headerpane',
    'title'    => 'LBL_TASK_TO_REASSIGN',
    'buttons'  => array(
        array(
            'name'      => 'cancel_button',
            'type'      => 'button',
            'label'     => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'      => 'done_button',
            'type'      => 'button',
            'label'     => 'LBL_DONE_BUTTON_LABEL',
            'css_class' => 'btn-primary',
        )
    ),
);
