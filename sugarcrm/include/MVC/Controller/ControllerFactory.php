<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once 'include/MVC/Controller/SugarController.php';
/**
 * MVC Controller Factory
 * @api
 */
class ControllerFactory
{
	/**
	 * Obtain an instance of the correct controller.
	 *
	 * @return an instance of SugarController
	 */
	function getController($module)
	{
		if(SugarAutoLoader::requireWithCustom("modules/{$module}/controller.php")) {
		    $class = SugarAutoLoader::customClass(ucfirst($module).'Controller');
		} else {
		    SugarAutoLoader::requireWithCustom('include/MVC/Controller/SugarController.php');
		    $class = SugarAutoLoader::customClass('SugarController');
		}
		if(class_exists($class, false)) {
			$controller = new $class();
		}

		if(empty($controller)) {
		    $controller = new SugarController();
		}
		//setup the controller
		$controller->setup($module);
		return $controller;
	}
}