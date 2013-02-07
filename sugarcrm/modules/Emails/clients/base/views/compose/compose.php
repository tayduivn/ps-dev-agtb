<?php
$viewdefs['Emails']['base']['view']['compose'] = array(
    'type' =>'record',
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'buttons' => array(
                array(
                    'name'      => 'send_button',
                    'type'      => 'button',
                    'label'     => 'LBL_SEND_BUTTON_LABEL',
                    'value'     => 'send',
                    'primary' => true,
                ),
                array(
                    'name'      => 'draft_button',
                    'type'      => 'button',
                    'label'     => 'LBL_SAVE_AS_DRAFT_BUTTON_LABEL',
                    'value'     => 'draft',
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name'         => 'panel_body',
            'label'        => 'LBL_PANEL_2',
            'columns'      => 1,
            'labels'       => true,
            'labelsOnTop'  => false,
            'placeholders' => true,
            'fields'       => array(
                array(
                    "name"                => "to_addresses",
                    "type"                => "recipients",
                    "label"               => "LBL_TO_ADDRS",
                    'css_class_container' => 'controls-one btn-fit',
                ),
                array(
                    "name"                => "cc_addresses",
                    "type"                => "recipients",
                    "label"               => "LBL_CC",
                    'css_class_container' => 'controls-one btn-fit',
                ),
                array(
                    "name"                => "bcc_addresses",
                    "type"                => "recipients",
                    "label"               => "LBL_BCC",
                    'css_class_container' => 'controls-one btn-fit',
                ),
                array(
                    'name'      => 'subject',
                    'label'     => 'LBL_SUBJECT',
                    'css_class' => 'inherit-width',
                ),
                array(
                    'name'      => 'html_body',
                    'type'      => 'htmleditable_tinymce',
                    'tinyConfig' => array(
                        // Location of TinyMCE script
                        'script_url' => 'include/javascript/tiny_mce/tiny_mce.js',

                        // General options
                        'theme' => "advanced",
                        'skin' => "sugar7",
                        'plugins' => "style,searchreplace,print,contextmenu,paste,noneditable,visualchars,nonbreaking,xhtmlxtras",
                        'entity_encoding' => "raw",

                        // Theme options
                        'theme_advanced_buttons1' => "code,help,separator,bold,italic,underline,strikethrough,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,forecolor,backcolor,separator,spellchecker,seperator,styleselect,formatselect,fontselect,fontsizeselect",
                        'theme_advanced_toolbar_location' => "top",
                        'theme_advanced_toolbar_align' => "left",
                        'theme_advanced_statusbar_location' => "bottom",
                        'theme_advanced_resizing' => false,
                        'schema' => "html5",
                        'template_external_list_url' => "lists/template_list.js",
                        'external_link_list_url' => "lists/link_list.js",
                        'external_image_list_url' => "lists/image_list.js",
                        'media_external_list_url' => "lists/media_list.js",
                        'theme_advanced_path' => false
                    ),
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 1,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    "type" => "teamset",
                    "name" => "team_name",
                ),
                //END SUGARCRM flav=pro ONLY
                array (
                    "label" => "LBL_LIST_RELATED_TO",
                    'type' => 'parent',
                    'name' => 'parent_name'
                ),
            ),
        ),
    ),
);
