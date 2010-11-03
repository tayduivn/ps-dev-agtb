<?php

function get_jigsaw_revenue($bean, $out_field, $value) {

	$value = trim($value);

    if(empty($value) || !is_numeric($value)) {
	   return '';	
    }
	
	switch($value) {
		case ($value < 10000000):
			return '10 - 25M';
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
		default:
			return 'more than 1B';
	}
}

function get_jigsaw_employeeCount($bean, $out_field, $value) {
	
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