<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY

/*
 * Function used in vardefs to retrieve options list
 */
function getPdfManagerAvailableModules() {
    return PdfManagerHelper::getAvailableModules();
}


class PdfManagerHelper {

    /**
     * Returns a list of available modules for PdfManager
     *
     * @return array
     */
    static function getAvailableModules(){
        $module_names = array_change_key_case ($GLOBALS['app_list_strings']['moduleList']);   
        require_once('modules/ModuleBuilder/Module/StudioBrowser.php');
        $studio_browser = new StudioBrowser();
        $studio_browser->loadModules();
        $studio_modules = array_keys($studio_browser->modules);
        $available_modules = array('Reports' => isset($module_names[strtolower('Reports')]) ? $module_names[strtolower('Reports')] : strtolower('Reports'));
        foreach ($studio_modules as $module_name) {
            $available_modules[$module_name] = isset($module_names[strtolower($module_name)]) ? $module_names[strtolower($module_name)] : strtolower($module_name);
        }
        asort($available_modules);
        return $available_modules;
    }

    /**
     * Takes an module name and returns a list of fields and links available for this module in PdfManager
     *
     * @param string $moduleName
     * @param boolean $addLinks
     * @return array
     */    
    static function getFields($moduleName, $addLinks = false) {
        $fieldsForSelectedModule = array();
        if (!empty($moduleName)) {
            // Retrieve the list of field
            $fieldsForSelectedModule = FormulaHelper::getRelatableFieldsForLink(array('module' => $moduleName));
            asort($fieldsForSelectedModule);
            
            if (!empty($fieldsForSelectedModule) && $addLinks) {
                $linksForSelectedModule = PdfManagerHelper::getLinksForModule($moduleName);
                if (count($linksForSelectedModule) > 0) {
                    //$fieldsForSelectedModule[''] = '-----';
                    $linksFieldsForSelectedModule = array();
                    foreach ($linksForSelectedModule as $linkName => $linkDef) {
                        $linksFieldsForSelectedModule['pdfManagerRelateLink_' . $linkName] = $linkDef['label'];
                    }
                    asort($linksFieldsForSelectedModule);
                    //$fieldsForSelectedModule += $linksFieldsForSelectedModule;
                    $fieldsForSelectedModule = array(
                        translate('LBL_FIELDS_LIST', 'PdfManager') => $fieldsForSelectedModule,
                        translate('LBL_LINK_LIST', 'PdfManager') => $linksFieldsForSelectedModule,
                    );
                    
                }
            }
        }    
        
        return $fieldsForSelectedModule;
    }
    
    
    static function getLinksForModule($module) {
        $focus = BeanFactory::newBean($module);
        $focus->id = create_guid();
                
        $fields = FormulaHelper::cleanFields($focus->field_defs);

        $links = array();
        
        if ($module == 'Quotes') {
            $focusBundle = BeanFactory::newBean('ProductBundles');
            $focusBundle->id = create_guid();
            $name = 'products';
            $def = $focusBundle->field_defs[$name];
            $focusBundle->load_relationship($name);                    
            $fieldsBundle = FormulaHelper::cleanFields($focusBundle->field_defs);
            $label = empty($def['vname']) ? $name : translate($def['vname'], $module);
            $relatedModule = 'Product';
            $links[$name] = array (
                "label" => "$label ($relatedModule)",
                "module" => $relatedModule
            );

            $name = 'product_bundles';
            $def = $focus->field_defs[$name];
            $focus->load_relationship($name);
            $relatedModule = $focus->$name->getRelatedModuleName();
            $label = empty($def['vname']) ? $name : translate($def['vname'], $module);
            $links[$name] = array(
                "label" => "$label ($relatedModule)",
                "module" => $relatedModule
            );



        }
        
        //Next, get a list of all links and the related modules
        foreach ($fields as $val) {
            $name = $val[0];
            $def = $focus->field_defs[$name];
            if ($val[1] == "relate" && $focus->load_relationship($name)) {
                $relatedModule = $focus->$name->getRelatedModuleName();
                if (
                    (isset($def['link_type']) && $def['link_type'] == 'one') ||
                    ($focus->$name->_relationship->relationship_type == 'one-to-one') || 
                    ($focus->$name->_relationship->relationship_type == 'one-to-many' && !$focus->$name->_get_bean_position()) || 
                    ($focus->$name->_relationship->relationship_type == 'many-to-one' && $focus->$name->_get_bean_position()) ||
                    ($focus->$name->_relationship->relationship_type == 'many-to-many' && !isset($def['side']) && $focus->$name->_get_link_table_definition($focus->$name->_relationship_name, 'true_relationship_type') == 'one-to-many' && !$focus->$name->_get_bean_position()) ||
                    ($focus->$name->_relationship->relationship_type == 'many-to-many' && !isset($def['side']) && $focus->$name->_get_link_table_definition($focus->$name->_relationship_name, 'true_relationship_type') == 'many-to-one' && $focus->$name->_get_bean_position())                
                ) {
                    //MB will sometimes produce extra link fields that we need to ignore
                    if (!empty($def['side']) && (substr($name, -4) == "_ida" || substr($name, -4) == "_idb")){
                        continue;
                    }

                
                    $label = empty($def['vname']) ? $name : translate($def['vname'], $module);
                    $links[$name] = array(
                        "label" => "$label ($relatedModule)",
                        "module" => $relatedModule
                    );
                }
            }
        }

        return $links;
    }    
    
}


