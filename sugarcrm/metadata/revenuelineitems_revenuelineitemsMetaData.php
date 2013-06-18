<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
//FILE SUGARCRM flav=pro ONLY
$dictionary['revenuelineitems_revenuelineitems'] = array(
    'table' => 'revenue_line_items_revenue_line_items',
    'fields' => array(
        array(
            'name' => 'id',
            'type' => 'varchar',
            'len' => '36'
        ),
        array(
            'name' => 'date_modified',
            'type' => 'datetime'
        ),
        array(
            'name' => 'deleted',
            'type' => 'bool',
            'len' => '1',
            'default' => '0',
            'required' => false
        ),
        array(
            'name' => 'parent_id',
            'type' => 'varchar',
            'len' => '36'
        ),
        array(
            'name' => 'child_id',
            'type' => 'varchar',
            'len' => '36'
        )
    ),
    'indices' => array(
        array(
            'name' => 'rli_rlipk',
            'type' => 'primary',
            'fields' => array(
                'id'
            )
        ),
        array(
            'name' => 'idx_pp_parent',
            'type' => 'index',
            'fields' => array(
                'parent_id'
            )
        ),
        array(
            'name' => 'idx_pp_child',
            'type' => 'index',
            'fields' => array(
                'child_id'
            )
        )
    ),

    'relationships' => array(
        'revenuelineitems_revenuelineitems' => array(
            'lhs_module' => 'RevenueLineItems',
            'lhs_table' => 'revenue_line_items',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'revenuelineitems_revenuelineitems',
            'join_key_lhs' => 'parent_id',
            'join_key_rhs' => 'child_id'
        )
    )
);
