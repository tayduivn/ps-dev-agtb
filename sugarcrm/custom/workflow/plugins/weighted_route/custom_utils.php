<?php


	function process_workflow_action_calculations(& $focus, $meta_array){
		
			$rand_assignment_number = mt_rand(0, 100);
	

			if($rand_assignment_number <= $meta_array['calculation_array']['user_1_weight']){
				
				//route to user 1
				$focus->assigned_user_id = $meta_array['calculation_array']['user_1'];
				
			} else {
				
				//route to user 2
				$focus->assigned_user_id = $meta_array['calculation_array']['user_2'];
				
			}				

		
		
	//end function process_workflow_action_calculations
	}	