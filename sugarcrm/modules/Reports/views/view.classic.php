<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/views/view.classic.php');
require_once('include/MVC/Controller/SugarController.php');

class ReportsViewClassic extends ViewClassic{
 	function ReportsViewClassic($bean = null, $view_object_map = array()){
 		parent::ViewClassic();
 	}
 	
 	function preDisplay(){
 		if(!empty($this->view_object_map['action']))
 			$this->action = $this->view_object_map['action'];
 	}
 	
 	
 	function display(){
 		parent::display();
 		$this->action = $GLOBALS['action'];	
 	}
 	
}
?>
