<?php
/************************************************************
 * Normalize Phone numbers
 * 
 * Author: Felix Nilam
 * Date: 17/08/2007
 ************************************************************/

if(!function_exists('str_split')){
	function str_split($text, $split = 1)
	{
        if (!is_string($text)) return false;
        if (!is_numeric($split) && $split < 1) return false;
        $len = strlen($text);
        $array = array();
        $s = 0;
        $e=$split;
        while ($s <$len)
        {
            $e=($e <$len)?$e:$len;
            $array[] = substr($text, $s,$e);
            $s = $s+$e;
        }
        return $array;
	}
}

// Params: number 	the phone number being normalized
// Params: user 	specify the current_user to overwrite system wide dial settings
// Returns: False if number is invalid or the normalized phone number 
function normalizePhone($number, $user = null){
	if(empty($number)){
		return false;
	}

	require('fonality/include/normalizePhone/default_dial_code.php');
	
	$country_code = $default_dial_code['country_code'];
	$area_code = $default_dial_code['area_code'];
	$international_code = $default_dial_code['international_code'];
	
	// overwrite settings with user specific
	if(!empty($user)){
		if($user->overwrite_dial_settings == '1'){
			$country_code = $user->dial_country_code;
			$area_code = $user->dial_area_code;
			$international_code = $user->dial_international_code;
		}
	}

	$normalized = 0;
    $internal = 0;
	
	// If number starts with +, leave it
	// BUG: number was not trimmed before comparison
	$number = trim($number);
	
	// handle internal extension
	if(substr($number, 0, 1) == "x" || substr($number, 0, 3) == "ext"){
		$phone = preg_replace('/[^0-9]/','',$number);
		// prepend x to the number to mark this as an internal extension
		$phone = "x".$phone;
		$normalized = 1;
		$internal = 1;
	}
        
	// remove any trailing extension if this is not an internal extension
	if(!$internal){
		$numbers = str_split($number);
		$extension_pos = -1;
		foreach($numbers as $key => $val){
			//if(preg_match('/[^0-9\+\(\)\- .]/', $val)){
			if(preg_match('/[xXeE]/', $val)){
				$extension_pos = $key;
				break;
			}
		}

		if($extension_pos > 0){
			$number = substr($number, 0, -1 * (strlen($number) - $extension_pos));
		}
	}
    
	if(substr($number, 0, 1) == "+"){
		$normalized = 1;
		$phone = preg_replace('/[^0-9]/','',trim($number));
		// put back the +
		$phone = "+".$phone;
	} else {
		// Trim the number
		$phone = preg_replace('/[^0-9]/','',trim($number));
 		if(empty($phone)){
 			return false;
 		}
	}
	
	// If number starts with international code
	// replace it with +
	// If it starts with 00, do nothing
	$itl_pattern = '/^'.$international_code.'/';
	if(preg_match($itl_pattern, $phone)){
		$phone = preg_replace($itl_pattern, "+", $phone);
		$normalized = 1;
	} else if(preg_match('/^000$/', $phone)){
		$normalized = 1;
	} else if(preg_match('/^911$/', $phone)){
		$normalized = 1;
	}
	if(!preg_match('/^1/', $country_code)){
		if(preg_match('/^1/', $phone)){
			$normalized = 1;
		}
	}
	
	if(!$normalized){
		// If it starts with an area code (look for the first digit of the default area code),
		// leave it, other wise prepend the area code
		if(!empty($area_code)){
			$first_digit = substr($area_code, 0, 1);
			$pattern = '/^'.$first_digit.'/';
	
			if(!preg_match($pattern, $phone)){
				// if area code starts with 0,
				// leave out the 0 (case for AU)
				if(preg_match('/^0/', $area_code)){
					$area_code = substr($area_code, 1);
				}
				$phone = $area_code . $phone;
			} else {
				// if area code starts with 0,
				// leave out the 0 (case for AU)
				if(preg_match('/^0[1-9]+/', $phone)){
					$phone = substr($phone, 1);
				}
			}
		}
		
		// prepend the country code
		$country_pattern = '/^'.$country_code.'/';
		if(!preg_match($country_pattern, $phone)){
			$phone = "+". $country_code . $phone;
		} else {
			$phone = "+". $phone;
		}
	}
		
	return $phone;
}

// Strip out international and area code
function strip_intl_area_code($phone, $user = null){
	require('fonality/include/normalizePhone/default_dial_code.php');
	
	$international_code = $default_dial_code['international_code'];
	$country_code = $default_dial_code['country_code'];
	$area_code = $default_dial_code['area_code'];

	// overwrite settings with user specific
	if(!empty($user)){
		if($user->overwrite_dial_settings == '1'){
			$country_code = $user->dial_country_code;
			$area_code = $user->dial_area_code;
			$international_code = $user->dial_international_code;
		}
	}

	$patterns[0] = '/^'.$international_code.$country_code.'/';
	$patterns[1] = '/^\+'.$country_code.'/';
	
	// if area code is specified and starts with 0, put back the 0
	if(preg_match('/^0/', $area_code)){
		$replacements[0] = '0';
		$replacements[1] = '0';
	} else {
		$replacements[0] = '';
		$replacements[1] = '';
	}
	
	return preg_replace($patterns, $replacements, $phone);
}

// format the number for displaying purposes
// used by Call Assistant and CDR Import
function uae_format_number($phone, $pattern_override = null){
	require('fonality/include/normalizePhone/default_dial_code.php');
	if(!isset($pattern_override)){
		require('fonality/include/normalizePhone/uae_format_number.php');
	} else {
		$uae_phone_pattern = $pattern_override;
	}
	
	// normalize it
	$nphone = normalizePhone($phone);
	// strip out international and country code
	$nphone = strip_intl_area_code($nphone);
	
	if(empty($nphone)){
		return '';
	}

	// apply pattern
	// e.g. xxx-xxx-xxx, (xx) xxxx xxxx, [xx]-xxxx-xxxx
	$pattern_array = str_split($uae_phone_pattern);
	
	$result = '';
	if(!empty($pattern_array)){
		$count = 0;
		foreach($pattern_array as $ptn){
			if($ptn != 'x'){
				$result .= $ptn;
			} else {
				$result .= substr($nphone, $count, 1);
				$count++;
			}
		}
		if($count < strlen($nphone)){
			$result .= substr($nphone, $count);
		}
	} else {
		$result = $nphone;
	}

	return $result;	
}
?>
