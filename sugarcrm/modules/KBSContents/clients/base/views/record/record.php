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
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
        ),
        array(
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => array(
                array(
                    'type' => 'rowaction',
                    'event' => 'button:edit_button:click',
                    'name' => 'edit_button',
                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:create_localization_button:click',
                    'name' => 'create_localization_button',
                    'label' => 'LBL_CREATE_LOCALIZATION_BUTTON_LABEL',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:create_revision_button:click',
                    'name' => 'create_revision_button',
                    'label' => 'LBL_CREATE_REVISION_BUTTON_LABEL',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'divider',
                ),
                array(
                    'type' => 'shareaction',
                    'name' => 'share',
                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'pdfaction',
                    'name' => 'download-pdf',
                    'label' => 'LBL_PDF_VIEW',
                    'action' => 'download',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'pdfaction',
                    'name' => 'email-pdf',
                    'label' => 'LBL_PDF_EMAIL',
                    'action' => 'email',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'divider',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:find_duplicates_button:click',
                    'name' => 'find_duplicates_button',
                    'label' => 'LBL_DUP_MERGE',
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:duplicate_button:click',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'acl_module' => 'KBSContents',
                    'acl_action' => 'create',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:audit_button:click',
                    'name' => 'audit_button',
                    'label' => 'LNK_VIEW_CHANGE_LOG',
                    'acl_action' => 'view',
                ),
                array(
                    'type' => 'divider',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:delete_button:click',
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
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
                array(
                    'name' => 'name',
                    'related_fields' => array(
                        'useful',
                        'notuseful',
                    ),
                ),
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
                    'enum_width' => '200px',
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
                array(
                    'name' => 'kbdocument_body_set',
                    'type' => 'fieldset',
                    'label' => 'LBL_TEXT_BODY',
                    'span' => 12,
                    'fields' => array(
                        array(
                            'name' => 'template',
                            'type' => 'button',
                            'icon' => 'icon-file-alt',
                            'css_class' => 'btn pull-right load-template',
                            'label' => 'LBL_TEMPLATES',
                        ),
                        array(
                            'name' => 'kbdocument_body',
                            'type' => 'htmleditable_tinymce',
                            'dismiss_label' => false,
                            'fieldSelector' => 'kbdocument_body',
                            'tinyConfig' => array(
                                'height' => '300',
                                'width' => '100%',
                                'plugins' => 'style,paste,inlinepopups,advimage,advlink',
                                'forced_root_block' => false,
                                'theme_advanced_buttons1' => 'code,separator,bold,italic,underline,strikethrough,' .
                                    'separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,' .
                                    'justifyfull,separator,forecolor,backcolor,separator,cleanup,removeformat,' .
                                    'separator,image,link',
                                'theme_advanced_buttons2' => 'fontsizeselect, formatselect, styleselect',
                                'theme_advanced_resizing' => false,
                                'theme_advanced_blockformats' => 'h1,h2,h3,h4,h5,h6,code,p,div',
                                'theme_advanced_font_sizes' => 'Normal=.fontSizeNormal,Header=.fontSizeHeader,Large=.' .
                                    'fontSizeLarge,Medium=.fontSizeMedium,Small=.fontSizeSmall,Mini=.fontSizeMini',
                                'style_formats' => array(
                                    array(
                                        'title' => 'Bold text',
                                        'inline' => 'b',
                                    )
                                ),
                            ),
                        ),
                        array(
                            'name' => 'filename',
                            'type' => 'file',
                            'css_class' => 'hidden',
                            'comment' => 'Used for uploading embedded files',
                        ),
                    ),
                ),
                'attachment_list' => array(
                    'name' => 'attachment_list',
                    'label' => 'LBL_ATTACHMENTS',
                    'type' => 'attachments',
                    'link' => 'attachments',
                    'module' => 'Notes',
                    'modulefield' => 'filename',
                    'bLabel' => 'LBL_ADD_ATTACHMENT',
                    'span' => 12,
                ),
                'tags' => array(
                    'name' => 'tags',
                    'type' => 'tags',
                    'span' => 12,
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
                'language' => array(
                    'name' => 'language',
                    'type' => 'enum-config',
                    'module' => 'KBSDocuments',
                    'key' => 'languages',
                ),
                'revision' => array(
                    'name' => 'revision',
                    'readonly' => true,
                ),
                'topic_name' => array(
                    'name' => 'topic_name',
                    'label' => 'LBL_TOPIC_NAME',
                ),
                'team_name' => array(
                    'name' => 'team_name',
                ),

                'date_entered' => array(
                    'name' => 'date_entered',
                ),
                'created_by_name' => array(
                    'name' => 'created_by_name',
                ),
                'date_modified' => array(
                    'name' => 'date_modified',
                ),
                'kbsapprover_name' => array(
                    'name' => 'kbsapprover_name',
                ),
                'active_date' => array(
                    'name' => 'active_date',
                ),
                'kbscase_name' => array(
                    'name' => 'kbscase_name',
                ),
                'exp_date' => array(
                    'name' => 'exp_date',
                ),
            ),
        ),
    ),
    'moreLessInlineFields' => array(
        'usefulness' => array(
            'name' => 'usefulness',
            'type' => 'usefulness',
            'span' => 6,
            'cell_css_class' => 'pull-right usefulness',
            'readonly' => true,
            'fields' => array(
                'useful',
                'notuseful',
            ),
        ),
    )
);
