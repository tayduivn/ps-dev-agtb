<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class ProductTemplatesViewEdit extends ViewEdit {
    function ProductTemplatesViewEdit() {
        parent::ViewEdit();
    }

    function display() {
        global $current_user;
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 82
 * Javascript function to call when editview loads to determine if percentage needs to be required
*/

	$js = "<script language='javascript'>\n";
	$js .= "var val = document.getElementById('price_format_c').value;\n";
	$js .= "isPercentRequired(val);\n";
	$js .= "</script>";        

	parent::display();
        echo $js;
    }
}
