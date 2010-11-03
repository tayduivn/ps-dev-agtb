<?php

    $validityCheckMeta = array(
		// BEGIN jostrow customization
		// See ITRequest #3256
		'billing_address_country' => array(
			'display_name' => 'Billing Address Country',
			'USA' => array(
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'AUSTRALIA' => array(
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'CANADA' => array(
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
		),
		'shipping_address_country' => array(
			'display_name' => 'Shipping Address Country',
			'USA' => array(
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'AUSTRALIA' => array(
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'CANADA' => array(
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
		),
		// END jostrow customization

		'account_type' => array(
			'display_name' => 'Account Type',
			'' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Contributor' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Analyst' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Competitor' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Customer-Express' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Customer' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Customer-Ent' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'network' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Investor' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Partner' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Partner_Type_c' => array(
					'display_name' => 'Partner Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'resell_discount' => array(
					'display_name' => 'Resell Discount',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Partner-Pro' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Partner_Type_c' => array(
					'display_name' => 'Partner Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'resell_discount' => array(
					'display_name' => 'Resell Discount',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Partner-Ent' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Partner_Type_c' => array(
					'display_name' => 'Partner Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'resell_discount' => array(
					'display_name' => 'Resell Discount',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Press' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Reseller' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Evaluation - hosted' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Customer-Other' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'SugarExchange' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'SugarExchange Partner: Premium' => array(
				'Partner_Type_c' => array(
					'display_name' => 'Partner Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'resell_discount' => array(
					'display_name' => 'Resell Discount',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'SugarExchange Partner: Standard' => array(
				'Partner_Type_c' => array(
					'display_name' => 'Partner Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'resell_discount' => array(
					'display_name' => 'Resell Discount',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Other' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Customer-OEM' => array(
				'account_type' => array(
					'display_name' => 'Account Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'industry' => array(
					'display_name' => 'Industry',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'annual_revenue' => array(
					'display_name' => 'Annual Revenue',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'employees' => array(
					'display_name' => 'Employees',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'deployment_type_c' => array(
					'display_name' => 'Deployment Type',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'Support_Service_Level_c' => array(
					'display_name' => 'Support Service Level',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'Customer-Pro-Webex' => array(
				'billing_address_street' => array(
					'display_name' => 'Billing Address Street',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_city' => array(
					'display_name' => 'Billing Address City',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'billing_address_state' => array(
					'display_name' => 'Billing Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'billing_address_postalcode ' => array(
					'display_name' => 'Billing Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'billing_address_country' => array(
					'display_name' => 'Billing Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
/*
				'shipping_address_state' => array(
					'display_name' => 'Shipping Address State',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
*/
				'shipping_address_postalcode' => array(
					'display_name' => 'Shipping Address Zip',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'shipping_address_country' => array(
					'display_name' => 'Shipping Address Country',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
		),
	);
