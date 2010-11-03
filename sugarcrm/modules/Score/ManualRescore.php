<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */

require_once('modules/Score/Rescore.php');

$scoreModules = array_keys(Score::getScoreModuleData());
$moduleCounts = array();
foreach ( $scoreModules as $module ) {
	$moduleCounts[$module] = getModuleCounts($module);
	if ( isset($GLOBALS['app_list_strings']['moduleList'][$module]) ) {
		$moduleCounts[$module]['label'] = $GLOBALS['app_list_strings']['moduleList'][$module];
	} else {
		$moduleCounts[$module]['label'] = $module;
	}
}

$sugar_smarty	= new Sugar_Smarty();
$sugar_smarty->assign('mod', $mod_strings);
$sugar_smarty->assign('app', $app_strings);
$sugar_smarty->assign('moduleCounts', $moduleCounts);
$sugar_smarty->assign('image_path', $GLOBALS['image_path']);
$sugar_smarty->display('modules/Score/ManualRescore.tpl');
