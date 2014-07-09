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
$viewdefs['base']['view']['history-summary'] = array(
    'template' => 'history-summary',
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'preview-button',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'icon-eye-open',
                'acl_action' => 'view'
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
                    'name' => 'description',
                    'type' => 'textarea',
                    'label' => 'LBL_DESCRIPTION',
                    'enabled' => true,
                    'default' => true,
                    'css_class' => 'description_col'
                ),
                array(
                    'name' => 'to_addrs',
                    'type' => 'email',
                    'label' => 'LBL_EMAIL_TO',
                    'enabled' => true,
                    'default' => true,
                ),

                array(
                    'name' => 'from_addr',
                    'type' => 'email',
                    'label' => 'LBL_EMAIL_FROM',
                    'enabled' => true,
                    'default' => true,
                ),

                array(
                    'name' => 'date_entered',
                    'label' => 'LBL_LIST_DATE_ENTERED',
                    'type' => 'date',
                    'enabled' => true,
                    'default' => true,
                    'isSortable' => true,
                ),
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'type' => 'date',
                    'enabled' => true,
                    'default' => true,
                    'isSortable' => true,
                ),
            )
        )
    ),
    'last_state' => array(
        'id' => 'history-summary',
    ),
);
