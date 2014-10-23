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

$viewdefs['KBSContents']['portal']['view']['record'] = array(
    'buttons' => array(
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'picture',
                    'type' => 'avatar',
                    'size' => 'large',
                    'dismiss_label' => true,
                    'readonly' => true,
                ),
                'name',
                'language' => array(
                    'name' => 'language',
                    'type' => 'enum-config',
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
                    'type' => 'html',
                    'span' => 12,
                ),
                'attachment_list' => array(
                    'name' => 'attachment_list',
                    'label' => 'LBL_ATTACHMENTS',
                    'type' => 'attachments',
                    'link' => 'attachments',
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
    ),
);
