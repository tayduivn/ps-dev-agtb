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

$viewdefs['WebSockets']['base']['view']['config-panel'] = array(
    'label' => 'LBL_WEBSOCKETS_LABEL',
    'panels' => array(
        array(
            'name' => 'panel_header1',
            'label' => 'LBL_WEBSOCKETS_CLIENT_SIDE_PANEL_LABEL',
            'fields' => array(
                array(
                    'name' => 'websockets_client_protocol',
                    'type' => 'radioenum',
                    'label' => 'LBL_WEBSOCKETS_DEFAULT_PROTOCOL_LABEL',
                    'description' => 'LBL_WEBSOCKETS_DEFAULT_CLIENT_SIDE_PROTOCOL_DESC',
                    'view' => 'edit',
                    'options' => 'http_type_protocol_options',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'websockets_client_host',
                    'label' => 'LBL_WEBSOCKETS_DEFAULT_HOST_LABEL',
                    'description' => 'LBL_WEBSOCKETS_DEFAULT_CLIENT_SIDE_HOST_DESC',
                    'default' => false,
                    'enabled' => true,
                    'required' => false,
                ),
                array(
                    'name' => 'websockets_client_port',
                    'label' => 'LBL_WEBSOCKETS_DEFAULT_PORT_LABEL',
                    'description' => 'LBL_WEBSOCKETS_DEFAULT_CLIENT_SIDE_PORT_DESC',
                    'enabled' => true,
                    'default' => false,
                    'required' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_header2',
            'label' => 'LBL_WEBSOCKETS_SERVER_SIDE_PANEL_LABEL',
            'fields' => array(
                array(
                    'name' => 'websockets_server_protocol',
                    'type' => 'radioenum',
                    'label' => 'LBL_WEBSOCKETS_DEFAULT_PROTOCOL_LABEL',
                    'description' => 'LBL_WEBSOCKETS_DEFAULT_SERVER_SIDE_PROTOCOL_DESC',
                    'view' => 'edit',
                    'options' => 'http_type_protocol_options',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'websockets_server_host',
                    'label' => 'LBL_WEBSOCKETS_DEFAULT_HOST_LABEL',
                    'description' => 'LBL_WEBSOCKETS_DEFAULT_SERVER_SIDE_HOST_DESC',
                    'default' => false,
                    'enabled' => true,
                    'required' => false,
                ),
                array(
                    'name' => 'websockets_server_port',
                    'label' => 'LBL_WEBSOCKETS_DEFAULT_PORT_LABEL',
                    'description' => 'LBL_WEBSOCKETS_DEFAULT_SERVER_SIDE_PORT_DESC',
                    'enabled' => true,
                    'default' => false,
                    'required' => true,
                ),
            ),
        ),
    ),
);
