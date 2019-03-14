<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$viewdefs['base']['view']['dashablerecord'] = array(
    'template' => 'record',
    'dashlets' => [
        array(
            'label' => 'LBL_DASHLET_RECORDVIEW_NAME',
            'description' => 'LBL_DASHLET_RECORDVIEW_DESCRIPTION',
            'filter' => array(
                'view' => ['record', 'Home'],
            ),
            'config' => [],
            'preview' => array(
                'module' => 'Accounts',
                'label' => 'LBL_MODULE_NAME',
            ),
        ),
    ],
    'panels' => [
        array(
            'name' => 'dashlet_settings',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => [
                array(
                    'name' => 'module',
                    'label' => 'LBL_MODULE',
                    'type' => 'enum',
                    'span' => 12,
                    'sort_alpha' => true,
                ),
            ],
        ),
    ],
);
