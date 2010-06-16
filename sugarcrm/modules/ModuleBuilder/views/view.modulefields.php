<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('modules/ModuleBuilder/MB/AjaxCompose.php');
require_once('modules/ModuleBuilder/views/view.modulefield.php');
 
class ViewModulefields extends SugarView
{
    /**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams()
	{
	    global $mod_strings;
	    
    	return array(
    	   translate('LBL_MODULE_NAME','Administration'),
    	   $mod_strings['LBL_MODULEBUILDER'],
    	   );
    }

	function display()
	{
        $smarty = new Sugar_Smarty();
        global $mod_strings;
        $bak_mod_strings=$mod_strings;
        $smarty->assign('mod_strings', $mod_strings);

        $module_name = $_REQUEST['view_module'];

        if(! isset($_REQUEST['view_package']) || $_REQUEST['view_package'] == 'studio') {
            //$this->loadPackageHelp($module_name);
            $studioClass = new stdClass;
            $studioClass->name = $module_name;
            
            global $beanList;
            $objectName = $beanList[$module_name];
            
            //BEGIN SUGARCRM flav!=sales ONLY
            if($objectName == 'aCase') // Bug 17614 - renamed aCase as Case in vardefs for backwards compatibililty with 451 modules
                $objectName = 'Case';
            //END SUGARCRM flav!=sales ONLY
                
            VardefManager::loadVardef($module_name, $objectName);
            global $dictionary;
            $f = array($mod_strings['LBL_HCUSTOM']=>array(), $mod_strings['LBL_HDEFAULT']=>array());

            // TODO: replace this section to select fields to list with the algorithm in AbstractMetaDataImplmentation::validField()
            $def = $this->cullFields($dictionary[$objectName]['fields']);

            foreach($dictionary[$objectName]['fields'] as $def) {
                if ($this->isValidStudioField($def))
                {
					//Custom relate fields will have a non-db source, but custom_module set
                	if(isset($def['source']) && $def['source'] == 'custom_fields' || isset($def['custom_module'])) {
                       $f[$mod_strings['LBL_HCUSTOM']][$def['name']] = $def;
                    } else {
                       $f[$mod_strings['LBL_HDEFAULT']][$def['name']] = $def;
                    }
                }
            }
            $studioClass->mbvardefs->vardefs['fields'] = $f;
            $smarty->assign('module', $studioClass);

            $package = new stdClass;
            $package->name = '';
            $smarty->assign('package', $package);
            $ajax = new AjaxCompose();
            $ajax->addCrumb($mod_strings['LBL_STUDIO'], 'ModuleBuilder.getContent("module=ModuleBuilder&action=wizard")');
            $ajax->addCrumb(translate($module_name), 'ModuleBuilder.getContent("module=ModuleBuilder&action=wizard&view_module='.$module_name.'")');
            $ajax->addCrumb($mod_strings['LBL_FIELDS'], '');
            $ajax->addSection('center', $mod_strings['LBL_EDIT_FIELDS'],$smarty->fetch('modules/ModuleBuilder/tpls/MBModule/fields.tpl'));
            $_REQUEST['field'] = '';

            echo $ajax->getJavascript();
        } else {
            require_once('modules/ModuleBuilder/MB/ModuleBuilder.php');
            $mb = new ModuleBuilder();
            $mb->getPackage($_REQUEST['view_package']);
            $package = $mb->packages[$_REQUEST['view_package']];

            $package->getModule($module_name);
            $this->module = $package->modules[$module_name];
            $this->loadPackageHelp($module_name);
            $this->module->getVardefs(true);
            $this->module->mbvardefs->vardefs['fields'] = array_reverse($this->module->mbvardefs->vardefs['fields'], true);
            $loadedFields = array();
            foreach($this->module->mbvardefs->vardefs['fields'] as $k=>$v){
                if($k != $module_name)
                    $titleLBL[$k]=translate("LBL_".strtoupper($k),'ModuleBuilder');
                else{
                    $titleLBL[$k]=$k;
                }
                foreach($v as $field => $def)
                {
                	if (isset($loadedFields[$field]))
                		unset($this->module->mbvardefs->vardefs['fields'][$k][$field]);
                	else
                	   $loadedFields[$field] = true;
                }
            }
            $this->module->mbvardefs->vardefs['fields'][$module_name] = $this->cullFields($this->module->mbvardefs->vardefs['fields'][$module_name]);
            if(file_exists($this->module->path. '/language/'.$GLOBALS['current_language'].'.lang.php')){
                include($this->module->path .'/language/'. $GLOBALS['current_language'].'.lang.php');
                $this->module->setModStrings($GLOBALS['current_language'],$mod_strings);
            }
            elseif(file_exists($this->module->path. '/language/en_us.lang.php')){
                include($this->module->path .'/language/en_us.lang.php');
                $this->module->setModStrings('en_us',$mod_strings);
            }
            $smarty->assign('title', $titleLBL);
            $smarty->assign('package', $package);
            $smarty->assign('module', $this->module);
            $smarty->assign('editLabelsMb','1'); //need to merge MB labels and studio labels. quick fix for now.


            $ajax = new AjaxCompose();
            $ajax->addCrumb($bak_mod_strings['LBL_MODULEBUILDER'], 'ModuleBuilder.main("mb")');
            $ajax->addCrumb($package->name,'ModuleBuilder.getContent("module=ModuleBuilder&action=package&package='.$package->name.'")');
            $ajax->addCrumb($module_name, 'ModuleBuilder.getContent("module=ModuleBuilder&action=module&view_package='.$package->name.'&view_module='. $module_name . '")');
            $ajax->addCrumb($bak_mod_strings['LBL_FIELDS'], '');
            $ajax->addSection('center', $bak_mod_strings["LBL_FIELDS"],$smarty->fetch('modules/ModuleBuilder/tpls/MBModule/fields.tpl'));
            $_REQUEST['field'] = '';

            echo $ajax->getJavascript();


        }
    }

    function loadPackageHelp(
        $name
        )
    {
        $this->module->help['default'] = (empty($name))?'create':'modify';
        $this->module->help['group'] = 'module';
    }

    function cullFields(
        $def
        )
    {
        if(!empty($def['parent_id']))
            unset($def['parent_id']);
        if(!empty($def['parent_type']))
            unset($def['parent_type']);
        if(!empty($def['currency_id']))
            unset($def['currency_id']);
        return $def;
    }
	
    function isValidStudioField(
        $def
        )
	{
    	if (isset($def['studio'])) {
            if (is_array($def [ 'studio' ]))
            {
    			if (isset($def['studio']['editField']) && $def['studio']['editField'] == true)
                    return true;
    			if (isset($def['studio']['required']) && $def['studio']['required'])
                    return true;
                    
    		} else
    		{
    			if ($def['studio'] == 'visible')
                    return true;
                if ($def['studio'] == 'hidden' || $def['studio'] == 'false' || !$def['studio'] )
                    return false;
            }
        }
    	if (empty($def ['source']) || $def ['source'] == 'db' || $def ['source'] == 'custom_fields')
		{
    		if ($def ['type'] != 'id' && (empty($def ['dbType']) || $def ['dbType'] != 'id'))
		  return true;
		}
		
		return false;
	}
}