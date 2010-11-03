<?php


class more_action_rel{
	
	/*
	
	Bug #9655 - there is a bug when you select and then want to reclick to open the selector.php , the requests need to override
	what was saved in the action_object if they are available.
	
	Bug #9656 - action type line 126 in original  selector.php - issue is with using a # character in text.  This causes
	a request var issue, not able to complete that var or complete variables after that in the URL string
	add some sort of safety check for # type characters?
	
	
	*/
	
	//watch 5911 bug to make sure that stuff is addressed

	//{DEFERRED} Test and build hook replacement mechanism
	//{DEFERRED} Figure out how to add checkboxes
	
	function more_action_rel_display_text(& $opt){
		
			return "Extra Relationship Data";
		
	//end function action_display_text
	}	
	
	

	function more_action_rel_selector(& $opt){
		
		global $current_language;
		
			if(!empty($opt['action_type'])){
		
				$temp_module = get_module_info($_REQUEST['target_module']);
				$temp_module_strings = return_module_language($current_language, $temp_module->module_dir);
				$all_fields_array = $temp_module->getFieldDefinitions();
				$target_field_array = $all_fields_array[$opt['action_object']->field];
				
				$field_type = get_field_type($target_field_array);	
				
				if($field_type=='link'){

						$href = "index.php?module=WorkFlowActionShells&action=CustomPlugin";
						$href .= "&target_state=more_action_rel";
						$href .= "&plugin_module=more_action_rel&plugin_action=Selector";
						$href .= "&html=Selector&form=EditView&form_submit=false&query=true&to_pdf=true";
						$href .= "&workflow_id=".$opt['workflow_object']->id."&field_num=".$opt['field_num']."";
						$href .= "&target_module=".$opt['target_module']."&target_field=".$opt['action_object']->field."";
						$href .= "&action_id=".$opt['action_object']->id."&value=".$opt['action_object']->value."&set_type=".$opt['action_object']->set_type."";
						$href .= "&ext1=".$opt['action_object']->ext1."&ext2=".$opt['action_object']->ext2."&ext3=".$opt['action_object']->ext3."&adv_value=".$opt['action_object']->value."&action_type=".$opt['action_type']."";

						//redirect to dynamic var selector page
						header("Location: ".$href."");
							
				//
				} else {
					
					//do not show the link
					
				//end if the actual type is link
				}
			
			//end if the action type is present
			}
				
	//end function more_action_rel_selector
	}	
	
	
	function more_action_rel_process_action(& $opt){
		
		/* 	-Extra options stored in value for self referencing relationships or member-of scenarios
			-four scenarios when the trigger mod and the target mod are the same module
			-All are only available on new or new_rel actions only
			-if not member-of or self referencing then just one option, which is scenario 1.
		
		scenario #1: Apply triggered record's relationship id
		new_module->relationship_field_id = trigger_module->relationship_field_id
		
		scenario #2: Apply triggered record's id
		new_module->relationship_field_id = trigger_module->id
		
		scenario #3: Use Scenario #1 if available, else use Scenario #2
		if(!empty(trigger_module->relationship_field_id)){
			
			new_module->relationship_field_id = trigger_module->relationship_field_id
		
		} else {
		
			new_module->relationship_field_id = trigger_module->id
		
		}
		
		scenario #4 Apply new record's id to the triggered record's relationship id regardless of rather the
		triggered record's relationship id is present or not
		
		trigger_module->relationship_field_id = new_module->id

		scenario #5 Apply new record's id to the triggered record's relationship id only if the 
		triggered record's relationship id is not present
		
		if(trigger_module->relationship_field_id is empty){
		trigger_module->relationship_field_id = new_module->id	
		}	
		*/
		
		global $app_list_strings;

		
		//target
		//trigger
		//related
		
		//determine what the field type is
		$all_fields_array = $opt['target_module']->getFieldDefinitions();
		$target_field_array = $all_fields_array[$opt['field']];
		$field_type = get_field_type($target_field_array);
		
		//ignore in case some how the field type is not Link
		if($field_type=='link'){
		
			if($opt['meta_array']['value']!=''){
		
			//use the appropriate value from the triggered record to set for the right field here
			
				//get the field name for the relationship
				$rel_handler = & $opt['trigger_module']->call_relationship_handler("module_dir", true);
				$target_rel_name = $rel_handler->traverse_rel_meta('not used', $opt['trigger_module'], $target_field_array['relationship']);
				

				//currently performs scenario 1 by default
				//scenario #1: Apply triggered record's relationship id
				//new_module->relationship_field_id = trigger_module->relationship_field_id
				if($opt['meta_array']['value']=='scenario_1'){
				
					if(!empty($opt['trigger_module']->$target_rel_name)){
						//set the value
					//	echo 'test'.$target_rel_name.'<BR>';
						$opt['target_module']->$target_rel_name = $opt['trigger_module']->$target_rel_name;
						//echo 'ok!';
					}
				//end if scenario 1	
				}
				
				
				
				
				
				//scenario #2: Apply triggered record's id
				//new_module->relationship_field_id = trigger_module->id				
				if($opt['meta_array']['value']=='scenario_2'){

					$opt['target_module']->$target_rel_name = $opt['trigger_module']->id;

				//end if scenario 2
				}	
				
				
				//scenario #3: Use Scenario #1 if available, else use Scenario #2
				//if(!empty(trigger_module->relationship_field_id)){	
				//	new_module->relationship_field_id = trigger_module->relationship_field_id
				//} else {
				//	new_module->relationship_field_id = trigger_module->id
				//}				
				if($opt['meta_array']['value']=='scenario_3'){
					//echo 'test2'.$target_field_array['relationship'].'<BR>';
					//echo 'test'.$target_rel_name.'<BR>';
					if(!empty($opt['trigger_module']->$target_rel_name)){
						$opt['target_module']->$target_rel_name = $opt['trigger_module']->$target_rel_name;
					} else {					
						$opt['target_module']->$target_rel_name = $opt['trigger_module']->id;
					}
					
				//end if scenario 3
				}					
				
		
				//scenario #4 Apply new record's id to the triggered record's relationship id regardless of rather the
				//triggered record's relationship id is present or not
				//trigger_module->relationship_field_id = new_module->id	
				if($opt['meta_array']['value']=='scenario_4'){
					
					//NOT AVAILABLE AT THIS TIME
					
					
				//end if scenario 4
				}					
				
			

				//scenario #5 Apply new record's id to the triggered record's relationship id only if the 
				//triggered record's relationship id is not present
		
				//if(trigger_module->relationship_field_id is empty){
				//trigger_module->relationship_field_id = new_module->id	
				//}			
				
				if($opt['meta_array']['value']=='scenario_5'){
					
					//NOT AVAILABLE AT THIS TIME
					
					
				//end if scenario 5
				}				
					
				
						
			//end must process
			}	
		
		
		
		//end if field type is link
		}
		
		
		return '';

	//end function action_display_text
	}		
	
	
	
	
}	

?>
