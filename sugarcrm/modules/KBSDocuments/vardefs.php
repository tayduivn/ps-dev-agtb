<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
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
            'options' => 'kbdocument_status_dom',
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
