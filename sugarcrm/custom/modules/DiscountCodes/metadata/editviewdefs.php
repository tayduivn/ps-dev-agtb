<?php
$module_name = 'DiscountCodes';
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
            'name' => 'discount_code',
            'label' => 'LBL_DISCOUNT_CODE',
          ),
          1 => 
          array (
            'name' => 'status',
            'studio' => 'visible',
            'label' => 'LBL_STATUS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'discount',
            'label' => 'LBL_DISCOUNT',
          ),
          1 => 
          array (
            'name' => 'discount_type',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_TYPE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'code_type',
            'studio' => 'visible',
            'label' => 'LBL_CODE_TYPE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'approval_type_c',
            'studio' => 'visible',
            'label' => 'LBL_APPROVAL_TYPE',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'number_of_allowed_uses',
            'label' => 'LBL_NUMBER_OF_ALLOWED_USES',
          ),
          1 => 
          array (
            'name' => 'number_of_uses',
            'customCode' => '{if $fields.number_of_uses.value != ""}{$fields.number_of_uses.value}{else}0{/if}',
            'label' => 'LBL_NUMBER_OF_USES',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'expires_on_new_c',
            'label' => 'LBL_EXPIRES_ON_NEW',
          ),
        ),
      ),
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'applies_when_c',
            'studio' => 'visible',
            'label' => 'LBL_APPLIES_WHEN',
          ),
          1 => 
          array (
            'name' => 'minimum_price',
            'label' => 'LBL_MINIMUM_PRICE',
          ),
        ),
        1 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_product_templ_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN_PRODUCT_TEMPL',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'discount_when_product_cat_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNT_WHEN_PRODUCT_CAT',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'discountcodetype_c',
            'studio' => 'visible',
            'label' => 'LBL_DISCOUNTCODETYPE',
          ),
          1 => 
          array (
            'name' => 'product',
            'studio' => 'visible',
            'label' => 'LBL_PRODUCT',
          ),
        ),
        4 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'product_category_c',
            'studio' => 'visible',
            'label' => 'LBL_PRODUCT_CATEGORY',
          ),
        ),
      ),
    ),
  ),
);
?>
