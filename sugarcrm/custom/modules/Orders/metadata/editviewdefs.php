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
      'javascript' => '{literal}
<script language="javascript">
function copyBilling(checked) {
	if(checked == true) {
		//disable the fields
		document.getElementById("shipping_address").disabled = true;
		document.getElementById("shipping_city").disabled = true;
		document.getElementById("shipping_state").disabled = true;
		document.getElementById("shipping_zip_code").disabled=true;
		document.getElementById("shipping_country").disabled = true;

		// copy the data
		document.getElementById("shipping_address").value = document.getElementById("billing_address").value;
		document.getElementById("shipping_city").value = document.getElementById("billing_city").value;
		document.getElementById("shipping_state").value = document.getElementById("billing_state").value;
		document.getElementById("shipping_zip_code").value = document.getElementById("billing_zip_code").value;
		document.getElementById("shipping_country").value = document.getElementById("billing_country").value;

	}
	else{
		// enable the fields
		document.getElementById("shipping_address").disabled = false;
		document.getElementById("shipping_city").disabled = false;
		document.getElementById("shipping_state").disabled = false;
		document.getElementById("shipping_zip_code").disabled=false;
		document.getElementById("shipping_country").disabled = false;
	}
}

	document.getElementById("copy_address_c").onclick = function() { copyBilling(document.getElementById("copy_address_c").checked) };
	document.getElementById("EditView").onsubmit = function() {
														is_checked = document.getElementById("copy_address_c").checked;
														if( is_checked == true ) {
															document.getElementById("shipping_address").disabled = false;
															document.getElementById("shipping_city").disabled = false;
															document.getElementById("shipping_state").disabled = false;
															document.getElementById("shipping_zip_code").disabled=false;
															document.getElementById("shipping_country").disabled = false;							
														}
														return true;
													};
	copyBilling(document.getElementById("copy_address_c").checked);
</script>
{/literal}',
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
            'name' => 'order_id',
            'comment' => 'Visible order identifier',
            'label' => 'LBL_NUMBER',
            'customCode' => '{if $fields.order_id.value != ""}{$fields.order_id.value}{else}NEWORDER{/if}',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'username',
            'label' => 'LBL_USERNAME',
          ),
          1 => 
          array (
            'name' => 'user_id',
            'label' => 'LBL_USER_ID',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'accounts_orders_name',
            'label' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
          ),
          1 => 
          array (
            'name' => 'contacts_orders_name',
            'label' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'orders_opportunities_name',
            'label' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'studio' => 'visible',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
            'studio' => 'visible',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'notes',
            'studio' => 'visible',
            'label' => 'LBL_NOTES',
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
            'name' => 'cart_action_c',
            'studio' => 'visible',
            'label' => 'LBL_CART_ACTION',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'subtotal',
            'label' => 'LBL_SUBTOTAL',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'discount',
            'label' => 'LBL_DISCOUNT',
          ),
          1 => 
          array (
            'name' => 'discountcodes_orders_name',
            'label' => 'LBL_DISCOUNTCODES_ORDERS_FROM_DISCOUNTCODES_TITLE',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'partner_margin_c',
            'label' => 'LBL_PARTNER_MARGIN',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'tax',
            'label' => 'LBL_TAX',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'total',
            'label' => 'LBL_TOTAL',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'orders_subscriptions_name',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'blue_bird_c',
            'label' => 'LBL_BLUE_BIRD',
          ),
          1 => 
          array (
            'name' => 'black_bird_c',
            'label' => 'LBL_BLACK_BIRD',
          ),
        ),
      ),
      'lbl_editview_panel6' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'orders_contracts_name',
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
      ),
      'lbl_editview_panel7' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'email',
            'label' => 'LBL_EMAIL',
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
          1 => 
          array (
            'name' => 'shipping_title',
            'label' => 'LBL_SHIPPING_TITLE',
          ),
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
            'name' => 'shipping_first_name',
            'label' => 'LBL_SHIPPING_FIRST_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'billing_last_name',
            'label' => 'LBL_BILLING_LAST_NAME',
          ),
          1 => 
          array (
            'name' => 'shipping_last_name',
            'label' => 'LBL_SHIPPING_LAST_NAME',
          ),
        ),
      ),
      'lbl_editview_panel8' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'billing_address',
            'label' => 'LBL_BILLING_ADDRESS',
          ),
          1 => 
          array (
            'name' => 'shipping_address',
            'label' => 'LBL_SHIPPING_ADDRESS',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'billing_city',
            'label' => 'LBL_BILLING_CITY',
          ),
          1 => 
          array (
            'name' => 'shipping_city',
            'label' => 'LBL_SHIPPING_CITY',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'billing_state',
            'label' => 'LBL_BILLING_STATE',
          ),
          1 => 
          array (
            'name' => 'shipping_state',
            'label' => 'LBL_SHIPPING_STATE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'billing_zip_code',
            'label' => 'LBL_BILLING_ZIP_CODE',
          ),
          1 => 
          array (
            'name' => 'shipping_zip_code',
            'label' => 'LBL_SHIPPING_ZIP_CODE',
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
            'name' => 'shipping_country',
            'label' => 'LBL_SHIPPING_COUNTRY',
          ),
        ),
        5 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'copy_address_c',
            'label' => 'LBL_COPY_ADDRESS',
          ),
        ),
      ),
    ),
  ),
);
?>
