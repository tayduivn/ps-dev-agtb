<?php
//Plugin List showing all installed plugins and their info 
	 $plugin_list = 
array (
  'trigger' => 
  array (
    'createstep1' => 
    array (
      'compare_test' => 
      array (
        'directory' => 'compare_test',
        'meta_file' => 'trigger_meta_array',
        'file' => 'compare_test',
        'class' => 'compare_test',
        'jscript_function' => 'compare_test_createstep1_jscript',
      ),
    ),
    'listview' => 
    array (
      'compare_test' => 
      array (
        'directory' => 'compare_test',
        'file' => 'compare_test',
        'class' => 'compare_test',
        'function' => 'compare_test_listview',
      ),
    ),
    'eval_dump' => 
    array (
      'compare_test' => 
      array (
        'directory' => 'custom_test',
        'file' => 'copmare_test',
        'class' => 'compare_test',
        'function' => 'compare_test_eval_dump',
      ),
    ),
    'glue' => 
    array (
      'compare_test' => 
      array (
        'directory' => 'compare_test',
        'file' => 'compare_test',
        'class' => 'compare_test',
        'function' => 'compare_test_glue',
      ),
    ),
  ),
  'vardef_handler_hook' => 
  array (
    'compare_test' => 
    array (
      'directory' => 'compare_test',
      'meta_file' => 'vardef_meta_arrays',
    ),
    'dynamic_var' => 
    array (
      'directory' => 'dynamic_var',
      'meta_file' => 'vardef_meta_arrays',
    ),
    'more_action_rel' => 
    array (
      'directory' => 'more_action_rel',
      'meta_file' => 'vardef_meta_arrays',
    ),
    'weighted_route' => 
    array (
      'directory' => 'weighted_route',
      'meta_file' => 'vardef_meta_arrays',
    ),
  ),
  'action' => 
  array (
    'selector' => 
    array (
      'dynamic_var' => 
      array (
        'directory' => 'dynamic_var',
        'file' => 'dynamic_var',
        'class' => 'dynamic_var',
        'function' => 'dynamic_var_selector',
      ),
      'more_action_rel' => 
      array (
        'directory' => 'more_action_rel',
        'file' => 'more_action_rel',
        'class' => 'more_action_rel',
        'function' => 'more_action_rel_selector',
      ),
    ),
    'display_text' => 
    array (
      'dynamic_var' => 
      array (
        'directory' => 'dynamic_var',
        'file' => 'dynamic_var',
        'class' => 'dynamic_var',
        'function' => 'dynamic_var_display_text',
      ),
      'more_action_rel' => 
      array (
        'directory' => 'more_action_rel',
        'file' => 'more_action_rel',
        'class' => 'more_action_rel',
        'function' => 'more_action_rel_display_text',
      ),
    ),
    'process_action' => 
    array (
      'dynamic_var' => 
      array (
        'directory' => 'dynamic_var',
        'file' => 'dynamic_var',
        'class' => 'dynamic_var',
        'function' => 'dynamic_var_process_action',
      ),
      'more_action_rel' => 
      array (
        'directory' => 'more_action_rel',
        'file' => 'more_action_rel',
        'class' => 'more_action_rel',
        'function' => 'more_action_rel_process_action',
      ),
    ),
    'createstep1' => 
    array (
      'weighted_route' => 
      array (
        'directory' => 'weighted_route',
        'meta_file' => 'action_meta_array',
        'file' => 'weighted_route',
        'class' => 'weighted_route',
        'jscript_function' => 'weighted_route_createstep1_jscript',
      ),
    ),
    'createstep2' => 
    array (
      'weighted_route' => 
      array (
        'directory' => 'weighted_route',
        'file' => 'weighted_route',
        'class' => 'weighted_route',
        'function' => 'weighted_route_createstep2',
      ),
    ),
    'listview' => 
    array (
      'weighted_route' => 
      array (
        'directory' => 'weighted_route',
        'file' => 'weighted_route',
        'class' => 'weighted_route',
        'function' => 'weighted_route_listview',
      ),
    ),
    'glue' => 
    array (
      'weighted_route' => 
      array (
        'directory' => 'weighted_route',
        'file' => 'weighted_route',
        'class' => 'weighted_route',
        'function' => 'weighted_route_glue',
      ),
    ),
  ),
) 

?>