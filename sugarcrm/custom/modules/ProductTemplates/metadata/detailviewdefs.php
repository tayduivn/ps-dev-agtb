<?php
$viewdefs ['ProductTemplates'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 'name',
          1 => 'status',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'label' => 'LBL_URL',
            'type' => 'link',
          ),
          1 => 'date_available',
        ),
        2 => 
        array (
          0 => 'tax_class',
          1 => 
          array (
            'name' => 'qty_in_stock',
            'label' => 'LBL_QUANTITY',
          ),
        ),
        3 => 
        array (
          0 => 'manufacturer_id',
          1 => 'weight',
        ),
        4 => 
        array (
          0 => 'mft_part_num',
          1 => 
          array (
            'name' => 'category_name',
            'type' => 'varchar',
            'label' => 'LBL_CATEGORY',
          ),
        ),
        5 => 
        array (
          0 => 'vendor_part_num',
          1 => 
          array (
            'name' => 'type_id',
            'type' => 'varchar',
            'label' => 'LBL_TYPE',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'price_format_c',
            'studio' => 'visible',
            'label' => 'LBL_PRICE_FORMAT',
          ),
          1 => 
          array (
            'name' => 'percentage_c',
            'label' => 'LBL_PERCENTAGE',
          ),
        ),
        7 => 
        array (
          0 => 'currency_id',
          1 => 'support_name',
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'cost_price',
            'customCode' => '{$fields.currency_symbol.value}{$fields.cost_price.value}&nbsp;',
          ),
          1 => 'support_contact',
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'list_price',
            'customCode' => '{$fields.currency_symbol.value}{$fields.list_price.value}&nbsp;',
          ),
          1 => 'support_description',
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'discount_price',
            'customCode' => '{$fields.currency_symbol.value}{$fields.discount_price.value}&nbsp;',
          ),
          1 => 'support_term',
        ),
        11 => 
        array (
          0 => 'pricing_formula',
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'displayParams' => 
            array (
              'nl2br' => true,
            ),
          ),
        ),
      ),
      'lbl_detailview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'available_for_sale_c',
            'label' => 'LBL_AVAILABLE_FOR_SALE',
          ),
          1 => '',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'accepted_payment_methods_c',
            'studio' => 'visible',
            'label' => 'LBL_ACCEPTED_PAYMENT_METHODS',
          ),
          1 => 
          array (
            'name' => 'default_order_status_c',
            'studio' => 'visible',
            'label' => 'LBL_DEFAULT_ORDER_STATUS',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'minimum_purchase_amount_c',
            'label' => 'LBL_MINIMUM_PURCHASE_AMOUNT',
          ),
          1 => 
          array (
            'name' => 'maximum_purchase_amount_c',
            'label' => 'LBL_MAXIMUM_PURCHASE_AMOUNT',
          ),
        ),
      ),
    ),
  ),
);
?>
