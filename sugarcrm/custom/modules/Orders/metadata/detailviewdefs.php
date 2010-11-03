<?php
$module_name = 'Orders';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 
          array (
            'customCode' => '{if (($fields.status.value == "Queued" || $fields.status.value == "pending_salesops") && $button_flag == "true")}<input title="Complete Order" accessKey="C" type="button" class="button" onClick="document.location=\'index.php?module=Orders&action=CompleteOrder&record={$fields.id.value}\'" name="complete" value="Complete Order">{/if}',
          ),
          4 => 
          array (
            'customCode' => '{if $fields.in_netsuite_c.value == "0"}<input title="Send To NetSuite" accessKey="N" type="button" class="button" onClick="document.location=\'index.php?module=Orders&action=SendToNetSuite&record={$fields.id.value}\'" name="netsuite" value="Send To NetSuite">{/if}',
          ),
        ),
      ),
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
            'label' => 'LBL_ORDER_ID',
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
            'name' => 'username',
            'customCode' => '<a href="http://www.sugarcrm.com/crm/users/{$fields.username.value}">{$fields.username.value}</a>',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
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
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'notes',
            'studio' => 'visible',
            'label' => 'LBL_NOTES',
          ),
        ),
        5 => 
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
        6 => 
        array (
          0 => 
          array (
            'name' => 'subtotal',
            'label' => 'LBL_SUBTOTAL',
          ),
        ),
        7 => 
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
        8 => 
        array (
          0 => 
          array (
            'name' => 'tax',
            'label' => 'LBL_TAX',
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
            'name' => 'total',
            'label' => 'LBL_TOTAL',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'orders_subscriptions_name',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'black_bird_c',
            'label' => 'LBL_BLACK_BIRD',
          ),
          1 => 
          array (
            'name' => 'blue_bird_c',
            'label' => 'LBL_BLUE_BIRD',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'partner_id_c',
            'studio' => 'visible',
            'label' => 'LBL_PARTNER_ID',
          ),
          1 => 
          array (
            'name' => 'partner_contact_id_c',
            'studio' => 'visible',
            'label' => 'LBL_PARTNER_CONTACT_ID',
          ),
        ),
      ),
      'lbl_detailview_panel7' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'orders_contracts_name',
          ),
        ),
      ),
      'lbl_detailview_panel1' => 
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
      'lbl_detailview_panel8' => 
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
      'lbl_detailview_panel3' => 
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
      'lbl_detailview_panel9' => 
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
      ),
      'LBL_WORKLOAD' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'workload_c',
            'label' => 'LBL_WORKLOAD',
            'customCode' => '<pre>{$fields.workload_c.value}</pre>',
          ),
        ),
      ),
    ),
  ),
);
?>
