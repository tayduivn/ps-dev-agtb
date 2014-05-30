<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
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
