<?php
$module_name = 'Orders';
$viewdefs [$module_name] = 
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
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'studio' => 'visible',
            'label' => 'LBL_STATUS',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'notes',
            'studio' => 'visible',
            'label' => 'LBL_NOTES',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'subtotal',
            'label' => 'LBL_SUBTOTAL',
          ),
          1 => 
          array (
            'name' => 'tax',
            'label' => 'LBL_TAX',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'discount_code',
            'label' => 'LBL_DISCOUNT_CODE',
          ),
          1 => 
          array (
            'name' => 'discount',
            'label' => 'LBL_DISCOUNT',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'payment_method',
            'studio' => 'visible',
            'label' => 'LBL_PAYMENT_METHOD',
          ),
          1 => 
          array (
            'name' => 'total',
            'label' => 'LBL_TOTAL',
          ),
        ),
      ),
      'lbl_editview_panel4' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
          ),
          1 => '',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'first_name',
            'label' => 'LBL_FIRST_NAME',
          ),
          1 => 
          array (
            'name' => 'last_name',
            'label' => 'LBL_LAST_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'company_name',
            'label' => 'LBL_COMPANY_NAME',
          ),
          1 => '',
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'phone',
            'label' => 'LBL_PHONE',
          ),
          1 => 
          array (
            'name' => 'fax',
            'label' => 'LBL_FAX',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'email',
            'label' => 'LBL_EMAIL',
          ),
          1 => '',
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'billing_address_city',
            'label' => 'LBL_BILLING_ADDRESS_CITY',
          ),
          1 => 
          array (
            'name' => 'billing_address_state',
            'label' => 'LBL_BILLING_ADDRESS_STATE',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'billing_address_country',
            'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
          ),
          1 => 
          array (
            'name' => 'shipping_address_postalcode',
            'label' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
          ),
        ),
      ),
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'billing_title',
            'label' => 'LBL_BILLING_TITLE',
          ),
          1 => '',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'billing_first_name',
            'label' => 'LBL_BILLING_FIRST_NAME',
          ),
          1 => 
          array (
            'name' => 'billing_last_name',
            'label' => 'LBL_BILLING_LAST_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'billing_address',
            'label' => 'LBL_BILLING_ADDRESS',
          ),
          1 => 
          array (
            'name' => 'billing_city',
            'label' => 'LBL_BILLING_CITY',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'billing_county',
            'label' => 'LBL_BILLING_COUNTY',
          ),
          1 => 
          array (
            'name' => 'billing_state',
            'label' => 'LBL_BILLING_STATE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'billing_country',
            'label' => 'LBL_BILLING_COUNTRY',
          ),
          1 => 
          array (
            'name' => 'billing_zip_code',
            'label' => 'LBL_BILLING_ZIP_CODE',
          ),
        ),
      ),
      'lbl_editview_panel2' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'shipping_title',
            'label' => 'LBL_SHIPPING_TITLE',
          ),
          1 => '',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'shipping_first_name',
            'label' => 'LBL_SHIPPING_FIRST_NAME',
          ),
          1 => 
          array (
            'name' => 'shipping_last_name',
            'label' => 'LBL_SHIPPING_LAST_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'shipping_address',
            'label' => 'LBL_SHIPPING_ADDRESS',
          ),
          1 => 
          array (
            'name' => 'shipping_city',
            'label' => 'LBL_SHIPPING_CITY',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'shipping_county',
            'label' => 'LBL_SHIPPING_COUNTY',
          ),
          1 => 
          array (
            'name' => 'shipping_state',
            'label' => 'LBL_SHIPPING_STATE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'shipping_country',
            'label' => 'LBL_SHIPPING_COUNTRY',
          ),
          1 => 
          array (
            'name' => 'shipping_zip_code',
            'label' => 'LBL_SHIPPING_ZIP_CODE',
          ),
        ),
      ),
    ),
  ),
);
?>
