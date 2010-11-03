<?php


$component_list = array(


	'action' => array(
	
						'selector' => array(
							'directory' => 'dynamic_var', 
							'file' => 'dynamic_var',
							'class' => 'dynamic_var',
							'function' => 'dynamic_var_selector',
							),														
						'display_text' => array(
							'directory' => 'dynamic_var',
							'file' => 'dynamic_var',
							'class' => 'dynamic_var',
							'function' => 'dynamic_var_display_text'
							),
						'process_action' => array(
							'directory' => 'dynamic_var',
							'file' => 'dynamic_var',
							'class' => 'dynamic_var',
							'function' => 'dynamic_var_process_action'
							),	
				//end action array
				),
		'vardef_handler_hook' => array(
							'directory' => 'dynamic_var', 
							'meta_file' => 'vardef_meta_arrays'
						),					
	//end component list
	);			


?>