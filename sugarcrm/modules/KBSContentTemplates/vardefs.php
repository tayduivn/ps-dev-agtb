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

$dictionary['KBSContentTemplate'] = array(
    'table' => 'kbscontent_templates',
    'audited' => true,
    'activity_enabled' => true,
    'comment' => 'A template is used as a body for KBSContent.',
    'fields' => array(
        'body' => array(
            'name' => 'body',
            'vname' => 'LBL_TEXT_BODY',
            'type' => 'longtext',
            'comment' => 'Template body',
            'audited' => true,
        ),
    ),
    'relationships' => array(),
    'duplicate_check' => array(
        'enabled' => false,
    ),
);

VardefManager::createVardef(
    'KBSContentTemplates',
    'KBSContentTemplate',
    array(
        'basic',
        'team_security',
    )
);
$dictionary['KBSContentTemplate']['fields']['name']['audited'] = true;
