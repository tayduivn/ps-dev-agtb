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
        // FIXME: add log file name field
    ),
    'relationships' => array(),
    'acls' => array(
        'SugarACLAdminOnly' => true,
    ),
);

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}

VardefManager::createVardef(
    'HealthCheck',
    'HealthCheck',
    array('basic')
);
