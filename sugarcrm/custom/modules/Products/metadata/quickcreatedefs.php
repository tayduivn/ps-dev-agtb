<?php
$viewdefs ['Products'] = 
array (
  'QuickCreate' => 
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
      'javascript' => '<script type="text/javascript" src="include/jsolait/init.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/JSON.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/javascript/jsclass_base.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="include/javascript/jsclass_async.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript" src="modules/Products/EditView.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>',
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'customCode' => '<input name="name" id="name" type="text" value="{$fields.name.value}"><input name="product_template_id" id="product_template_id" type="hidden" value="{$fields.product_template_id.value}">&nbsp;<input title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" type="button" class="button" value="{$APP.LBL_SELECT_BUTTON_LABEL}" onclick=\'return get_popup_product();\'>&nbsp;<input tabindex="1" title="{$LBL_CLEAR_BUTTON_TITLE}" accessKey="{$APP.LBL_CLEAR_BUTTON_KEY}" class="button" onclick="this.form.product_template_id.value = \'\'; this.form.name.value = \'\';" type="button" value="{$APP.LBL_CLEAR_BUTTON_LABEL}">',
          ),
          1 => 'status',
        ),
        1 => 
        array (
          0 => 'account_name',
          1 => 'contact_name',
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'quantity',
            'displayParams' => 
            array (
              'size' => 5,
            ),
          ),
          1 => 'date_purchased',
        ),
        3 => 
        array (
          0 => 'serial_number',
          1 => 'date_support_starts',
        ),
        4 => 
        array (
          0 => 'asset_number',
          1 => 'date_support_expires',
        ),
      ),
      0 => 
      array (
        0 => 
        array (
          0 => 'currency_id',
          1 => '',
        ),
        1 => 
        array (
          0 => 'cost_price',
          1 => '',
        ),
        2 => 
        array (
          0 => 'list_price',
          1 => 'book_value',
        ),
        3 => 
        array (
          0 => 'discount_price',
          1 => 'book_value_date',
        ),
        4 => 
        array (
          0 => 'discount_amount',
          1 => 'discount_select',
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'type' => 'Link',
          ),
          1 => 'tax_class',
        ),
        1 => 
        array (
          0 => 'manufacturer_id',
          1 => 'weight',
        ),
        2 => 
        array (
          0 => 'mft_part_num',
          1 => 'category_id',
        ),
        3 => 
        array (
          0 => 'vendor_part_num',
          1 => 'type_id',
        ),
        4 => 
        array (
          0 => 'description',
        ),
        5 => 
        array (
          0 => 'support_name',
          1 => 'support_contact',
        ),
        6 => 
        array (
          0 => 'support_description',
          1 => 'support_term',
        ),
      ),
    ),
  ),
);
?>
