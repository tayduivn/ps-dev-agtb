<?php

class DocumentLogicHooks {

	// BEGIN sadek customization
	// update the tags associated with this record
	function saveTags(&$focus, $event, $arguments) {

		if (isset($_REQUEST['tags'])) {
			IBMHelper::saveTags($focus->module_dir, $focus->id, $_REQUEST['tags']);
		}

	}
	// END sadek customization

	// BEGIN sadek customization
	// get tags associated with this record
	function getTags(&$focus, $event, $arguments) {

		$tags = IBMHelper::getRecordTags($focus);

		// encode as a Multienum so the EditView can process it correctly
		$focus->tags = encodeMultienumValue($tags);

	}
	// END sadek customization
	
}
