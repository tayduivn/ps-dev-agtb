<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class OrdersViewEdit extends ViewEdit {
    function OrdersViewEdit() {
        parent::ViewEdit();
    }

    function display() {
        global $app_list_strings;
        global $mod_strings;
        global $current_user;

	if (!(isset($this->bean->status) && $this->bean->status == 'Completed')) {
	    unset($app_list_strings['order_status_list']['Completed']);
	}

		// BEGIN jostrow customization
		// See ITRequest #19920 -- people shouldn't be modifying (or especially trying to complete) old Orders that we imported from xCart
		// We began the MoofCart order numbers at 40,000 -- so this prevents anybody from modifying an Order whose order number is under 40,000.

		if (!empty($this->bean->order_id) && $this->bean->order_id < 40000) {
			die("Modifications to Orders imported from xCart are not allowed -- any order number under 40,000 came from xCart.  <a href='/index.php?module=Orders&action=DetailView&record={$this->bean->id}'>Back to DetailView</a>");
		}

		// END jostrow customization

        $js = "\n<script>\n";
		
        $js .= "\n</script>\n";
		
        parent::display();
        echo $js;
    }
}
