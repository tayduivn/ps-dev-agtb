<?php
// BEGIN sadek - UTILITY TO MAKE IT EASIER TO LOOKUP ARRAY ELEMENTS BASED ON A VARIABLE - BACK TO PRODUCT

function smarty_modifier_lookup($value='', $from=array()){
	$value = trim($value);
	if (array_key_exists($value, $from)) { 
		return $from[$value]; 
	} 
	return ''; 
} 
