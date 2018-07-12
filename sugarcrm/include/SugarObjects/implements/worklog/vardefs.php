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
        'worklog' =>  array(
            'name' => 'worklog',
            'vname' => 'LBL_WORKLOG',
            'type' => 'worklog',
            'link' => 'worklog_link',
            'source' => 'non-db',
            'module' => 'Worklog',
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
        'worklog_link' => array(
            'name' => 'worklog_link',
            'type' => 'link',
            'vname' => 'LBL_WORKLOG_LINK',
            'relationship' => strtolower($module).'_worklog',
            'source' => 'non-db',
            'exportable' => false,
            'duplicate_merge' => 'disabled',
        ),
    ),
    'relationships' => array(
        strtolower($module).'_worklog' => array(
            'lhs_module' => $module,
            'lhs_table' => $table_name,
            'lhs_key' => 'id',
            'rhs_module' => 'Worklog',
            'rhs_table' => 'worklog',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'worklog_index',
            'join_key_lhs' => 'record_id',
            'join_key_rhs' => 'worklog_id',
            'relationship_role_column' => 'module',
            'relationship_role_column_value' => $module,
        ),
    ),
);
