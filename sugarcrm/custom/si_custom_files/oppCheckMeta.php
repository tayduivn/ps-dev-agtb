<?php

	
	$validityCheckMeta = array(
		'opportunity_type' => array(
			'display_name' => 'Opportunity Type',
			'Sugar Network' => array(
				/*
				DEE - ITREQUEST 6475
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'variable_equal',
					'value' => 'users',
					'alt_field_display_name' => 'Subscriptions',
				),*/
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_equal',
					'value' => 'Annual',
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_equal',
					'value' => 'New',
				),
			),
			'Support Services' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
			),
			'Plug-ins' => array(
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_equal',
					'value' => 'Annual',
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_equal',
					'value' => 'New',
				),
			),
			// NOTE: BEGIN - THE NEXT THREE ITEMS ARE IDENTICAL
			'Profesional Services' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Additional',
				),
			),
			'Pro Services - Channel' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Additional',
				),
			),
			'OS Support' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Additional',
				),
			),
			// NOTE: END - THE NEXT THREE ITEMS ARE IDENTICAL
			'OEM' => array(
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
			),
			'Partner Fees' => array(
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'Custom',
						'Remainder of Term',
					),
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Additional',
				),
				'partner_assigned_to_c' => array(
					'display_name' => 'Partner Assigned To',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
			),
			'SugarExchange' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_equal',
					'value' => '0',
				),
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_not_equal',
					'value' => 'Remainder of Term',
				),
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Additional',
				),
			),
			'Sugar Enterprise' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'0',
						'',
					),
				),
			),
			'Sugar Professional' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'0',
						'',
					),
				),
			),
			'Sugar Enterprise On-Demand' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'0',
						'',
					),
				),
			),
			'Sugar OnDemand' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'0',
						'',
					),
				),
			),
			'Sugar Cube' => array(
				'users' => array(
					'display_name' => 'Subscriptions',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'0',
						'',
					),
				),
			),
			'Training' => array(
				'additional_training_credits_c' => array(
					'display_name' => 'Learning Credits',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'0',
						'',
					),
				),
			),
		),
		'sales_stage' => array(
			'display_name' => 'Sales Stage',
			'Closed Won' => array(
				'order_number' => array(
					'display_name' => 'Order Number',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'opportunity_type' => array(
					'display_name' => 'Opportunity Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Undecided',
				),
			),
				       'Closed Lost' => array('closed_lost_reason_c' => array('display_name' => 'Closed Lost Reason',
											    'value_type' => 'literal_not_equal',
											    'value' => '',),
							      'closed_lost_reason_c' => array('display_name' => 'Closed Lost Reason',
											    'value_type' => 'literal_not_equal',
											    'value' => '-blank-',),
							      'closed_lost_description' => array('display_name' => 'Closed Lost Description',
												 'value_type' => 'literal_not_equal',
												 'value' => '',),
							      )
		),
		'Term_c' => array(
			'display_name' => 'Term',
/*<--			'Annual' => array(
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => 'Additional',
				),
			),*/
			'Remainder of Term' => array(
				'Revenue_Type_c' => array(
					'display_name' => 'Revenue Type',
					'value_type' => 'literal_not_equal',
					'value' => array(
						'New',
						'Renewal',
					),
				),
			),
		),
		'Revenue_Type_c' => array(
			'display_name' => 'Revenue Type',
			'New' => array(
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_not_equal',
					'value' => 'Remainder of Term',
				),
				'opportunity_type' => array(
					'exempt_roles' => array(
						'Sales Operations Opportunity Admin',
						'Finance',
					),
					'display_name' => 'Opportunity Type',
					'value_type' => 'literal_equal',
					'value' => array(
						'sugar_ent_converge',
						'sugar_pro_converge',
						'Support Services',
						'Partner Fees',
						'Partner Sales Training',
						'Profesional Services',
						'OEM',
						'SugarExchange',
					),
				),
			),
			'Renewal' => array(
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_not_equal',
					'value' => 'Remainder of Term',
				),
				'renewal_date_c' => array(
					'display_name' => 'Renewal Date',
					'value_type' => 'literal_not_equal',
					'value' => '',
				),
				'opportunity_type' => array(
					'exempt_roles' => array(
						'Sales Operations Opportunity Admin',
						'Finance',
					),
					'display_name' => 'Opportunity Type',
					'value_type' => 'literal_equal',
					'value' => array(
						'sugar_ent_converge',
						'sugar_pro_converge',
						'Support Services',
						'Partner Fees',
						'Partner Sales Training',
						'Profesional Services',
						'OEM',
						'SugarExchange',
					),
				),
			),
/*<--			'Additional' => array(
				'Term_c' => array(
					'display_name' => 'Term',
					'value_type' => 'literal_not_equal',
					'value' => 'Annual',
				)
			),*/
		),
	);
	
?>
