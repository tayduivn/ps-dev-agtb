<?php


	function get_createstep1_jscript(){
		
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
		
		
		$plugin_output['jscript1'] = $jscript1;
		$plugin_output['jscript2'] = $jscript2;
		
		return $plugin_output;
		
	//end function get_createstep1_jscript
	}	
	






?>