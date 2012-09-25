<?php
$viewdefs['Emails']['base']['view']['quickedit'] = array(
    'type' => 'edit',
    
    'buttons' => array(
        array(
            'name'    => 'save_button',
            'type'    => 'button',
            'label'   => 'LBL_SEND_BUTTON_LABEL',
            'primary' => true,
        ),
        array(
            'name'    => 'save_draft_button',
            'type'    => 'button',
            'label'   => 'LBL_SAVE_AS_DRAFT_BUTTON_LABEL',
            'primary' => true,
        ),
        array(
            'name'    => 'cancel_button',
            'type'    => 'button',
            'label'   => 'Cancel',
            'value'   => 'cancel',
            'events'  => array(
                'click' => 'function(){ window.history.back(); }',
            ),
            'primary' => false,
        ),
    ),
    
    'panels' => array(
        array(
            'label' => 'LBL_EMAILS_QUICK_SEND',
            'fields' => array(
                array(
                    'name' => 'to_addresses',
                    'label' => 'LBL_TO_ADDRS',
                ),
                array(
                    'name' => 'cc_addresses',
                    'label' => 'LBL_CC',
                ),
                array(
                    'name' => 'bcc_addresses',
                    'label' => 'LBL_BCC',
                ),
                array(
                    'name' => 'subject',
                    'label' => 'LBL_SUBJECT',
                ),
                array(
                    'name' => 'html_body',
                    'label' => 'LBL_HTML_BODY',
                    'type' => 'htmleditable',
                ),
                array(
                    'name' => 'text_body',
                    'label' => 'LBL_TEXT_BODY',
                    'type' => 'textarea',
                ),
            ),
        ),
    ),
);
