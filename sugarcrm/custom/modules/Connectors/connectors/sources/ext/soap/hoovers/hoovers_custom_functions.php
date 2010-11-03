<?php

/**
 * get_country_value
 * 
 */
function get_country_value($bean, $out_field, $value) {
	if(file_exists('include/language/en_us.lang.php')) {
	   require('include/language/en_us.lang.php');
	   if(isset($app_list_strings['countries_dom'])) {
	   	  $country = trim(strtoupper($value));
	   	  if(isset($app_list_strings['countries_dom'][$country])) {
	   	  	 return $app_list_strings['countries_dom'][$country];
	   	  }
	   }
	}
	
    switch($country) {
     	case (preg_match('/U[\.]?S[\.]?A[\.]?/', $country) || $country == 'UNITED STATES' || $country == 'AMERICA' || $country == 'NORTH AMERICA') :
     	    return "USA";
     	case ($country == "ENGLAND" || $country == "UK" || $country == "GREAT BRITAIN" || $country == "BRITAIN") :
     		return "UNITED KINGDOM";
     	default : 
     		return $value;
    }
}


/**
 * get_hoovers_finsales
 * 
 * @param $value decimal number denoting annual sales in millions of dollars
 */
function get_hoovers_finsales($bean, $out_field, $value) {
	
	$value = trim($value);
	if(empty($value) || !is_numeric($value) || $value == '0'){
			return 'Unknown';
	}
	
	$value = $value * 1000000;	//Multiply by 1 million	
	$value = intval(floor($value));
	
	switch($value) {
		case ($value < 10000000):
			return 'under 10M';
		case ($value < 25000000):
			return '10 - 25M';
		case ($value < 100000000):
			return '25 - 99M';
		case ($value < 250000000):
			return '100M - 249M';
		case ($value < 500000000):
			return '250M - 499M';
		case ($value < 1000000000):
			return '500M - 1B';
		case ($value >= 1000000000):
			return 'more than 1B';
		default:
			return 'Unknown';
	}
}

function get_hoovers_employees($bean, $out_field, $value) {
	
	$value = trim($value);
	if(empty($value) || !is_numeric($value)) {
	   return '';
	}
	
	switch($value) {
		case ($value < 100):
			return 'under 100 employees';
		case ($value < 400):
			return '100 - 399 employees';
		case ($value < 1000):
			return '400 - 999 employees';
		default:
			return 'more than 1000 employees';
	}
}

?>
