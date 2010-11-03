<?php

	

	
		$mod_strings['LBL_WEIGHTED_ROUTE'] = 'Weighted assignment between two people';
		
	//	$process_dictionary['ActionsCreateStep1']['hide_others']['target_element']['compare_test'] = array('compare_test');
		
		$process_dictionary['ActionsCreateStep1']['elements']['weighted_route'] = 

	Array(
		'trigger_type' => 'all',
		'top' => Array(
			'type' => 'radio',
			'name' => 'action_type',
			'value' => 'weighted_route',	
			'options' => Array(
				'1' => Array('vname' => 'LBL_WEIGHTED_ROUTE'),
			//end top options
			),
		//end top
		),
		'bottom' => Array(
			'type' => 'text',
			'value' => 'weighted_route',
			'related' => '0',
			'options' => Array(
				'1' => Array('vname' => 'Weighted Routing between two people', 'text_type' => 'static'),
			//end bottom options
			),
		//end bottom
		),
//end compare_test2 array	
);




?>