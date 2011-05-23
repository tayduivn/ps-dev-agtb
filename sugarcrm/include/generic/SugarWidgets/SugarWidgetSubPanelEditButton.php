<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelEditButton
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: SugarWidgetSubPanelEditButton.php 54581 2010-02-18 00:01:21Z dwheeler $

require_once('include/generic/SugarWidgets/SugarWidgetField.php');
//TODO Rename this to edit link
class SugarWidgetSubPanelEditButton extends SugarWidgetField
{
	function displayHeaderCell(&$layout_def)
	{
		return '&nbsp;';
	}

	function displayList(&$layout_def)
	{
		global $app_strings, $beanList;
//BEGIN SUGARCRM flav=pro ONLY
		$this->bean = new $beanList[$layout_def['module']]();
//END SUGARCRM flav=pro ONLY

		$edit_icon_html = SugarThemeRegistry::current()->getImage( 'edit_inline', 'align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_EDIT']);

        $onclick ='';
//BEGIN SUGARCRM flav=pro ONLY
		$formname = $this->getFormName($layout_def);

		$onclick = "document.forms['{$formname}'].record.value='{$layout_def['fields']['ID']}';";
		$onclick .= "retValz = SUGAR.subpanelUtils.sendAndRetrieve('" . $formname
			. "', 'subpanel_" . $layout_def['subpanel_id'] . "', '" . addslashes($app_strings['LBL_LOADING'])
			. "', '" . $layout_def['subpanel_id'] . "');";
		$onclick .= "document.forms['{$formname}'].record.value='';retValz;return false;";


		if($layout_def['EditView'] && $this->isQuickCreateValid($layout_def['module'])){
			return '<a href="#" class="listViewTdToolsS1" onclick="' . $onclick . '">' .
                    $edit_icon_html . '&nbsp;' . $app_strings['LNK_EDIT'] .'</a>&nbsp;';
		}else
//END SUGARCRM flav=pro ONLY
            if($layout_def['EditView']) {
			return "<a href='#' onMouseOver=\"javascript:subp_nav('".$layout_def['module']."', '".$layout_def['fields']['ID']."', 'e', this"
			. (empty($layout_def['linked_field']) ? "" : ", '{$layout_def['linked_field']}'") . ");\""
			. " onFocus=\"javascript:subp_nav('".$layout_def['module']."', '".$layout_def['fields']['ID']."', 'e', this"
			. (empty($layout_def['linked_field']) ? "" : ", '{$layout_def['linked_field']}'") . ");\""
			. ' class="listViewTdToolsS1">' . $edit_icon_html . '&nbsp;' . $app_strings['LNK_EDIT'] .'</a>&nbsp;';
		}
		else{
		}

			return '';
		}
	//}

//BEGIN SUGARCRM flav=pro ONLY
	function isQuickCreateValid($module) {
		$isValid = false;
		if(file_exists('custom/modules/'.$module.'/metadata/quickcreatedefs.php')) {
			$isValid = true;
		}
		if(file_exists('modules/'.$module.'/metadata/quickcreatedefs.php')) {
			$isValid = true;
		}
		return $isValid;
	}


	function getFormName(&$layout_def) {
        global $beanList;
	    $parentBean = new $beanList[$_REQUEST['module']]();
        $relFound = false;
        $module_name = strtolower($_REQUEST['module']);
        $formname = "formform";

        //we need to retrieve the relationship name as the form name

        //if this is a collection, just return the module name, start by loading the subpanel definitions
        if (file_exists ( 'modules/' . $parentBean->module_dir . '/metadata/subpaneldefs.php' ))
            require ('modules/' . $parentBean->module_dir . '/metadata/subpaneldefs.php') ;

        if (file_exists ( 'custom/modules/' . $parentBean->module_dir . '/Ext/Layoutdefs/layoutdefs.ext.php' ))
            require ('custom/modules/' . $parentBean->module_dir . '/Ext/Layoutdefs/layoutdefs.ext.php') ;

        //check to make sure the proper arrays were loaded
        if (!empty($layout_defs) && is_array($layout_defs) && !empty($layout_defs[$_REQUEST['module']]) && !empty($layout_defs[$_REQUEST['module']]['subpanel_setup'][$layout_def['subpanel_id']] )){
            //return module name if this is a collection
            $def_to_check = $layout_defs[$_REQUEST['module']]['subpanel_setup'][$layout_def['subpanel_id']];
            if(isset($def_to_check['type']) && $def_to_check['type'] == 'collection'){
                $formname .= $layout_def['module'];
                return $formname;
            }
        }

        //load the bean relationships for the next check
        $this->bean->load_relationships();
        $link = $layout_def['linked_field'];

        //if this is not part of a subpanel collection, see if the link field name and relationship is defined on the subpanel bean
        if(isset($this->bean->$link) && !empty($this->bean->field_name_map[$link]) && !empty($this->bean->field_name_map[$link]['relationship'])){
            //return relationship name
            return $formname . $this->bean->field_name_map[$link]['relationship'];

        }else{
            //if the relationship was not found on the subpanel bean, then see if the relationship is defined on the parent bean
            $subpanelMod = strtolower($layout_def['module']);
            if(!empty($parentBean->field_name_map[$subpanelMod]) && !empty($parentBean->field_name_map[$subpanelMod]['relationship'])){
                //return relationship name
                return $formname . $parentBean->field_name_map[$subpanelMod]['relationship'];

            }
        }

        //as a last resort, if the relationship is not found, then default to the module name
        return $formname . $layout_def['module'];

	}
//END SUGARCRM flav=pro ONLY
}

?>