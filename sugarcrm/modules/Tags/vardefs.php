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
