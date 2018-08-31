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

$vardefs = array(
    'fields' => array(
        'commentslog' =>  array(
            'name' => 'commentslog',
            'vname' => 'LBL_COMMENTSLOG',
            'type' => 'commentslog',
            'link' => 'commentslog_link',
            'source' => 'non-db',
            'module' => 'Commentslog',
            'relate_collection' => true,
            'rname' => 'name',
            'studio' => array(
                'listview' => false,
                'recordview' => true,
            ),
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
            ),
        ),
        'commentslog_link' => array(
            'name' => 'commentslog_link',
            'type' => 'link',
            'vname' => 'LBL_COMMENTSLOG_LINK',
            'relationship' => strtolower($module).'_commentslog',
            'source' => 'non-db',
            'exportable' => false,
            'duplicate_merge' => 'disabled',
        ),
    ),
    'relationships' => array(
        strtolower($module).'_commentslog' => array(
            'lhs_module' => $module,
            'lhs_table' => $table_name,
            'lhs_key' => 'id',
            'rhs_module' => 'Commentslog',
            'rhs_table' => 'commentslog',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'commentslog_rel',
            'join_key_lhs' => 'record_id',
            'join_key_rhs' => 'commentslog_id',
            'relationship_role_column' => 'module',
            'relationship_role_column_value' => $module,
        ),
    ),
);
