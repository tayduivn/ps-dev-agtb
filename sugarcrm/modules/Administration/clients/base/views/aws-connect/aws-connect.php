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
$viewdefs['Administration']['base']['view']['aws-connect'] = [
    'template' => 'record',
    'label' => 'LBL_AWS_CONNECT_TITLE',
    'panels' => [
        [
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_1',
            'columns' => 1,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => [
                [
                    'name' => 'aws_connect_instance_name',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CONNECT_INST_NAME',
                    'placeholder' => 'LBL_AWS_CONNECT_INST_NAME',
                    'labelSpan' => 2,
                    'span' => 4,
                ],
                [
                    'name' => 'aws_connect_region',
                    'type' => 'text',
                    'label' => 'LBL_AWS_CONNECT_REGION',
                    'placeholder' => 'LBL_AWS_CONNECT_REGION',
                    'required' => true,
                    'labelSpan' => 2,
                    'span' => 4,
                ],
            ],
        ],
    ],
];
