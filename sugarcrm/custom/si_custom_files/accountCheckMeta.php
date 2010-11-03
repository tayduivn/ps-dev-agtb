<?php

// DEE CUSTOMIZATION - ITREQUEST #3300 - IF PARTNER TYPE IS AFFILIATE THEN SUPPORT SERVICE LEVEL SHOULD BE = NO SUPPORT PROVIDED

	$validityCheckMeta = array(
		'Partner_Type_c' => array(
			'display_name' => 'Partner Account Type',
                        'Affiliate' => array(
                                'Support_Service_Level_c' => array(
                                        'display_name' => 'Support Service Level',
                                        'value_type' => 'literal_equal',
                                        'value' => 'no_support',
					'alt_field_display_name' => 'NO SUPPORT PROVIDED',
				)
			)
		),
		'account_type' => array(
                        'display_name' => 'Account Type',
                        'Past Partner' => array(
                                'Support_Service_Level_c' => array(
                                        'display_name' => 'Support Service Level',
                                        'value_type' => 'literal_equal',
                                        'value' => 'no_support',
                                        'alt_field_display_name' => 'NO SUPPORT PROVIDED',
                                )
                        ),
			'Past Customer' => array(
                                'Support_Service_Level_c' => array(
                                        'display_name' => 'Support Service Level',
                                        'value_type' => 'literal_equal',
                                        'value' => 'no_support',
                                        'alt_field_display_name' => 'NO SUPPORT PROVIDED',
                                )
                        ),

                ),
		
	);
?>
