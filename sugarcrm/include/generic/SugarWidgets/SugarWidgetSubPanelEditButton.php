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

class SugarWidgetSubPanelEditButton extends SugarWidgetField
{
    protected static $defs = array();
    protected static $edit_icon_html;

	function displayHeaderCell($layout_def)
	{
		return '';
	}

	function displayList($layout_def)
	{
		global $app_strings;
        global $subpanel_item_count;
		$unique_id = $layout_def['subpanel_id']."_edit_".$subpanel_item_count; //bug 51512
        $onclick ='';
//BEGIN SUGARCRM flav=pro ONLY
		$formname = $this->getFormName($layout_def);

		$onclick = "document.forms['{$formname}'].record.value='{$layout_def['fields']['ID']}';";
		$onclick .= "document.forms['{$formname}'].action.value='SubpanelEdits';";
		$onclick .= "retValz = SUGAR.subpanelUtils.sendAndRetrieve('" . $formname
			. "', 'subpanel_" . $layout_def['subpanel_id'] . "', '" . addslashes($app_strings['LBL_LOADING'])
			. "', '" . $layout_def['subpanel_id'] . "');";
		$onclick .= "document.forms['{$formname}'].record.value='';retValz;return false;";

		if($layout_def['EditView'] && $this->isQuickCreateValid($layout_def['module'],$layout_def['subpanel_id'])){
			return '<a href="#" class="listViewTdToolsS1" id="'.$unique_id .'" onclick="' . $onclick . '">' .
                      $app_strings['LNK_EDIT'] .'</a>';
		}else
//END SUGARCRM flav=pro ONLY
        if($layout_def['EditView']) {
            if (isModuleBWC($layout_def['module'])) {
                $label = $app_strings['LNK_EDIT'];
            } else {
                //TODO:SP-1618 can't nav to inline edit in sidecar
                $label = $app_strings['LNK_VIEW'];
            }
			return "<a href='#' onMouseOver=\"javascript:subp_nav('".$layout_def['module']."', '".$layout_def['fields']['ID']."', 'e', this"
			. (empty($layout_def['linked_field']) ? "" : ", '{$layout_def['linked_field']}'") . ");\""
			. " onFocus=\"javascript:subp_nav('".$layout_def['module']."', '".$layout_def['fields']['ID']."', 'e', this"
			. (empty($layout_def['linked_field']) ? "" : ", '{$layout_def['linked_field']}'") . ");\""
            . " onClick=\"javascript:subp_nav_sidecar('".$layout_def['module']."', '".$layout_def['fields']['ID']."', 'e');\""
			. " class='listViewTdToolsS1' id=\"$unique_id\">". $label .'</a>';
		}

        return '';
    }

//BEGIN SUGARCRM flav=pro ONLY

    protected function getSubpanelDefs($module_dir)
    {
        if(!isset(self::$defs[$module_dir])) {
            $defs = SugarAutoLoader::loadWithMetafiles($module_dir, 'subpaneldefs');
            if($defs) {
                require $defs;
            }
            $defs = SugarAutoLoader::loadExtension("layoutdefs", $module_dir);
            if($defs) {
            	require $defs;
            }

            if(!isset($layout_defs))
            {
                return null;
            }

            self::$defs[$module_dir] = $layout_defs;
        }

        return self::$defs[$module_dir];

    }

    function isQuickCreateValid($module,$panel_id)
    {
        //try to retrieve the subpanel defs
        global $beanList;
        $isValid = false;
        $layout_defs = $this->getSubpanelDefs($_REQUEST['module']);

        //For Sidecar modules return false as we want caller to add an
        //onClick routed to the SubPanelTiles.js subp_nav_sidecar function
        if (!isModuleBWC($module)) {
            return false;
        }

        //lets check to see if the subpanel buttons are defined, and if they extend quick create
        //If no buttons are defined, then the default ones are used which do NOT use quick create
        if (!empty($panel_id) && !empty($layout_defs) && is_array($layout_defs)
            && !empty($layout_defs[$_REQUEST['module']]) && !empty($layout_defs[$_REQUEST['module']]['subpanel_setup'][$panel_id])
            && !empty($layout_defs[$_REQUEST['module']]['subpanel_setup'][$panel_id]['top_buttons'])
            && is_array($layout_defs[$_REQUEST['module']]['subpanel_setup'][$panel_id]['top_buttons'])
        ){
            //we have the buttons from the definitions, lets see if they enabled for quickcreate
            foreach($layout_defs[$_REQUEST['module']]['subpanel_setup'][$panel_id]['top_buttons'] as $buttonClasses){
                $buttonClass = '';
                //get the button class
                if (isset($buttonClasses['widget_class'])){
                    $buttonClass = $buttonClasses['widget_class'];
                }
                //include the button class and see if it extends quick create
                $className = 'SugarWidget'.$buttonClass;
                if (SugarAutoLoader::requireWithCustom('include/generic/SugarWidgets/'.$className.'.php')) {
                    if (class_exists($className)){
                        $button = new $className();
                        //set valid flag to true if this class extends quickcreate button
                        if($button instanceof SugarWidgetSubPanelTopButtonQuickCreate){
                            $isValid = true;
                        }
                     }
                }
            }
        }


        //if only default buttons are used, or none of the buttons extended quick create, then there is no need to proceed
        if(!$isValid){
            return false;
        }

        //So our create buttons are defined, now lets check for the proper quick create meta files
        if(SugarAutoLoader::existingCustomOne('modules/'.$module.'/metadata/quickcreatedefs.php')) {
            return true;
        }

        return false;
    }

	function getFormName($layout_def)
	{
        global $currentModule;
        $formname = "formform";
        $mod = $currentModule;

        //we need to retrieve the relationship name as the form name
        //if this is a collection, just return the module name, start by loading the subpanel definitions
        $layout_defs = $this->getSubpanelDefs($mod);

        //check to make sure the proper arrays were loaded
        if (!empty($layout_defs) && is_array($layout_defs) && !empty($layout_defs[$mod]) && !empty($layout_defs[$mod]['subpanel_setup'][$layout_def['subpanel_id']] )){
            //return module name if this is a collection
            $def_to_check = $layout_defs[$mod]['subpanel_setup'][$layout_def['subpanel_id']];
            if(isset($def_to_check['type']) && $def_to_check['type'] == 'collection'){
                $formname .= $layout_def['module'];
                return $formname;
            }
        }

        global $beanList;


		if(empty($this->bean)) {
            $this->bean = BeanFactory::getBean($layout_def['module']);
		}

        //load the bean relationships for the next check
        $link = $layout_def['linked_field'];
        if (empty($this->bean->$link))
            $this->bean->load_relationship($link);

        //if this is not part of a subpanel collection, see if the link field name and relationship is defined on the subpanel bean
        if(isset($this->bean->$link) && !empty($this->bean->field_name_map[$link]) && !empty($this->bean->field_name_map[$link]['relationship'])){
            //return relationship name
            return $formname . $this->bean->field_name_map[$link]['relationship'];

        } else {
            //if the relationship was not found on the subpanel bean, then see if the relationship is defined on the parent bean
	        $parentBean = BeanFactory::getBean($mod);
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
