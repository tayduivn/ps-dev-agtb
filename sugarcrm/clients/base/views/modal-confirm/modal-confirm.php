<?php
$viewdefs['base']['view']['modal-confirm'] = array(
    'type' => 'edit',
    'buttons' => array(
        array(
            'name' => 'ok_button',
            'type' => 'button',
            'css_class' => 'btn-primary pull-right',
            'label' => 'LBL_EMAIL_OK',
            'primary' => true,
        ),
        array(
            'name' => 'close_button',
            'type' => 'button',
            'css_class' => 'btn-invisible',
            'label' => 'LNK_CLOSE',
            'primary' => false,
        ),
    ),
);
