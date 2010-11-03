<?php
require_once('fonality/include/normalizePhone/normalizePhone.php');
require_once('fonality/include/normalizePhone/utils.php');
class Normalize
{
	function normalize_phones(&$bean){
		// Normalize all phone fields and store it in custom fields
		// the custom fields are of the form phone_field_normalized_c
		// Get all phone fields
		$phone_fields = array();
		$all_field_def_names = array();
		foreach($bean->field_defs as $key => $def){
			$all_field_def_names[] = $def['name'];
		}
		$phone_fields = getAllPhoneFields($bean);
		foreach($phone_fields as $phone){
			$custom_field = $phone."_normalized_c";
			if(in_array($custom_field, $all_field_def_names)){
				$bean->$custom_field = normalizePhone($bean->$phone);
			}
		}
	}
}
?>
