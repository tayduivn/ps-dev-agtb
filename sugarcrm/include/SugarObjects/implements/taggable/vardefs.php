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

$vardefs = array(
    'fields' => array(
        'tag' => array(
            'name' => 'tag',
            'vname' =>'LBL_TAGS',
            'type' => 'tag',
            'link' => 'tag_link',
            'source' => 'non-db',
            'studio' => array(
                'mobile' => false,
                'portal' => false,
            ),
            'massupdate' => true,
            'exportable' => true,
        ),
        'tag_link' => array(
            'name' => 'tag_link',
            'type' => 'link',
            'vname' => 'LBL_TAGS_LINK',
            'relationship' => strtolower($module).'_tags',
            'source' => 'non-db',
            'exportable' => false,
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
);
