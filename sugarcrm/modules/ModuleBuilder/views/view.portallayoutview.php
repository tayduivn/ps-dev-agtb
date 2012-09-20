<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

//FILE SUGARCRM flav=ent ONLY

require_once('modules/ModuleBuilder/views/view.layoutview.php');
require_once('modules/ModuleBuilder/parsers/ParserFactory.php');
require_once('modules/ModuleBuilder/MB/AjaxCompose.php');

class ViewPortalLayoutView extends ViewLayoutView 
{
	function ViewPortalLayoutView()
	{
	    $GLOBALS['log']->debug('in ViewPortalLayoutView');
		$this->editModule = $_REQUEST['view_module'];
		$this->editLayout = $_REQUEST['view'];
	}

	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;
	    
    	return array(
    	   translate('LBL_MODULE_NAME','Administration'),
    	   ModuleBuilderController::getModuleTitle(),
    	   );
    }

	// DO NOT REMOVE - overrides parent ViewEdit preDisplay() which attempts to load a bean for a non-existent module
	function preDisplay() 
	{
	}

	function display() 
	{
	    $this->parser = ParserFactory::getParser(MB_PORTAL . strtolower($this->editLayout),$this->editModule,null,null,MB_PORTAL);
		$smarty = new Sugar_Smarty();
		
		//Add in the module we are viewing to our current mod strings
		global $mod_strings, $current_language;
		$editModStrings = return_module_language($current_language, $this->editModule);
		$mod_strings = sugarArrayMerge($editModStrings, $mod_strings);
		$smarty->assign('mod', $mod_strings);
		$smarty->assign('MOD', $mod_strings);
		
		// assign buttons
		$images = array('icon_save' => 'studio_save', 'icon_publish' => 'studio_publish', 'icon_address' => 'icon_Address', 'icon_emailaddress' => 'icon_EmailAddress', 'icon_phone' => 'icon_Phone');
		foreach($images as $image=>$file) {
			$smarty->assign($image,SugarThemeRegistry::current()->getImage($file, ''
,null,null,'.gif',$file));
		}
		$smarty->assign('icon_delete',SugarThemeRegistry::current()->getImage('icon_Delete','',48,48,'.gif',$mod_strings['LBL_MB_DELETE'] ));

		$buttons = array();
		$buttons[] = array(
		  'id'=>'saveBtn',
		  'image'=>SugarThemeRegistry::current()->getImage($images['icon_save'],'',null,null,'.gif',$mod_strings['LBL_BTN_SAVE']),
		  'text'=>$GLOBALS['mod_strings']['LBL_BTN_SAVE'],
		  'actionScript'=>"onclick='if(Studio2.checkCalcFields(\"{$_REQUEST['view']}\", \"ERROR_CALCULATED_PORTAL_FIELDS\"))Studio2.handleSave();'"
		);
		$buttons[] = array(
		  'id'=>'publishBtn',
		  'image'=>SugarThemeRegistry::current()->getImage($images['icon_publish'],'',null,null,'.gif',$mod_strings['LBL_BTN_PUBLISH']),
		  'text'=>$GLOBALS['mod_strings']['LBL_BTN_SAVEPUBLISH'],
		  'actionScript'=>"onclick='if(Studio2.checkCalcFields(\"{$_REQUEST['view']}\", \"ERROR_CALCULATED_PORTAL_FIELDS\"))Studio2.handlePublish();'"
		);

		$html = "";
		foreach($buttons as $button){
		    if ($button['id'] == "spacer") {
                $html .= "<td style='width:{$button['width']}'> </td>";
            } else {
                $html .= "<td><input id='{$button['id']}' type='button' valign='center' class='button' style='cursor:pointer' "
                   . "onmousedown='this.className=\"buttonOn\";return false;' onmouseup='this.className=\"button\"' "
                   . "onmouseout='this.className=\"button\"' {$button['actionScript']} value = '{$button['text']}' ></td>" ;
            }
		}

		$smarty->assign('buttons', $html);

		// assign fields and layout
		$smarty->assign('available_fields', $this->parser->getAvailableFields());
        $smarty->assign ( 'field_defs', $this->parser->getFieldDefs () ) ;
		$smarty->assign('layout', $this->parser->getLayout());
		$smarty->assign('view_module', $this->editModule);
		$smarty->assign('calc_field_list', json_encode($this->parser->getCalculatedFields()));
		$smarty->assign('view', $this->editLayout);
        $smarty->assign('maxColumns', $this->parser->getMaxColumns());
		$smarty->assign('fieldwidth', '150px');
		$smarty->assign('translate',true);
		$smarty->assign('fromPortal',true); // flag for form submittal - when the layout is submitted the actions are the same for layouts and portal layouts, but the parsers must be different...

		if (!empty($this->parser->usingWorkingFile)) {
			$smarty->assign('layouttitle',$GLOBALS['mod_strings']['LBL_LAYOUT_PREVIEW']);
		} else {
			$smarty->assign('layouttitle',$GLOBALS['mod_strings']['LBL_CURRENT_LAYOUT']);
		}

		$ajax = new AjaxCompose();

		$ajax->addCrumb(translate('LBL_SUGARPORTAL', 'ModuleBuilder'), 'ModuleBuilder.main("sugarportal")');
        $ajax->addCrumb(translate('LBL_LAYOUTS', 'ModuleBuilder'), 'ModuleBuilder.getContent("module=ModuleBuilder&action=wizard&portal=1&layout=1")');
        $ajax->addCrumb(ucwords(translate('LBL_MODULE_NAME',$this->editModule)), 'ModuleBuilder.getContent("module=ModuleBuilder&action=wizard&portal=1&editModule='.$this->editModule.'")');
        $ajax->addCrumb(ucwords($this->editLayout), '');

		// set up language files
		$smarty->assign('language',$this->parser->getLanguage());	// for sugar_translate in the smarty template

		//navjeet- assistant logic has changed
		//include('modules/ModuleBuilder/language/en_us.lang.php');
		//$smarty->assign('assistantBody', $mod_strings['assistantHelp']['module']['editView'] );
		$ajax->addSection('center', $GLOBALS['mod_strings']['LBL_EDIT_LAYOUT'],$smarty->fetch('modules/ModuleBuilder/tpls/layoutView.tpl'));
		echo $ajax->getJavascript();
	}
}
