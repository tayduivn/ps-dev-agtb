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

$dictionary['purchasedlineitems_documents'] = [
    'true_relationship_type' => 'many-to-many',
    'relationships' => [
        'purchasedlineitems_documents' => [
            'lhs_module' => 'PurchasedLineItems',
            'lhs_table' => 'purchased_line_items',
            'lhs_key' => 'id',
            'rhs_module' => 'Documents',
            'rhs_table' => 'documents',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'purchasedlineitems_documents',
            'join_key_lhs' => 'purchasedlineitem_id',
            'join_key_rhs' => 'document_id',
        ],
    ],
    'table' => 'purchasedlineitems_documents',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'type' => 'datetime',
        ],
        'deleted' => [
            'name' => 'deleted',
            'type' => 'bool',
            'len' => '1',
            'default' => '0',
            'required' => true,
        ],
        'purchasedlineitem_id' => [
            'name' => 'purchasedlineitem_id',
            'type' => 'id',
        ],
        'document_id' => [
            'name' => 'document_id',
            'type' => 'id',
        ],
    ],
];
