<?php


$component_list = array(


	'trigger' => array(
	
						'createstep1' => array(
							'directory' => 'compare_test', 
							'meta_file' => 'trigger_meta_array',
							'file' => 'compare_test',
							'class' => 'compare_test',
							'jscript_function' => 'compare_test_createstep1_jscript',
							),
						'listview' => array(
							'directory' => 'compare_test', 
							'file' => 'compare_test',
							'class' => 'compare_test',
							'function' => 'compare_test_listview'							
							),							
						'eval_dump' => array(
							'directory' => 'custom_test',
							'file' => 'copmare_test',
							'class' => 'compare_test',
							'function' => 'compare_test_eval_dump'
							),
						'glue' => array(
							'directory' => 'compare_test',
							'file' => 'compare_test',
							'class' => 'compare_test',
							'function' => 'compare_test_glue'
							),
	
				//end trigger array
				),
		'vardef_handler_hook' => array(
							'directory' => 'compare_test', 
							'meta_file' => 'vardef_meta_arrays'
						),	
	//end component list
	);			


?>