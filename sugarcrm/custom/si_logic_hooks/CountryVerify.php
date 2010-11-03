<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
// $Id: CountryVerify.php,v 1.1 2007/05/25 00:01:10 sadek Exp $

class CountryVerify {
	
	function LogInvalidCountry(& $focus, $event, $arguments){
		$app_list_strings = return_app_list_strings_language('en_us');
		if($event=="before_save"){
			require("custom/modules/Administration/CountryAbbreviationMap.php");
			if(isset($focus->primary_address_country) && !empty($focus->primary_address_country) && !in_array($focus->primary_address_country, $app_list_strings['countries_dom'])){
				$upper_pad = strtoupper($focus->primary_address_country);
				if(in_array($upper_pad, $app_list_strings['countries_dom'])){
					$focus->primary_address_country = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->primary_address_country = strtoupper($abbreviation_map[$upper_pad]);
				}
			}

			if(isset($focus->alt_address_country) && !empty($focus->alt_address_country) && !in_array($focus->alt_address_country, $app_list_strings['countries_dom'])){
				$upper_pad = strtoupper($focus->alt_address_country);
				if(in_array($upper_pad, $app_list_strings['countries_dom'])){
					$focus->alt_address_country = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->alt_address_country = strtoupper($abbreviation_map[$upper_pad]);
				}
			}

			if(isset($focus->address_country) && !empty($focus->address_country) && !in_array($focus->address_country, $app_list_strings['countries_dom'])){
				$upper_pad = strtoupper($focus->address_country);
				if(in_array($upper_pad, $app_list_strings['countries_dom'])){
					$focus->address_country = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->address_country = strtoupper($abbreviation_map[$upper_pad]);
				}
			}
			
			if(isset($focus->shipping_address_country) && !empty($focus->shipping_address_country) && !in_array($focus->shipping_address_country, $app_list_strings['countries_dom'])){
				$upper_pad = strtoupper($focus->shipping_address_country);
				if(in_array($upper_pad, $app_list_strings['countries_dom'])){
					$focus->shipping_address_country = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->shipping_address_country = strtoupper($abbreviation_map[$upper_pad]);
				}
			}
			
			if(isset($focus->billing_address_country) && !empty($focus->billing_address_country) && !in_array($focus->billing_address_country, $app_list_strings['countries_dom'])){
				$upper_pad = strtoupper($focus->billing_address_country);
				if(in_array($upper_pad, $app_list_strings['countries_dom'])){
					$focus->billing_address_country = $upper_pad;
				}
				else if(array_key_exists($upper_pad, $abbreviation_map)){
					$focus->billing_address_country = strtoupper($abbreviation_map[$upper_pad]);
				}
			}
			
			//end if logic hook is beforesave
		}	
			
			
		if($event=="after_save"){
			if(isset($focus->primary_address_country) && !empty($focus->primary_address_country) && !in_array($focus->primary_address_country, $app_list_strings['countries_dom'])){
				$string = "\nInvalid Country - primary_address_country -> value = {$focus->primary_address_country}\n".
					  "REQUEST data - ".var_export($_REQUEST, true)."\n";
				$GLOBALS['log']->fatal($string);
			}

			if(isset($focus->alt_address_country) && !empty($focus->alt_address_country) && !in_array($focus->alt_address_country, $app_list_strings['countries_dom'])){
				$string = "\nInvalid Country - alt_address_country -> value = {$focus->alt_address_country}\n".
					  "REQUEST data - ".var_export($_REQUEST, true)."\n";
				$GLOBALS['log']->fatal($string);
			}

			if(isset($focus->address_country) && !empty($focus->address_country) && !in_array($focus->address_country, $app_list_strings['countries_dom'])){
				$string = "\nInvalid Country - address_country -> value = {$focus->address_country}\n".
					  "REQUEST data - ".var_export($_REQUEST, true)."\n";
				$GLOBALS['log']->fatal($string);
			}
			
			if(isset($focus->shipping_address_country) && !empty($focus->shipping_address_country) && !in_array($focus->shipping_address_country, $app_list_strings['countries_dom'])){
				$string = "\nInvalid Country - shipping_address_country -> value = {$focus->shipping_address_country}\n".
					  "REQUEST data - ".var_export($_REQUEST, true)."\n";
				$GLOBALS['log']->fatal($string);
			}
			
			if(isset($focus->billing_address_country) && !empty($focus->billing_address_country) && !in_array($focus->billing_address_country, $app_list_strings['countries_dom'])){
				$string = "\nInvalid Country - billing_address_country -> value = {$focus->billing_address_country}\n".
					  "REQUEST data - ".var_export($_REQUEST, true)."\n";
				$GLOBALS['log']->fatal($string);
			}
			
			//end if event is after_save
		}	
		
	//end function LogInvalidCountry
	}

//end class CountryVerify
}


?>
