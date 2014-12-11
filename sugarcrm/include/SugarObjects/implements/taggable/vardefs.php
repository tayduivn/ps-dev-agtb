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

/**
 *
 * Tagging support - only supported for sidecar enabled modules
 * @var array
 */
$vardefs = array(
    'fields' => array(
        'tags' => array(
            'name' => 'tags',
            'vname' => 'LBL_TAGS_LINK',
            'type' => 'link',
            'relationship' => strtolower($module).'_tags',
            'source' => 'non-db',
            'comment' => '',
        ),
        'tag' => array(
            'name' => 'tag',
            'vname' => 'LBL_TAGS',
            'type' => 'tag',
            'dbType' => 'text',
            'studio' => array(
                'mobile' => false,
                'portal' => false,
            ),
            'sortable' => false,
        ),
        'tag_lower' => array(
            'name' => 'tag_lower',
            'vname' => 'LBL_TAGS_LOWER',
            'type' => 'text',
            'studio' => false,
            'reportable' => false,
            'importable' => false,
            'exportable' => false,
            'sortable' => false,
            'hideacl' => true,
        ),
    ),
    'relationships' => array(
        strtolower($module).'_tags' => array(
            'lhs_module' => $module,
            'lhs_table' => strtolower($module),
            'lhs_key' => 'id',
            'rhs_module' => 'Tags',
            'rhs_table' => 'tags',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'tag_bean_rel',
            'join_key_lhs' => 'bean_id',
            'join_key_rhs' => 'tag_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => $module,
        ),
    ),
    'indices' => array(
    ),
);
