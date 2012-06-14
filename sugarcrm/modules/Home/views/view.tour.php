<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');

class HomeViewTour extends SugarView
{
 	public function display()
 	{
 	    
 		$this->ss->display('modules/Home/tour.tpl');
 	}
}
?>
