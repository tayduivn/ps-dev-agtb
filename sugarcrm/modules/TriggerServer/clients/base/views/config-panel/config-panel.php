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

$viewdefs['TriggerServer']['base']['view']['config-panel'] = array(
    'label' => 'LBL_TRIGGER_SERVER_LABEL',
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'triggerserver_protocol',
                    'type' => 'radioenum',
                    'label' => 'LBL_TRIGGER_SERVER_DEFAULT_PROTOCOL_LABEL',
                    'description' => 'LBL_TRIGGER_SERVER_DEFAULT_PROTOCOL_DESC',
                    'view' => 'edit',
                    'options' => 'http_type_protocol_options',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'triggerserver_host',
                    'label' => 'LBL_TRIGGER_SERVER_DEFAULT_HOST_LABEL',
                    'description' => 'LBL_TRIGGER_SERVER_DEFAULT_HOST_DESC',
                    'default' => false,
                    'enabled' => true,
                    'required' => false,
                ),
                array(
                    'name' => 'triggerserver_port',
                    'label' => 'LBL_TRIGGER_SERVER_DEFAULT_PORT_LABEL',
                    'description' => 'LBL_TRIGGER_SERVER_DEFAULT_PORT_DESC',
                    'enabled' => true,
                    'default' => false,
                    'required' => true,
                ),
            ),
        ),
    ),
);
