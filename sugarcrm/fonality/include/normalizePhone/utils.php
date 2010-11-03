<?php
/**
 * Find the phone number fields
 * which are fields of type phone
 * Felix Nilam - 03/11/2010
 */

function getAllPhoneFields($bean){
	$field_defs = $bean->field_defs;
	$phones = array();
	foreach($field_defs as $def){
		if($def['type'] == 'phone'){
			$phones[] = $def['name'];
		}
	}
	
	return $phones;
}
?>
