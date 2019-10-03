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

$vardefs = [
    'fields' => [
        'asset_number' => [
            'name' => 'asset_number',
            'vname' => 'LBL_ASSET_NUMBER',
            'type' => 'varchar',
            'len' => 50,
            'comment' => 'Asset tag number of sales item in use',
        ],
        'book_value' => [
            'name' => 'book_value',
            'vname' => 'LBL_BOOK_VALUE',
            'type' => 'currency',
            'len' => '26,6',
            'comment' => 'Book value of sales item in use',
            'related_fields' => [
                'currency_id',
                'base_rate',
            ],
        ],
        'book_value_date' => [
            'name' => 'book_value_date',
            'vname' => 'LBL_BOOK_VALUE_DATE',
            'type' => 'date',
            'comment' => 'Date of book value for sales item in use',
        ],
        'date_purchased' => [
            'name' => 'date_purchased',
            'vname' => 'LBL_DATE_PURCHASED',
            'type' => 'date',
            'comment' => 'Date sales item purchased',
        ],
        'date_support_expires' => [
            'name' => 'date_support_expires',
            'vname' => 'LBL_DATE_SUPPORT_EXPIRES',
            'type' => 'date',
            'comment' => 'Support expiration date',
        ],
        'date_support_starts' => [
            'name' => 'date_support_starts',
            'vname' => 'LBL_DATE_SUPPORT_STARTS',
            'type' => 'date',
            'comment' => 'Support start date',
        ],
        'list_price' => [
            'name' => 'list_price',
            'vname' => 'LBL_LIST_PRICE',
            'type' => 'currency',
            'len' => '26,6',
            'audited' => true,
            'comment' => 'List price of sales item',
            'related_fields' => [
                'currency_id',
                'base_rate',
            ],
        ],
        'pricing_factor' => [
            'name' => 'pricing_factor',
            'vname' => 'LBL_PRICING_FACTOR',
            'type' => 'int',
            'group' => 'pricing_formula',
            'len' => 4,
            'comment' => 'Variable pricing factor depending on pricing_formula',
        ],
        'pricing_formula' => [
            'name' => 'pricing_formula',
            'vname' => 'LBL_PRICING_FORMULA',
            'type' => 'varchar',
            'len' => 100,
            'comment' => 'Pricing formula (ex: Fixed, Markup over Cost)',
        ],
        'quantity' => [
            'name' => 'quantity',
            'vname' => 'LBL_QUANTITY',
            'type' => 'decimal',
            'len' => 12,
            'precision' => 2,
            'validation' => [
                'type' => 'range',
                'greaterthan' => -1,
            ],
            'comment' => 'Quantity in use',
            'default' => 1.0,
        ],
        'renewable' => [
            'name' => 'renewable',
            'vname' => 'LBL_RENEWABLE',
            'type' => 'bool',
            'default' => 0,
            'comment' => 'Indicates whether the sales item is renewable (e.g. a service)',
        ],
        'serial_number' => [
            'name' => 'serial_number',
            'vname' => 'LBL_SERIAL_NUMBER',
            'type' => 'varchar',
            'len' => 50,
            'comment' => 'Serial number of sales item in use',
        ],
        'support_contact' => [
            'name' => 'support_contact',
            'vname' => 'LBL_SUPPORT_CONTACT',
            'type' => 'varchar',
            'len' => 50,
            'comment' => 'Contact for support purposes',
        ],
        'support_description' => [
            'name' => 'support_description',
            'vname' => 'LBL_SUPPORT_DESCRIPTION',
            'type' => 'varchar',
            'len' => 255,
            'comment' => 'Description of sales item for support purposes',
        ],
        'support_name' => [
            'name' => 'support_name',
            'vname' => 'LBL_SUPPORT_NAME',
            'type' => 'varchar',
            'len' => 50,
            'comment' => 'Name of sales item for support purposes',
        ],
        'support_term' => [
            'name' => 'support_term',
            'vname' => 'LBL_SUPPORT_TERM',
            'type' => 'varchar',
            'len' => 100,
            'comment' => 'Term (length) of support contract',
        ],
        'vendor_part_num' => [
            'name' => 'vendor_part_num',
            'vname' => 'LBL_VENDOR_PART_NUM',
            'type' => 'varchar',
            'len' => 50,
            'comment' => 'Vendor part number',
        ],
        'website' => [
            'name' => 'website',
            'vname' => 'LBL_URL',
            'type' => 'varchar',
            'len' => 255,
            'comment' => 'Sales item URL',
        ],
        'weight' => [
            'name' => 'weight',
            'vname' => 'LBL_WEIGHT',
            'type' => 'decimal',
            'len' => '12,2',
            'precision' => 2,
            'comment' => 'Weight of the sales item',
        ],
    ],
    'uses' => [
        'currency',
    ],
];
