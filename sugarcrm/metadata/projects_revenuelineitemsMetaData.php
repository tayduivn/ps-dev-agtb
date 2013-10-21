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
// adding project-to-products relationship
$dictionary['projects_revenuelineitems'] = array(
    'table' => 'projects_revenue_line_items',
    'fields' => array(
        array(
            'name' => 'id',
            'type' => 'varchar',
            'len' => '36'
        ),
        array(
            'name' => 'rli_id',
            'type' => 'varchar',
            'len' => '36'
        ),
        array(
            'name' => 'project_id',
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
    ),
    'indices' => array(
        array(
            'name' => 'projects_rli_pk',
            'type' => 'primary',
            'fields' => array(
                'id'
            )
        ),
        array(
            'name' => 'idx_proj_rli_project',
            'type' => 'index',
            'fields' => array(
                'project_id'
            )
        ),
        array(
            'name' => 'idx_proj_rli_product',
            'type' => 'index',
            'fields' => array(
                'rli_id'
            )
        ),
        array(
            'name' => 'projects_rli_alt',
            'type' => 'alternate_key',
            'fields' => array(
                'project_id',
                'rli_id'
            )
        ),
    ),
    'relationships' => array(
        'projects_revenuelineitems' => array(
            'lhs_module' => 'Project',
            'lhs_table' => 'project',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'projects_products',
            'join_key_lhs' => 'project_id',
            'join_key_rhs' => 'rli_id',
        ),
    ),
);
