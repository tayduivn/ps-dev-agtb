<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//require_once('include/Utils.php');

require_once('modules/ExpressionEngine/formulaHelper.php');

class ViewEditFormula extends SugarView
{
	function ViewEditFormula(){
		$this->options['show_footer'] = false;
		if (isset ($_REQUEST['embed']) && $_REQUEST['embed'])
		{
			$this->options['show_header'] = false;
		}
		parent::SugarView();

 	}

 	function display(){
 		global $app_strings, $current_user, $mod_strings, $theme, $beanList, $beanFiles;
		$smarty = new Sugar_Smarty();
 		$json = new JSON();
		require_once('include/JSON.php');
		//Load the field list from the target module
        if(!empty($_REQUEST['targetModule']) && $_REQUEST['targetModule'] != 'undefined')
 		{
			$module = $_REQUEST['targetModule'];
 			if (isset($_REQUEST['package']) && $_REQUEST['package'] != 'studio' && $_REQUEST['package'] != '') {
				//Get the MB Parsers
 				require_once('modules/ModuleBuilder/MB/MBPackage.php');
 				$pak = new MBPackage($_REQUEST['package']);
 				$defs = $pak->modules[$module]->getVardefs();
                $fields = FormulaHelper::cleanFields(array_merge($pak->modules[$module]->getLinkFields(), $defs['fields']));
 			} else {
 				$class = $beanList[$module];
				require_once $beanFiles [ $class ] ;
	        	$seed = new $class ( ) ;
	        	$fields = FormulaHelper::cleanFields($seed->field_defs);
 			}
        	$smarty->assign('Field_Array', $json->encode($fields));
		}
		else
		{
			$fields = array(array('income', 'number'), array('employed', 'boolean'), array('first_name', 'string'), array('last_name', 'string'));
			$smarty->assign('Field_Array', $json->encode($fields));
		}
		if (!empty($_REQUEST['targetField']))
		{
			$smarty->assign("target", $_REQUEST['targetField']);
		}
        if (isset($_REQUEST['returnType']))
		{
			$smarty->assign("returnType", $_REQUEST['returnType']);
		}
		//Assign any requested Javascript event actions
		foreach(array('onSave', 'onLoad', 'onClose') as $e) {
			if (!empty($_REQUEST[$e]))
			{
				$smarty->assign($e, html_entity_decode($_REQUEST[$e], ENT_QUOTES));
			} else
			{
				$smarty->assign($e, 'function(){}');
			}
		}
		//Check if we need to load Ext ourselves
 		if (!isset($_REQUEST['loadExt']) || ($_REQUEST['loadExt'] && $_REQUEST['loadExt'] != "false"))
		{
			$smarty->assign('loadExt', true);
		} else
		{
			$smarty->assign('loadExt', false);
		}
		if (!empty($_REQUEST['formula'])) {
			$smarty->assign('formula', $json->decode(htmlspecialchars_decode($_REQUEST['formula'])));
		}
		if (isset($_REQUEST['returnType'])) {
			$smarty->assign('returnType', $_REQUEST['returnType']);
		}
 		$smarty->assign('app_strings', $app_strings);
 		$smarty->assign('mod', $mod_strings);
 		$smarty->display('modules/ExpressionEngine/tpls/formulaBuilder.tpl');
 	}


}
?>