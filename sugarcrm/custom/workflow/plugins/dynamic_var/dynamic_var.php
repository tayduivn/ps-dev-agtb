<?php


class dynamic_var{
	
	/*
	
	Bug #9655 - there is a bug when you select and then want to reclick to open the selector.php , the requests need to override
	what was saved in the action_object if they are available.
	
	Bug #9656 - action type line 126 in original  selector.php - issue is with using a # character in text.  This causes
	a request var issue, not able to complete that var or complete variables after that in the URL string
	add some sort of safety check for # type characters?
	
	
	*/
	
	//watch 5911 bug to make sure that stuff is addressed

	//5911  - make list for max
	//if you do regular after you were dynamic or extra rel, will it clear out the exts as aprorpriate?
	
	//{DEFERRED} Test and build hook replacement mechanism
	//{DEFERRED} Figure out how to add checkboxes
	
	function dynamic_var_display_text(& $opt){
		
			return "Dynamic Variable Data";
		
	//end function action_display_text
	}	
	
	

	function dynamic_var_selector(& $opt){
		
			if(!empty($opt['action_type'])){
				
				if($opt['action_type']=='new' || $opt['action_type']=='new_rel'){

						$href = "index.php?module=WorkFlowActionShells&action=CustomPlugin";
						$href .= "&target_state=dynamic_var";
						$href .= "&plugin_module=dynamic_var&plugin_action=Selector";
						$href .= "&html=Selector&form=EditView&form_submit=false&query=true&to_pdf=true";
						$href .= "&workflow_id=".$opt['workflow_object']->id."&field_num=".$opt['field_num']."";
						$href .= "&target_module=".$opt['target_module']."&target_field=".$opt['action_object']->field."";
						$href .= "&action_id=".$opt['action_object']->id."&value=".$opt['action_object']->value."&set_type=".$opt['action_object']->set_type."";
						$href .= "&ext1=".$opt['action_object']->ext1."&ext2=".$opt['action_object']->ext2."&ext3=".$opt['action_object']->ext3."&adv_value=".$opt['action_object']->value."&action_type=".$opt['action_type']."";
										
					//if the ext1 value is present and equal to 'dynamic var', then re-direct to proper selector page
					if(!empty($opt['action_object']->ext1) && $opt['action_object']->ext1=='dynamic_var' && (empty($opt['target_state']) || $opt['target_state']=='dynamic_var')){
				
						//redirect to dynamic var selector page
						header("Location: ".$href."");
							
					
					} else {	
				
				

					
						echo "&nbsp;&nbsp;<a href='".$href."'>[Dynamic Variable Mode]</a>";				
					
					}
							
				//
				} else {
					
					//do not show the link
					
				//end if the action type is new or new_rel
				}
			
			//end if the action type is present
			}
				
	//end function dynamic_var_selector
	}	
	
	
	function dynamic_var_process_action(& $opt){
		
		global $app_list_strings;

		
		//target
		//trigger
		//related
		
		//determine what the field type is
		$all_fields_array = $opt['target_module']->getFieldDefinitions();
		$target_field_array = $all_fields_array[$opt['field']];
		$field_type = get_field_type($target_field_array);

		//based on the field type determine how to populate the target field
		if($field_type == 'enum'){
			
			if($opt['meta_array']['ext3']=='triggered'){
				
				
				$target_value = $opt['trigger_module']->$opt['meta_array']['value'];
				
			} else {
				//must be related
				
				$target_value = $opt['related_module']->$opt['meta_array']['value'];
								
				
				
			//end if else triggered or related	
			}	
			
			//make sure this value is a valid value for the ENUM
			echo 'target value'.$target_value.'optfield:'.$opt['field'].'<BR>';
			//TODO
			
			if(!empty($app_list_strings[$target_field_array['options']][$target_value])){
				
				//ok values do match up
				return $target_value;
				
			} else {
				$target_value = $app_list_strings[$target_field_array['options']][0];
				return $target_value;	
			}		
			
			
			return $target_value;
				
		//end is enum
		} else {	
		//is not enum
		

			//parse out the value and replace accordingly
			
			return $this->parse_dyn_var($opt['meta_array']['value'], $opt['trigger_module'], $opt['related_module']);
	
		//end if else enum or not
		}	
		
	//end function action_display_text
	}		
	
	
	
function parse_dyn_var($target_body, & $trigger_module, & $related_module){
	
	preg_match_all("/(({::)[^>]*?)(.*?)((::})[^>]*?)/", $target_body, $matches, PREG_SET_ORDER);

	//echo 'target body'.$target_body;
	
	foreach ($matches as $val) {
   		$matched_component = $val[0];
   		if(!empty($val[3])){
   		
   		$matched_component_core = $val[3];
   		
   		$split_array = preg_split('{::}', $matched_component_core);
   		
   	//		print_r($split_array);
   			
   			
   			//type triggered or related  - $split_array[0];
   			//field - $split_array[1];
   			
   			
   			$split_processed=false;
   				
   			if($split_array[0]=='triggered'){
   			
   				//replace matched component with value
   				
   				if(!empty($split_array[1])){
   					//if this replacement value is an enum, then make sure to use the value not key
   					$replacement_value = $this->check_enum_value($split_array[1], $trigger_module);
   				
   					// $replacement_value = $trigger_module->$split_array[1];
   					$split_processed=true;
   				}
   					
   				
   			}

   			if($split_array[0]=='related'){
   				
   				//replace matched component with value
   				
   				if(!empty($split_array[1])){
   					//if this replacement value is an enum, then make sure to use the value not key
   					$replacement_value = $this->check_enum_value($split_array[1], $related_module);
   				
   					//$replacement_value = $related_module->$split_array[1];
   					$split_processed=true;
   				}
   				
   			//end if else triggered or related	
   			}		
   			
   			if($split_processed==true){
   				$target_body = str_replace($matched_component, $replacement_value, $target_body);
   			} else {
   				//do not process, because something in the split was malformed
   			}	
   			
   			
      	//end if val 3 is empty
		}			
   			
	//end loop through components
	}

   		return $target_body;

//end function parse_dyn_var
}		
	
	function check_enum_value($field, $target_module){
		
		global $app_list_strings;
		
		$all_fields_array = $target_module->getFieldDefinitions();
		$target_field_array = $all_fields_array[$field];
		$field_type = get_field_type($target_field_array);
		
		//echo 'field'.$field.'field type'.$field_type.'<BR>';
		
		if($field_type=='enum'){
			
			//echo 'this is enum options are'.$target_field_array['options'].'<BR>';
			return $app_list_strings[$target_field_array['options']][$target_module->$field];
			
		
		//end if enum		
		} else {
			
		
			return $target_module->$field;	
		}		
		
	//end function check_enum_value
	}	
	
	
}	

?>