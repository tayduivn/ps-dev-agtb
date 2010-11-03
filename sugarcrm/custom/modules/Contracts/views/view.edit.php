<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class ContractsViewEdit extends ViewEdit {
    function ContractsViewEdit(){
		parent::ViewEdit();
    }

    function display() {
		global $mod_strings;
		$this->ev->th->clearCache($this->module, 'EditView.tpl');

		// See ITRequest #12543
		// Restrict EditView access to members of the Finance/Sales Operations roles
		if (!$GLOBALS['current_user']->check_role_membership('Finance') && !$GLOBALS['current_user']->check_role_membership('Sales Operations')) {
			sugar_die("Only Sales Ops and Finance are allowed to create/modify Contracts.");
		}

		parent::display();
    }
}
