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

$dictionary['HealthCheck'] = array(
    'table' => 'healthcheck',
    'fields' => array(
        'logfile' => array(
            'name' => 'logfile',
            'vname' => 'LBL_LOGFILE',
            'type' => 'varchar',
            'len' => 255,
        ),
        'bucket' => array(
            'name' => 'bucket',
            'vname' => 'LBL_BUCKET',
            'type' => 'varchar',
            'len' => 1,
        ),
        'flag' => array(
            'name' => 'flag',
            'vname' => 'LBL_FLAG',
            'type' => 'int',
            'len' => 1,
        ),
        'logmeta' => array(
            'name' => 'logmeta',
            'vname' => 'LBL_LOGMETA',
            'type' => 'text',
        ),
        'error' => array(
            'name' => 'error',
            'vname' => 'LBL_ERROR',
            'type' => 'varchar',
            'len' => 255,
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => false,
    'uses' => array(
        'default',
    ),
    'acls' => array(
        'SugarACLAdminOnly' => true,
    ),
);

VardefManager::createVardef('HealthCheck', 'HealthCheck');
