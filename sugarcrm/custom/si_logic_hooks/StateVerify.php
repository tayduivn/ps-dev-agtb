<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class StateVerify {
	
	function AdjustInvalidState(&$focus, $event, $arguments){
		if($event=="before_save"){
			require("custom/si_custom_files/meta/StateAbbreviationMap.php");
			if(isset($focus->primary_address_state) && !empty($focus->primary_address_state) && !in_array($focus->primary_address_state, $abbreviation_map)){
				$upper_pad = strtoupper($focus->primary_address_state);
				if(in_array($upper_pad, $abbreviation_map)){
					$focus->primary_address_state = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->primary_address_state = strtoupper($abbreviation_map[$upper_pad]);
				}
			}

			if(isset($focus->alt_address_state) && !empty($focus->alt_address_state) && !in_array($focus->alt_address_state, $abbreviation_map)){
				$upper_pad = strtoupper($focus->alt_address_state);
				if(in_array($upper_pad, $abbreviation_map)){
					$focus->alt_address_state = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->alt_address_state = strtoupper($abbreviation_map[$upper_pad]);
				}
			}

			if(isset($focus->address_state) && !empty($focus->address_state) && !in_array($focus->address_state, $abbreviation_map)){
				$upper_pad = strtoupper($focus->address_state);
				if(in_array($upper_pad, $abbreviation_map)){
					$focus->address_state = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->address_state = strtoupper($abbreviation_map[$upper_pad]);
				}
			}
			
			if(isset($focus->shipping_address_state) && !empty($focus->shipping_address_state) && !in_array($focus->shipping_address_state, $abbreviation_map)){
				$upper_pad = strtoupper($focus->shipping_address_state);
				if(in_array($upper_pad, $abbreviation_map)){
					$focus->shipping_address_state = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->shipping_address_state = strtoupper($abbreviation_map[$upper_pad]);
				}
			}
			
			if(isset($focus->billing_address_state) && !empty($focus->billing_address_state) && !in_array($focus->billing_address_state, $abbreviation_map)){
				$upper_pad = strtoupper($focus->billing_address_state);
				if(in_array($upper_pad, $abbreviation_map)){
					$focus->billing_address_state = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->billing_address_state = strtoupper($abbreviation_map[$upper_pad]);
				}
			}
			//end if logic hook is beforesave
		}	
	}

//end class StateVerify
}


?>
