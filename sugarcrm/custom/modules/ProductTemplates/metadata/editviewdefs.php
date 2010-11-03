<?php
$viewdefs ['ProductTemplates'] = 
array (
  'EditView' => 
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
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 82
 * Javascript function to call when editview loads to determine if percentage needs to be required
*/

      'javascript' => '
                {literal}
                <script language="javascript">
                function isPercentRequired( val ) {
                                for( var i=0; i < validate["EditView"].length; i++) {
                                        if( validate["EditView"][i][nameIndex] == "percentage_c") {
                                                if( val != "percentage" ) {
                                                        validate["EditView"][i][requiredIndex] = false;
                                                }
                                                else {
                                                        validate["EditView"][i][requiredIndex] = true;
						}
                                        }
                                        if( validate["EditView"][i][nameIndex] == "cost_price" || validate["EditView"][i][nameIndex] == "discount_price" || validate["EditView"][i][nameIndex] == "list_price" ) {
                                                if( val != "percentage" ) {
                                                        validate[ "EditView"][i][requiredIndex] = true;
                                                }
                                                else {
                                                        validate["EditView"][i][requiredIndex] = false;
                                                }
                                        }
			}
                }
                </script>
                {/literal}
',
      'useTabs' => false,
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
            'label' => 'LBL_NAME',
            'displayParams' => 
            array (
              'required' => true,
            ),
          ),
          1 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'category_name',
            'label' => 'LBL_CATEGORY_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'label' => 'LBL_URL',
          ),
          1 => 
          array (
            'name' => 'date_available',
            'label' => 'LBL_DATE_AVAILABLE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'tax_class',
            'label' => 'LBL_TAX_CLASS',
          ),
          1 => 
          array (
            'name' => 'qty_in_stock',
            'label' => 'LBL_QUANTITY',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'manufacturer_id',
            'label' => 'LBL_LIST_MANUFACTURER_ID',
          ),
          1 => 
          array (
            'name' => 'weight',
            'label' => 'LBL_WEIGHT',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'mft_part_num',
            'label' => 'LBL_MFT_PART_NUM',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'vendor_part_num',
            'label' => 'LBL_VENDOR_PART_NUM',
          ),
          1 => 
          array (
            'name' => 'type_id',
            'label' => 'LBL_LIST_TYPE_ID',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'price_format_c',
            'studio' => 'visible',
            'label' => 'LBL_PRICE_FORMAT',
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 82
 * Javascript function to call when editview loads to determine if percentage needs to be required
*/
    
	'displayParams' =>
                array(
                        'javascript' => 'onchange="isPercentRequired(this.value)"',
                ),

          ),
          1 => 
          array (
            'name' => 'percentage_c',
            'label' => 'LBL_PERCENTAGE',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'currency_id',
            'label' => 'LBL_CURRENCY',
          ),
          1 => 
          array (
            'name' => 'support_name',
            'label' => 'LBL_SUPPORT_NAME',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'cost_price',
            'label' => 'LBL_COST_PRICE',
          ),
          1 => 
          array (
            'name' => 'support_contact',
            'label' => 'LBL_SUPPORT_CONTACT',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'list_price',
            'label' => 'LBL_LIST_PRICE',
          ),
          1 => 
          array (
            'name' => 'support_description',
            'label' => 'LBL_SUPPORT_DESCRIPTION',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'discount_price',
            'label' => 'LBL_DISCOUNT_PRICE',
          ),
          1 => 
          array (
            'name' => 'support_term',
            'label' => 'LBL_SUPPORT_TERM',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'pricing_formula',
            'label' => 'LBL_PRICING_FORMULA',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
            'displayParams' => 
            array (
              'rows' => 8,
              'cols' => 60,
            ),
          ),
        ),
      ),
      'lbl_editview_panel1' => 
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
