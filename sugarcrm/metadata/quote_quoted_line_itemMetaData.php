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
$dictionary['quote_quoted_line_item'] = array(
    'table' => 'quote_quoted_line_item',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'type' => 'datetime',
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'len' => '1',
            'default' => '0',
            'required' => false,
        ),
        'product_id' => array(
            'name' => 'product_id',
            'type' => 'id',
        ),
        'quote_id' => array(
            'name' => 'quote_id',
            'type' => 'id',
        ),
        'quote_index' => array(
            'name' => 'quote_index',
            'type' => 'int',
            'len' => '11',
            'default' => 0,
            'required' => false,
        ),
    ),
    'indices' => array(
        array(
            'name' => 'quote_quoted_line_itempk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_qliq_product',
            'type' => 'index',
            'fields' => array(
                'product_id',
            ),
        ),
        array(
            'name' => 'idx_qliq_quote',
            'type' => 'index',
            'fields' => array(
                'quote_id',
            ),
        ),
        array(
            'name' => 'idx_qliq_bq',
            'type' => 'alternate_key',
            'fields' => array(
                'quote_id',
                'product_id',
            ),
        ),
        array(
            'name' => 'qliq_index_idx',
            'type' => 'index',
            'fields' => array(
                'quote_index',
            ),
        ),
    ),
    'relationships' => array(
        'quote_quoted_line_item' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'Products',
            'rhs_table' => 'products',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-many',
            'join_table' => 'quote_quoted_line_item',
            'join_key_lhs' => 'quote_id',
            'join_key_rhs' => 'product_id',
            'true_relationship_type' => 'one-to-many',
        ),
    ),
);
