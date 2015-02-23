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
$viewdefs['base']['view']['gsr-list'] = array(
    'template' => 'gsr-list',
    'rowactions' => array(
        'actions' => array(
            array(
                'type' => 'preview-button',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'fa-eye',
                'acl_action' => 'view'
            ),
        ),
    ),
    'fields' => array(
        'icon' => array(
            array(
            'name' => 'picture',
            'type' => 'avatar',
            'align' => 'center',
            'label' => 'LBL_MODULE_TYPE',
            'dismiss_label' => true,
            'readonly' => true,
            'enabled' => true,
            'default' => true,
            'isSortable' => true,
            'size' => 'medium',
            )
        ),
        'primary' => array(
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                    'type' => 'name',
                    'enabled' => true,
                    'default' => true,
                    'link' => true,
                    'isSortable' => true,
                    'width' => 'large',
                )
            )

        ),
        'secondary' => array(
            'fields' => array(
            )
        ),
    )
);
