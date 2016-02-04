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

$dictionary['NotificationCenterSubscription'] = array(
    'table' => 'notification_subscription',
    'audited' => false,
    'fields' => array(
        'type' => array(
            'name' => 'type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => 'emitter_types',
            'help' => 'Type of emitter - application, bean, module',
            'required' => false,
            'massupdate' => true,
            'comments' => '',
            'importable' => false,
            'audited' => 0,
            'reportable' => 1,
        ),
        'user_id' => array(
            'name' => 'user_id',
            'vname' => 'LBL_USER_ID',
            'type' => 'id',
            'reportable' => false,
            'isnull' => true,
            'required' => false,
            'audited' => true,
            'duplicate_on_record_copy' => 'always',
            'comment' => 'User ID or null if global',
            'duplicate_merge' => 'disabled',
            'mandatory_fetch' => true,
            'massupdate' => false,
        ),
        'emitter_module_name' => array(
            'name' => 'emitter_module_name',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
            'comment' => 'Name of module from emitter',
        ),
        'event_name' => array(
            'name' => 'event_name',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
            'comment' => 'Name of event from the emitter',
        ),
        'filter_name' => array(
            'name' => 'filter_name',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
            'comment' => 'Name of subscription filter (AssignedToMe, Team, Application, can be added more)',
        ),
        'carrier_name' => array(
            'name' => 'carrier_name',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
            'comment' => 'Name of carrier',
        ),
        'carrier_option' => array(
            'name' => 'carrier_option',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
            'comment' => 'Recipient value for carrier',
        ),
    ),
    'indices' => array(
        'idx_user_id' => array(
            'name' => 'idx_notification_subscription_user_id',
            'type' => 'index',
            'fields' => array(
                'user_id',
            )
        ),
    ),
    'relationships' => array(),
    'optimistic_lock' => true,
    'ignore_templates' => array(
        'taggable',
    ),
);

require_once 'include/SugarObjects/VardefManager.php';
VardefManager::createVardef('NotificationCenterSubscriptions', 'NotificationCenterSubscription', array('basic'));
