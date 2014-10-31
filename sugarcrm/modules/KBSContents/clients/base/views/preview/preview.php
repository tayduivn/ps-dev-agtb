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

$viewdefs['KBSContents']['base']['view']['preview'] = array(
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
                        'kbsdocument_id',
                        'kbsarticle_id',
                    ),
                ),
                array(
                    'label' => 'LBL_STATUS',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'status',
                    'type' => 'status',
                ),
                array(
                    'name' => 'language',
                    'label' => 'LBL_LANG',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                    'type' => 'enum-config',
                    'key' => 'languages',
                ),
                array(
                    'name' => 'attachment_list',
                    'label' => 'LBL_ATTACHMENTS',
                    'type' => 'attachments',
                    'link' => 'notes',
                    'module' => 'Notes',
                    'modulefield' => 'filename',
                ),
                array(
                    'label' => 'LBL_TAGS',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'tag',
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'label' => 'LBL_SHOW_MORE',
            'hide' => true,
            'fields' => array(
                'revision',
                'created_by_name',
                'date_entered',
                'approved',
                'date_modified',
                'team_name',
                'exp_date',
                'active_date',
            ),
        ),
    ),
);
