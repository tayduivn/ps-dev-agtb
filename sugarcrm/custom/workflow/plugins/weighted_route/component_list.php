<?php


$component_list = array(


	'action' => array(
	
						'createstep1' => array(
							'directory' => 'weighted_route', 
							'meta_file' => 'action_meta_array',
							'file' => 'weighted_route',
							'class' => 'weighted_route',
							'jscript_function' => 'weighted_route_createstep1_jscript',
							),
						'createstep2' => array(
							'directory' => 'weighted_route', 
							'file' => 'weighted_route',
							'class' => 'weighted_route',
							'function' => 'weighted_route_createstep2'							
							),								
						'listview' => array(
							'directory' => 'weighted_route', 
							'file' => 'weighted_route',
							'class' => 'weighted_route',
							'function' => 'weighted_route_listview'							
							),							
						'glue' => array(
							'directory' => 'weighted_route',
							'file' => 'weighted_route',
							'class' => 'weighted_route',
							'function' => 'weighted_route_glue'
							),
	
				//end action array
				),
		'vardef_handler_hook' => array(
							'directory' => 'weighted_route', 
							'meta_file' => 'vardef_meta_arrays'
						),	
	//end component list
	);			


?>