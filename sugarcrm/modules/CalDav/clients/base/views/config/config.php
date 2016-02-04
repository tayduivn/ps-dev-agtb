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

$module_name = 'CalDav';
$viewdefs[$module_name]['base']['view']['config'] = array(
    'panels' => array(
        array(
//            'columns' => 2,
            'fields' => array(
                array(
                    'name' => 'caldav_module',
                    'label' => 'LBL_CALDAV_DEFAULT_MODULE',
                    'description' => 'LBL_CALDAV_DEFAULT_MODULE_DESC',
                    'type' => 'enum',
                    'width' => 15,
                    'options' => array('' => ''),
                    'view' => 'edit',
                    'required' => true,
                ),
                array(
                    'name' => 'caldav_call_direction',
                    'type' => 'CallsDirection',
                    'label' => 'LBL_CALDAV_CALL_DIRECTION',
                    'description' => 'LBL_CALDAV_CALL_DIRECTION_DESC',
                    'view' => 'edit',
                    'options' => array('' => ''),
                    'default' => 'Outbound',
                    'enabled' => true,
                ),
                array(
                    'name' => 'caldav_interval',
                    'label' => 'LBL_CALDAV_DEFAULT_INTERVAL',
                    'description' => 'LBL_CALDAV_DEFAULT_INTERVAL_DESC',
                    'type' => 'enum',
                    'width' => 15,
                    'options' => array('' => ''),
                    'view' => 'edit',
                    'required'=>true,
                ),
            ),
        ),
    ),
);
