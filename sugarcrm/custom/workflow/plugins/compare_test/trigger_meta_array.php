<?php

	

	
		$mod_strings['LBL_CUSTOM_TEST'] = 'Trigger field value residing in list of values';
		
		$process_dictionary['TriggersCreateStep1']['hide_others']['target_element']['compare_test'] = array('compare_test');
		
		$process_dictionary['TriggersCreateStep1']['elements']['compare_test'] = 

	Array(
		'trigger_type' => 'all',
		'filter_type' => Array('Normal'=>'Normal'),
		'top' => Array(
			'type' => 'radio',
			'name' => 'type',
			'value' => 'compare_test',	
			'options' => Array(
				'1' => Array('vname' => 'LBL_CUSTOM_TEST'),
			//end top options
			),
		//end top
		),
		'bottom' => Array(
			'type' => 'text',
			'value' => 'compare_test',
			'options' => Array(
				'1' => Array('vname' => 'When', 'text_type' => 'static'),
				'2' => Array(
						'vname' => 'LBL_FIELD', 
						'default' => 'on',
						'text_type' => 'dynamic',
						'type'=> 'href',
						'value' => 'field',
						'value_type' => 'normal_field',
						//'jscript_function' => 'get_single_selector',
						//'jscript_content' => array('self', 'field'),
						'jscript_function' => 'get_single_selector2',
						'jscript_content' => array('self', 'field', 'field' ,'compare_test_filter')					
						),
				'3' => Array('vname' => 'value is found in list', 'text_type' => 'static')		
			//end bottom options
			),
		//end bottom
		),
//end compare_test array	
);	




?>