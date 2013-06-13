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
$dictionary['product_revenuelineitem'] = array(
    'table' => 'product_revenue_line_item',
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
            'name' => 'product_id',
            'type' => 'varchar',
            'len' => '36'
        ),
        array(
            'name' => 'revenuelineitem_id',
            'type' => 'varchar',
            'len' => '36'
        )
    ),
    'indices' => array(
        array(
            'name' => 'prod_rlidpk',
            'type' => 'primary',
            'fields' => array(
                'id'
            )
        ),
        array(
            'name' => 'idx_pp_prod',
            'type' => 'index',
            'fields' => array(
                'product_id'
            )
        ),
        array(
            'name' => 'idx_pp_rli',
            'type' => 'index',
            'fields' => array(
                'revenuelineitem_id'
            )
        )
    ),

    'relationships' => array(
        'product_revenuelineitem' => array(
            'lhs_module' => 'Products',
            'lhs_table' => 'products',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'product_revenue_line_items',
            'join_key_lhs' => 'product_id',
            'join_key_rhs' => 'revenuelineitem_id',
            'reverse' => '1'
        ),
        'revenuelineitem_product' => array(
            'rhs_module' => 'Products',
            'rhs_table' => 'products',
            'rhs_key' => 'id',
            'lhs_module' => 'RevenueLineItems',
            'lhs_table' => 'revenue_line_items',
            'lhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'product_revenue_line_items',
            'join_key_rhs' => 'product_id',
            'join_key_lhs' => 'revenuelineitem_id',
            'reverse' => '1'
        )
    )
);
