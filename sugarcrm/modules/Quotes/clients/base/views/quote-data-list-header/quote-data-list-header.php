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
$viewdefs['Quotes']['base']['view']['quote-data-list-header'] = array(
    'selection' => array(
        'type' => 'multi',
        'actions' => array(
            array(
                'name' => 'group_button',
                'type' => 'button',
                'label' => 'LBL_CREATE_GROUP_BUTTON_LABEL',
                'tooltip' => 'LBL_CREATE_GROUP_BUTTON_TOOLTIP',
                'acl_action' => 'edit',
            ),
            array(
                'name' => 'massdelete_button',
                'type' => 'button',
                'label' => 'LBL_DELETE',
                'tooltip' => 'LBL_DELETE',
                'acl_action' => 'delete',
            ),
        ),
    ),
    'panels' => array(
        array(
            'name' => 'quote_data_list',
            'label' => 'LBL_QUOTE_DATA_LIST',
            'fields' => array(
                array(
                    'name' => 'quantity',
                    'label' => 'LBL_QUANTITY',
                    'widthClass' => 'cell-xsmall',
                    'type' => 'float',
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_ITEM_NAME',
                    'widthClass' => 'cell-medium',
                    'type' => 'name',
                    'link' => true,
                ),
                array(
                    'name' => 'mft_part_num',
                    'label' => 'LBL_MFT_PART_NUM',
                    'type' => 'text',
                ),
                array(
                    'name' => 'discount_price',
                    'label' => 'LBL_DISCOUNT_PRICE',
                    'type' => 'currency',
                ),
                array(
                    'name' => 'discount_amount',
                    'label' => 'LBL_DISCOUNT_AMOUNT',
                    'type' => 'currency',
                ),
                array(
                    'name' => 'tax',
                    'label' => 'LBL_TAX',
                    'type' => 'currency',
                    'widthClass' => 'cell-medium',
                ),
                array(
                    'name' => 'subtotal',
                    'label' => 'LBL_LINE_ITEM_TOTAL',
                    'type' => 'currency',
                    'widthClass' => 'cell-medium',
                ),
            ),
        ),
    ),
);
