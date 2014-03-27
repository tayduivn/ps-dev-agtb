<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
$viewdefs['KBSContents']['base']['view']['record'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_HEADER',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'picture',
                    'type' => 'avatar',
                    'size' => 'large',
                    'dismiss_label' => true,
                    'readonly' => true,
                    'span' => 8,
                ),
                'name',
                array(
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ),
                array(
                    'name' => 'follow',
                    'label' => 'LBL_FOLLOW',
                    'type' => 'follow',
                    'readonly' => true,
                    'dismiss_label' => true,
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'status',
                    'span' => 2,
                ),
                'language' => array(
                    'name' => 'language',
                    'type' => 'enum-config',
                    'module' => 'KBSDocuments',
                    'key' => 'languages',
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'kbdocument_body' => array(
                    'name' => 'kbdocument_body',
                    'type' => 'htmleditable_tinymce',
                    'span' => 12,
                    'dismiss_label' => true,
                    'fieldSelector' => 'kbdocument_body',
                    'tinyConfig' => array(
                        'height' => '300',
                        'width' => '100%',
                        'plugins' => 'style,paste,inlinepopups',
                        'forced_root_block' => false,
                        'theme_advanced_buttons1' => "code,separator,bold,italic,underline,strikethrough,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,forecolor,backcolor,separator,fontsizeselect",
                        'theme_advanced_buttons2' => null,
                        'theme_advanced_resizing' => false,
                    ),
                ),
                'attachment_list' => array(
                    'name' => 'attachment_list',
                    'label' => 'LBL_ATTACHMENTS',
                    'type' => 'attachments',
                    'link' => 'notes',
                    'module' => 'Notes',
                    'modulefield' => 'filename',
                    'bLable' => 'LBL_ADD_ATTACHMENT',
                    'bIcon' => 'icon-paper-clip',
                    'span' => 12,
                ),
                'usefulness' => array(
                    'name' => 'usefulness',
                    'type' => 'usefulness',
                    'span' => 12,
                    'fields' => array(
                        'useful',
                        'notuseful',
                    ),
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'label' => 'LBL_SHOW_MORE',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'kbsdocument_name' => array(
                    'name' => 'kbsdocument_name',
                    'readonly' => true,
                ),
                'topic_name' => array(
                    'name' => 'topic_name',
                    'label' => 'LBL_TOPIC_NAME',
                ),
                'active_date' => array(
                    'name' => 'active_date'
                ),
                'exp_date' => array(
                    'name' => 'exp_date'
                ),
                'created_by_name' => array(
                    'name' => 'created_by_name'
                ),
                'team_name' => array(
                    'name' => 'team_name'
                ),
                'date_entered' => array(
                    'name' => 'date_entered'
                ),
                'date_modified' => array(
                    'name' => 'date_modified'
                ),
            ),
        ),
    ),
);
