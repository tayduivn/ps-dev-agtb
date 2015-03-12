<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['KBContents']['portal']['view']['preview'] = array(
    'panels' => array(
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                    'related_fields' => array(
                        'kbdocument_id',
                        'kbarticle_id',
                    ),
                ),
                'kbdocument_body' => array(
                    'name' => 'kbdocument_body',
                    'type' => 'html',
                    'span' => 12,
                ),
                array(
                    'name' => 'attachment_list',
                    'label' => 'LBL_ATTACHMENTS',
                    'type' => 'attachments',
                    'link' => 'attachments',
                    'module' => 'Notes',
                    'modulefield' => 'filename',
                ),
                'category_name' => array(
                    'name' => 'category_name',
                    'label' => 'LBL_CATEGORY_NAME',
                ),
                'language' => array(
                    'name' => 'language',
                    'type' => 'enum-config',
                    'key' => 'languages',
                ),
                'date_entered' => array(
                    'name' => 'date_entered',
                ),
                'active_date' => array(
                    'name' => 'active_date',
                ),
            ),
        ),
    ),
);
