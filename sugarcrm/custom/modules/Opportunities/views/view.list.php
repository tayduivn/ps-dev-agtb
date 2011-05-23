<?php

require_once('include/MVC/View/views/view.list.php');

class CustomOpportunitiesViewList extends ViewList {

	// START jvink customizations
	// add duplicate button in Actions menu for ListView

	public function preDisplay() {
        parent::preDisplay();
        $this->lv->actionsMenuExtraItems[] = $this->addDuplicateItem();
    }

	protected function addDuplicateItem() {
		global $app_strings;
		$html = "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' onclick=\"sugarListView.get_checks(); if(sugarListView.get_checks_count() != 1) { alert('Please select one record to be duplicated'); return false;} else { document.MassUpdate.action.value='dupFromListView'; document.MassUpdate.submit(); }\">Duplicate</a>";
		return $html;
	}

	// END jvink customizations
}

?>
