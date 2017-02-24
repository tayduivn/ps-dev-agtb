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
$dictionary['quote_product_bundle_note'] = array(
    'table' => 'quote_product_bundle_note',
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
        'note_id' => array(
            'name' => 'note_id',
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
            'name' => 'quote_product_bundle_notepk',
            'type' => 'primary',
            'fields' => array(
                'id',
            ),
        ),
        array(
            'name' => 'idx_quote_pbn',
            'type' => 'index',
            'fields' => array(
                'note_id',
            ),
        ),
        array(
            'name' => 'idx_pbn_quote',
            'type' => 'index',
            'fields' => array(
                'quote_id',
            ),
        ),
        array(
            'name' => 'idx_qpbn_bq',
            'type' => 'alternate_key',
            'fields' => array(
                'quote_id',
                'note_id',
            ),
        ),
        array(
            'name' => 'qpbn_index_idx',
            'type' => 'index',
            'fields' => array(
                'quote_index',
            ),
        ),
    ),
    'relationships' => array(
        'quote_product_bundle_note' => array(
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'ProductBundleNotes',
            'rhs_table' => 'product_bundle_note',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-many',
            'join_table' => 'quote_product_bundle_note',
            'join_key_lhs' => 'quote_id',
            'join_key_rhs' => 'note_id',
            'true_relationship_type' => 'one-to-many',
        ),
    ),
);
