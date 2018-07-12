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

$dictionary['Worklog'] = array(
    'table' => 'worklog',
    'audited' => false,
    'comment' => 'Worklog of each records',
    'duplicate_merge' => false,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'fields' => array(
        'entry' => array(
            'name' => 'entry',
            'vname' => 'LBL_ENTRY',
            'type' => 'text',
        ),
    ),
    'ignore_templates' => array(
        'worklog',
    ),
    'uses' => array(
        'basic',
    ),
    'load_fields' => array(
        'class' =>'WorklogRelatedModulesUtilities',
        'method' => 'getRelatedFields',
    ),
);

VardefManager::createVardef("Worklog", "Worklog");
