<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
********************************************************************************/

// Needed by VarDef manager when running the load_fields directive
SugarAutoLoader::load('modules/Tags/TagsRelatedModulesUtilities.php');

$dictionary['Tag'] = array(
    'comment' => 'Tagging module',
    'table' => 'tags',
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'optimistic_locking' => false,
    'unified_search' => true,
    'full_text_search' => false,
    'unified_search_default_enabled' => true,
    'fields' => array(
        'name_lower' => array(
            'name' => 'name_lower',
            'vname' => 'LBL_NAME_LOWER',
            'type' => 'varchar',
            'len' => 255,
            'unified_search' => true,
            'full_text_search' => array('enabled' => true, 'boost' => 3),
            'required' => true,
            'reportable' => false,
        ),
    ),
    'relationships' => array(),
    'indices' => array(
        'name' => array(
            'name' => 'idx_tag_name',
            'type' => 'index',
            'fields' => array('name'),
        ),
        'name_lower' => array(
            'name' => 'idx_tag_name_lower',
            'type' => 'index',
            'fields' => array('name_lower'),
        ),
    ),
    'uses' => array(
        'basic',
        'external_source'
    ),
    // This can also be a string that maps to a global function. If it's an array
    // it should be static
    'load_fields' => array(
        'class' =>'TagsRelatedModulesUtilities', 
        'method' => 'getRelatedFields',
    ),
    // get rid of favorites field as this is set through basic template
    // regardless if the favorites flag is set to false
    'unset_fields' => array(
        'user_favorites',
    ),
);

VardefManager::createVardef(
    'Tags',
    'Tag'
);
