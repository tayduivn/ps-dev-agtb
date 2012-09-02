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

class SugarPortalModule{
	var $name;
	
	function SugarPortalModule($module)
	{
	    global $app_list_strings;
        $moduleNames = array_change_key_case($app_list_strings['moduleList']);
		$this->name = $moduleNames[strtolower($module)];
		$this->module = $module;
		
		$path = 'modules/'.$this->module.'/clients/portal/views/';
        $views = self::getViewFiles();
		foreach($views as $file => $def) {
            $dirname = $path . basename($file, '.php') . '/';
            if (is_dir($dirname) && file_exists($dirname . $file)) {
                $this->views[$file] = $def;
            }
		}
	}
	

	function getNodes()
	{
		$layouts = array();
		if (isset($this->views)) {
            foreach($this->views as $file=>$def){
          			   $file = str_replace($file, '.php', '');
          			   $viewType = ($def['type'] == 'list')?"ListView":ucfirst($def['type']);
          			   $layouts[] = array('name'=>$def['name'], 'module'=>$this->module, 'action'=>"module=ModuleBuilder&action=editPortal&view=${viewType}&view_module=".$this->module);
          		}
        }

		$nodes =  array(
		            'name'=>$this->name, 'module'=>$this->module, 'type'=>'SugarPortalModule', 'action'=>"module=ModuleBuilder&action=wizard&portal=1&view_module=".$this->module, 
		            'children'=>$layouts,
			        );
		return $nodes;
	}
	
    /**
     * Gets an array of expected view files for portal layouts
     * 
     * Added as a helper to bug 55003
     * 
     * @static
     * @return array
     */
	public static function getViewFiles()
    {
        // These mod_strings are ModuleBuilder module strings
        return array(
            'edit.php'   => array('name' => $GLOBALS['mod_strings']['LBL_EDITVIEW'],    'type' => 'editView'),
            'detail.php' => array('name' => $GLOBALS['mod_strings']['LBL_DETAILVIEW'] , 'type' => 'detailView'),
            'list.php'   => array('name' => $GLOBALS['mod_strings']['LBL_LISTVIEW'],    'type' => 'list'),
        );
    }
	
	
	
	
}
?>