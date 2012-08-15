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

//FILE SUGARCRM flav=ent ONLY

require_once('modules/ModuleBuilder/Module/SugarPortalModule.php');
require_once('modules/ModuleBuilder/Module/SugarPortalFunctions.php');

class SugarPortalBrowser
{
    var $modules = array();

    function loadModules()
    {
        $d = dir('modules');
		while($e = $d->read()){
			if (substr($e, 0, 1) == '.' || !is_dir('modules/' . $e)) continue;
            
			if ((file_exists("modules/$e/metadata/studio.php")) && $this->isPortalModule($e))
			{
				$this->modules[$e] = new SugarPortalModule($e);
			}
		}
    }

    function getNodes(){
        $nodes = array();
        $functions = new SugarPortalFunctions();
        $nodes = $functions->getNodes();
        $this->loadModules();
        $layouts = array();
        foreach($this->modules as $module){
            $layouts[$module->name] = $module->getNodes();
        }
        $nodes[] = array(
            'name'=> translate('LBL_LAYOUTS'),
            'imageTitle' => 'Layouts', 
            'type'=>'Folder', 
            'children'=>$layouts, 
            'action'=>'module=ModuleBuilder&action=wizard&portal=1&layout=1');
        ksort($nodes);
        return $nodes;
    }

    /**
     * Runs through the views metadata directory to check for expected portal 
     * files to verify if a given module is a portal module.
     * 
     * This replaces the old file path checker that looked for 
     * portal/modules/$module/metadata. We are now looking for 
     * modules/$module/metadata/portal/views/(edit|list|detail).php
     *
     * @param string $module The module to check portal validity on
     * @return bool True if a portal/view/$type.php file was found
     */
    function isPortalModule($module) {
        // Create the path to search
        $path = "modules/$module/metadata/portal/views/";

        // Handle it
        // Bug 55003 - Notes showing as a portal module because it has non 
        // standard layouts
        $views = SugarPortalModule::getViewFiles();
        $viewFiles = array_keys($views);
        foreach ($viewFiles as $file) {
            if (file_exists($path . $file)) {
                return true;
            }
        }
        
        return false;
    }

}
?>