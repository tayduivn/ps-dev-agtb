<?php

$target_departments = array(
    'Sales - Channels',
    'Sales - Corporate',
    'Sales - Enterprise',
    'Sales - Inside',
    'Sales - Inside - CA Group',
    'Sales - Inside - EMEA',
);  

function getTargetFor($department, $category, $year, $quarter){
	global $projection_array;
	if(isset($projection_array[$year][$quarter][$category][$department])){
		return $projection_array[$year][$quarter][$category][$department];
	}

	$storage_dir = 'custom/si_custom_files/meta';
	$storage_file = 'financeProjections.php';
	if(!file_exists($storage_dir)){
		mkdir($storage_dir);
	}
	if(!file_exists("$storage_dir/$storage_file")){
		$projection_array[$year][$quarter][$category][$department] = 0;
		write_array_to_file('projection_array', $projection_array, "$storage_dir/$storage_file");
		chmod("$storage_dir/$storage_file", 0700);
		return 0;
	}
	else{
		require("$storage_dir/$storage_file");
		if(!isset($projection_array[$year][$quarter][$category][$department])){
			$projection_array[$year][$quarter][$category][$department] = 0;
			write_array_to_file('projection_array', $projection_array, "$storage_dir/$storage_file");
			return 0;
		}
		else{
			return $projection_array[$year][$quarter][$category][$department];
		}
	}
}

function setTargetFor($department, $category, $value, $year, $quarter){
	$storage_dir = 'custom/si_custom_files/meta';
	$storage_file = 'financeProjections.php';
	global $projection_array;
	if(!empty($projection_array)){
		$projection_array[$year][$quarter][$category][$department] = $value;
		write_array_to_file('projection_array', $projection_array, "$storage_dir/$storage_file");
		return true;
	}

	if(!file_exists("$storage_dir/$storage_file")){
		$projection_array[$year][$quarter][$category][$department] = $value;
		write_array_to_file('projection_array', $projection_array, "$storage_dir/$storage_file");
		return true;
	}
	else{
		require("$storage_dir/$storage_file");
		$projection_array[$year][$quarter][$category][$department] = $value;
		write_array_to_file('projection_array', $projection_array, "$storage_dir/$storage_file");
		return true;
	}
}

