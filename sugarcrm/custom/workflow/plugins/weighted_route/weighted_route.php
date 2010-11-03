<?php


class weighted_route{
	
	
	function weighted_route_createstep2(& $opt){

		//$list_data_array['temp_module'] = get_module_info($opt['workflow_object']->base_module);
		//$list_data_array['meta_filter'] ="action_filter";	
		$list_data_array['action_processed'] = true;
		
		$sub_array['ACTION_DISPLAY_TEXT'] = 'existing field plus 10';
		$sub_array['FIELD_NAME'] = $opt['action_shell']->rel_module;
		
		
		$list_data_array['results']['RESULT_ARRAY'][] = $sub_array;
		
		return $list_data_array;
		
		
	//end function get_listview_data	
	}	
	
	function weighted_route_createstep1_jscript(& $opt){
	
			$jscript1 = "
				action_array.push('weighted_route'); \n ";
			
			$jscript2 = "
				if(checked_value==\"weighted_route\"){
					this.document.getElementById('action').value = \"CustomPlugin\"; \n
					this.document.getElementById('plugin_module').value = \"weighted_route\"; \n
					this.document.getElementById('plugin_action').value = \"CreateStep2\"; \n		
					return true;
				}
			
			";
		
		
		$jscript_array['jscript_part1'] = $jscript1;
		$jscript_array['jscript_part2'] = $jscript2;
		
		return $jscript_array;	
		
	//end compare_test2_createstep1_jscript
	}	
	
	
	function weighted_route_listview(& $opt){

		$list_data['HREF_EDIT'] = "'javascript:get_popup(\"".$opt->parent_id."\",\"".$opt->id."\",\"CustomPlugin\",\"CreateStep2\",\"weighted_route\",\"400\",\"500\")'";
		
		
		$list_data_array['list_data'] = $list_data;
		$list_data_array['action_processed'] = true;
		
		return $list_data_array;
		
		
	//end function get_listview_data	
	}		
	
	
	function weighted_route_eval_dump(& $opt){
		
		
		//Nothing for compare_test2 for the eval_dump
		
		
		
	//end function compare_test_eval_dump
	}

	function weighted_route_glue(& $opt){
		
		//write to the plugin meta_array
		$array_position_name = $opt['array_position_name'];
		
		$parameters = unserialize(base64_decode($opt['row']['parameters']));
		
		$plugin_meta_data = "'".$array_position_name."' => \n\n";
		$plugin_meta_data .= "array ( \n\n";
		$plugin_meta_data .= $opt['object']->glue_object->build_trigger_array_component("calculation_array", $parameters);
		$plugin_meta_data .= "), \n\n";
		
		//write back the plugin_meta_data
		$opt['object']->glue_object->plugin_meta_data .= $plugin_meta_data;
		
		//write to the eval to check if in array to match the plugin_meta_array
		
		$eval_array['action_string'] = "\t process_workflow_action_calculations(\$focus, \$plugin_meta_array['".$array_position_name."']); \n ";
					
		$eval_array['action_processed'] = true;
		return $eval_array;
		
	//end function compare_test_glue	
	}	
	
	
	
	
	
	
	
}	

?>