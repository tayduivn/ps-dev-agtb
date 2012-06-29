<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');

class HomeViewTour extends SugarView
{
 	public function display()
 	{
 		global $sugar_flavor;
        $this->ss->assign('mod', return_module_language($GLOBALS['current_language'], 'Home'));
 	    $this->ss->assign("sugarFlavor",$sugar_flavor);
 		$this->ss->display('modules/Home/tour.tpl');
 	}
}
?>
