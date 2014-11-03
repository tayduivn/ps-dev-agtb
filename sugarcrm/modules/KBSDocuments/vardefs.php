<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$dictionary['KBSDocument'] = array(
    'table' => 'kbsdocuments',
    'favorites' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'comment' => 'Knowledge Base management',
    'fields' => array(
        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'kbsdocument_status_dom',
            'reportable' => false,
        ),
    ),
);
VardefManager::createVardef(
    'KBSDocuments',
    'KBSDocument',
    array(
        'basic',
        'team_security',
        'assignable'
    )
);
