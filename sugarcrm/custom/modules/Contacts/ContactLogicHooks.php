<?php

class ContactLogicHooks {

	// BEGIN jostrow customization
	// update the tags associated with this record

	function saveTags(&$focus, $event, $arguments) {

		if (isset($_REQUEST['tags'])) {
			IBMHelper::saveTags($focus->module_dir, $focus->id, $_REQUEST['tags']);
		}

	}

	// END jostrow customization

	// BEGIN jostrow customization
	// get tags associated with this record

	function getTags(&$focus, $event, $arguments) {

		$tags = IBMHelper::getRecordTags($focus);

		// encode as a Multienum so the EditView can process it correctly
		$focus->tags = encodeMultienumValue($tags);

	}

	// END jostrow customization
	
	// BEGIN sadek - SET SUPPRESSION HTML AROUND LISTVIEW COLUMNS FOR PHONE AND EMAIL
	function strikeSuppressedFields(&$focus, $event, $arguments) {
		if(!empty($focus->phone_work_suppressed)){
			$focus->phone_work = "<strike>{$focus->phone_work}</strike>";
		}
	} 
	// END sadek - SET SUPPRESSION HTML AROUND LISTVIEW COLUMNS FOR PHONE AND EMAIL
}
