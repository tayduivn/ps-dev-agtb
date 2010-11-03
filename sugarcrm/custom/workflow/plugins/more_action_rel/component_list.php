<?php


$component_list = array(


	'action' => array(
	
						'selector' => array(
							'directory' => 'more_action_rel', 
							'file' => 'more_action_rel',
							'class' => 'more_action_rel',
							'function' => 'more_action_rel_selector',
							),														
						'display_text' => array(
							'directory' => 'more_action_rel',
							'file' => 'more_action_rel',
							'class' => 'more_action_rel',
							'function' => 'more_action_rel_display_text'
							),
						'process_action' => array(
							'directory' => 'more_action_rel',
							'file' => 'more_action_rel',
							'class' => 'more_action_rel',
							'function' => 'more_action_rel_process_action'
							),	
				//end action array
				),
		'vardef_handler_hook' => array(
							'directory' => 'more_action_rel', 
							'meta_file' => 'vardef_meta_arrays'
						),					
	//end component list
	);			


?>