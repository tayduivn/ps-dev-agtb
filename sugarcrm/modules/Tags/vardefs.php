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
    'favorites' => true,
    'optimistic_locking' => false,
    'unified_search' => true,
    'full_text_search' => false,
    'unified_search_default_enabled' => true,
    'required_import_indexes' => array('idx_tag_name::name'),
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
            'studio' => false,
            'exportable' => false,
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
        'external_source',
        'assignable',
    ),
    // This can also be a string that maps to a global function. If it's an array
    // it should be static
    'load_fields' => array(
        'class' =>'TagsRelatedModulesUtilities',
        'method' => 'getRelatedFields',
    ),
    // Tags should not implement taggable, but since there is no distinction
    // between basic and default for sugar objects templates yet, we need to
    // forecefully remove the taggable implementation fields. Once there is a
    // separation of default and basic templates we can safely remove these as
    // this module will implement default instead of basic.
    'unset_fields' => array(
        'tag',
        'tag_link',
        'tags_tags',
    ),
    // These ACLs prevent regular users from taking administrative actions on
    // Tag records. This allows view, list and export only.
    'acls' => array(
        'SugarACLDeveloperOrAdmin' => array(
            'aclModule' => 'Tags',
            'allowUserRead' => true,
        ),
    ),
);
VardefManager::createVardef(
    'Tags',
    'Tag'
);
