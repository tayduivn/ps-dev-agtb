<?php


class compare_test{
	
	
	function compare_test_listview(& $opt){

		$list_data['ACTION'] = 'CustomPlugin';
		$list_data['PLUGIN_ACTION'] = 'CreateStep2';
		$list_data['PLUGIN_MODULE'] = 'compare_test';
		
		$list_data_array['list_data'] = $list_data;
		$list_data_array['action_processed'] = true;
		
		return $list_data_array;
		
		
	//end function get_listview_data	
	}		
	
	
	function compare_test_createstep1_jscript(& $opt){
		
			$jscript1 = "
					if(target_value == 'compare_test'){ \n
					//make next button visible, save button invisible \n
						this.document.getElementById('next_div').style.display= ''; \n
						this.document.getElementById('save_div').style.display= 'none'; \n
					}
			";
		
		
			$jscript2 = "if(checked_value==\"compare_test\"){ \n
				this.document.getElementById('action').value = \"CustomPlugin\"; \n
				this.document.getElementById('plugin_module').value = \"compare_test\"; \n
				this.document.getElementById('plugin_action').value = \"CreateStep2\"; \n
				return confirm_value_present('field', 'Testing custom module javascript'); \n
			}	";
		
		
		$jscript_array['jscript_part1'] = $jscript1;
		$jscript_array['jscript_part2'] = $jscript2;
		
		return $jscript_array;	
		
	//end compare_test2_createstep1_jscript
	}		
	
	function compare_test_eval_dump(& $opt){
		
		
		//Nothing for compare_test for the eval_dump
		
		
		
	//end function compare_test_eval_dump
	}

	function compare_test_glue(& $opt){

		//determine trigger position
		if($opt['trigger_position'] == "Primary"){
			$field = "target_field";
		} else {
			$field = "field";
		}			
		
		//turn the parameters into an array
		$parameters_array = explode(",", $opt['row']['parameters']);
		
		//strip out the initial white space and ending white space
		foreach($parameters_array as $key => $value){
				
			//remove out any blank values.  This is also caused from having
			// an extra zip at the end
		if(!empty($value) && $value!=""){
			$parameters_array[$key] = trim($value);
		} else {
			unset($parameters_array[$key]);

		}
		//end foreach split
		}	
		
		//write to the plugin meta_array
		$array_position_name = $opt['array_position_name'];
		
		
		$plugin_meta_data = "'".$array_position_name."' => \n\n";
		$plugin_meta_data .= "array ( \n\n";
		$plugin_meta_data .= $opt['object']->glue_object->build_trigger_array_component("compare_array", $parameters_array);
		$plugin_meta_data .= "), \n\n";
		
		//write back the plugin_meta_data
		$opt['object']->glue_object->plugin_meta_data .= $plugin_meta_data;
		
		//write to the eval to check if in array to match the plugin_meta_array
		
		$eval_array['eval'] = "isset(\$focus->".$opt['row'][$field].") && in_array(\$focus->".$opt['row'][$field].", \$plugin_meta_array[\"".$array_position_name."\"][\"compare_array\"])==true";
		$eval_array['trigger_processed'] = true;
		
		return $eval_array;
		
	//end function compare_test_glue	
	}	
	
	
	
	
	
	
	
}	

?>
