<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');
require_once('include/MVC/Controller/SugarController.php');

class ViewClassic extends SugarView{
 	function ViewClassic(){
 		parent::SugarView();
 		$this->type = $this->action;
 	}
 	
 	function display(){
 		// Call SugarController::getActionFilename to handle case sensitive file names
 		$file = SugarController::getActionFilename($this->action);
 		if(file_exists('custom/modules/' . $this->module . '/'. $file . '.php')){
			$this->includeClassicFile('custom/modules/'. $this->module . '/'. $file . '.php');
			return true;
		}elseif(file_exists('modules/' . $this->module . '/'. $file . '.php')){
			$this->includeClassicFile('modules/'. $this->module . '/'. $file . '.php');
			return true;
		}
		return false;
 	}
}
?>
