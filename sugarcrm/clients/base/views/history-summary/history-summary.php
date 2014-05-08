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
$viewdefs['base']['view']['history-summary'] = array(
    'template' => 'history-summary',
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'rowaction',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'icon-eye-open',
                'acl_action' => 'view',
                'id' => 'previewBtn'
            ),
        ),
    ),
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                    'enabled' => true,
                    'default' => true,
                    'link' => true,
                    'isSortable' => true
                ),
                array(
                    'name' => 'module',
                    'label' => 'LBL_MODULE_TYPE',
                    'enabled' => true,
                    'default' => true,
                    'isSortable' => true
                ),
                array(
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'type' => 'status',
                    'enabled' => true,
                    'default' => true
                ),
                array(
                    'name' => 'related_contact',
                    'label' => 'LBL_RELATED_CONTACT',
                    'enabled' => true,
                    'default' => true,
                    'type' => 'related-contact',
                    'link' => true
                ),
                array(
                    'name' => 'date_entered',
                    'label' => 'LBL_LIST_DATE_ENTERED',
                    'type' => 'date',
                    'enabled' => true,
                    'default' => true,
                    'isSortable' => true
                ),
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'type' => 'date',
                    'enabled' => true,
                    'default' => true,
                    'isSortable' => true
                ),
            )
        )
    ),
);
