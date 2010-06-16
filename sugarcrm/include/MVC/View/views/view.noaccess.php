<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/vCard.php');

class ViewNoaccess extends SugarView{
	var $type ='noaccess';
	function ViewNoaccess(){
 		parent::SugarView();
 	}
 	
	function display(){
		echo '<p class="error">Warning: You do not have permission to access this module.</p>';
 	}
}
?>
