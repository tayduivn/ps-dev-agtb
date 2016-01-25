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

$dictionary['Addressee'] = array(
    'table' => 'addressees',
    'audited' => true,
    'activity_enabled' => false,
    'duplicate_merge' => true,
    'fields' => array(
        'meetings' => array(
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'meetings_addressees',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS',
        ),
        'calls' => array(
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'calls_addressees',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS',
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
    'full_text_search' => true,
);

VardefManager::createVardef('Addressees', 'Addressee', array('basic', 'team_security', 'assignable', 'taggable', 'person'));
