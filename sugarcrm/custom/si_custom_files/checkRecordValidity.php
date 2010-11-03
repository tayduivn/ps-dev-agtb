<?php

class checkRecordValidity{
	
	var $warningArray;
	var $warningFields;
	
	function checkRecordValidity(){
		$this->warningArray = array();
	}
	
	function checkValidity(&$focus, $metafile, $html = true){
		$blockedSalesStages = array(
					    //'Closed Lost',
			'Interested_Prospect',
			'Qualified',
		);
		if(get_class($focus) == "Opportunity" && (!isset($focus->sales_stage) || in_array($focus->sales_stage, $blockedSalesStages))){
			return;
		}
		
		$nl = "<br />";
		$sp = "&nbsp;";
		if(!$html){
			$nl = "\n";
			$sp = " ";
		}
		
		require($metafile);
		
		//echo "Opp Name: ".$focus->name."\n";
		$warningList = array();
		$warningFields = array();
		foreach($validityCheckMeta as $field => $valueArray){
			//echo "loop 1\n";
			foreach($valueArray as $fieldValue => $otherFieldChecks){
				//echo "loop 2\n";
				if($fieldValue == 'display_name' || !isset($focus->$field) || $focus->$field != $fieldValue){
					/* DEBUG
					if(!isset($focus->$field)){
						echo "\$focus->$field is not set\n";
					}
					if($focus->$field != $fieldValue){
						echo "\$focus->$field is not equal to $fieldValue\n";
					}
					*/
					continue;
				}
				//echo "Didn't skip for $field having $fieldValue\n";
				
				foreach($otherFieldChecks as $fieldToCheck => $fieldCheckMeta){
					$found_one = false;
					if(isset($fieldCheckMeta['exempt_roles'])){
						foreach($fieldCheckMeta['exempt_roles'] as $role_name){
							if($GLOBALS['current_user']->check_role_membership($role_name)){
								$found_one = true;
							}
						}
					}
					if($found_one == true){
						continue;
					}
					
					if(isset($focus->$fieldToCheck)){
						$prependMessage = "* When the field {$valueArray['display_name']} has a value of {$focus->$field}, ".
												"{$fieldCheckMeta['display_name']} must";
						switch($fieldCheckMeta['value_type']){
							case 'variable_equal':
								$otherField = $fieldCheckMeta['value'];
								if($focus->$fieldToCheck != $focus->$otherField){
									$warningList[] = "$prependMessage be equal to the value of {$fieldCheckMeta['alt_field_display_name']}.";
									$warningFields[$field] = array('field' => $field, 'display' => $valueArray['display_name']);
									$warningFields[$fieldToCheck] = array('field' => $fieldToCheck, 'display' => $fieldCheckMeta['display_name']);
									$warningFields[$otherField] = array('field' => $otherField, 'display' => $fieldCheckMeta['alt_field_display_name']);
								}
								break;
							case 'literal_equal':
								if(is_array($fieldCheckMeta['value'])){
									if(!in_array($focus->$fieldToCheck, $fieldCheckMeta['value'])){
										$valueList = implode("', '", $fieldCheckMeta['value']);
										$warningList[] = "$prependMessage be one of the following values:$nl$sp$sp$sp$sp'$valueList'";
										$warningFields[$field] = array('field' => $field, 'display' => $valueArray['display_name']);
										$warningFields[$fieldToCheck] =
											array('field' => $fieldToCheck, 'display' => $fieldCheckMeta['display_name']);
									}
								}
								else{
									if($focus->$fieldToCheck != $fieldCheckMeta['value']){
										$display = ($fieldCheckMeta['value'] == '' ? 'blank' : "equal to '{$fieldCheckMeta['value']}'");
										$warningList[] = "$prependMessage be $display";
										$warningFields[$field] = array('field' => $field, 'display' => $valueArray['display_name']);
										$warningFields[$fieldToCheck] =
											array('field' => $fieldToCheck, 'display' => $fieldCheckMeta['display_name']);
									}
								}
								break;
							case 'variable_not_equal':
								$otherField = $fieldCheckMeta['value'];
								if($focus->$fieldToCheck == $focus->$otherField){
									$warningList[] = "$prependMessage *not* be equal to the value of {$fieldCheckMeta['alt_field_display_name']}.";
									$warningFields[$field] = array('field' => $field, 'display' => $valueArray['display_name']);
									$warningFields[$fieldToCheck] = array('field' => $fieldToCheck, 'display' => $fieldCheckmeta['display_name']);
									$warningFields[$otherField] = array('field' => $otherField, 'display' => $fieldCheckMeta['alt_field_display_name']);
								}
								break;
							case 'literal_not_equal':
								if(is_array($fieldCheckMeta['value'])){
									if(in_array($focus->$fieldToCheck, $fieldCheckMeta['value'])){
										$valueList = implode("', '", $fieldCheckMeta['value']);
										$warningList[] = "$prependMessage *not* be one of the following values:$nl$sp$sp$sp$sp'$valueList'";
										$warningFields[$field] = array('field' => $field, 'display' => $valueArray['display_name']);
										$warningFields[$fieldToCheck] =
											array('field' => $fieldToCheck, 'display' => $fieldCheckMeta['display_name']);
									}
								}
								else{
									if($focus->$fieldToCheck == $fieldCheckMeta['value']){
										$display = ($fieldCheckMeta['value'] == '' ? 'blank' : "equal to '{$fieldCheckMeta['value']}'");
										$warningList[] = "$prependMessage *not* be $display";
										$warningFields[$field] = array('field' => $field, 'display' => $valueArray['display_name']);
										$warningFields[$fieldToCheck] =
											array('field' => $fieldToCheck, 'display' => $fieldCheckMeta['display_name']);
									}
								}
								break;
							default:
								sugar_die("Case {$fieldCheckMeta['value_type']} not handled. Please contact <a href='mailto:sadek@sugarcrm.com'>sadek@sugarcrm.com</a> with this error message.");
								break;
						}
					}
				}
			}
		}
		$this->warningArray = $warningList;
		$this->warningFields = $warningFields;
	}	
}
?>
