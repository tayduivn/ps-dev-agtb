<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'include/MetaDataManager/MetaDataManager.php';

class MetaDataManagerMobile extends MetaDataManager
{
    protected $blackListModuleDataKeys = array(
        'menu'
    );

    protected $allowedModuleViews = array(
        'list',
        'edit',
        'detail',
    );

    protected $allowedModuleLayouts = array(
        'list',
        'edit',
        'detail',
        'subpanels',
    );

    /**
     * Find all modules enabled in Mobile
     *
     * @return array List of Mobile module names
     */
    protected function getModules() {
        // Get the current user module list
        $modules = $this->getTabList();
        
        // add in Users [Bug59548] since it is forcefully removed for the 
        // CurrentUserApi
        if(!array_search('Users', $modules)) {
            $modules[] = 'Users';
        }
        
        return $modules;
    }

    /**
     * Gets the full module list of Mobile.
     * Returns the same module list as `getModules`.
     *
     * @return array List of Mobile module names
     */
    public function getFullModuleList()
    {
        return $this->getModules();
    }

    /**
     * Gets every single module of the application and the properties for every
     * of these modules
     *
     * @return array An array with all the modules and their properties
     */
    public function getModulesInfo()
    {
        // Need to override the base one because it grabs the visibility settings from
        // the $moduleList global and we don't like messing with globals
        $modulesInfo = parent::getModulesInfo();
        if (isset($modulesInfo['Employees'])) {
            $modulesInfo['Employees']['visible'] = $modulesInfo['Employees']['display_tab'];
        }
         
        return $modulesInfo;
    }

    /**
     * Gets the list of mobile modules. Used by getModules and the CurrentUserApi
     * to get the module list for a user.
     * 
     * @return array The list of modules for mobile
     */
    public function getTabList()
    {
        // replicate the essential part of the behavior of the private loadMapping() method in SugarController
        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }

        // Forcibly remove the Users module
        // So if they have added it, remove it here
        if (isset($wireless_module_registry['Users'])) {
            unset($wireless_module_registry['Users']);
        }

        // $wireless_module_registry is defined in the file loaded above
        return isset($wireless_module_registry) && is_array($wireless_module_registry) ?
            array_keys($wireless_module_registry) :
            array();
    }

    public function getQuickcreateList() {
        // replicate the essential part of the behavior of the private loadMapping() method in SugarController
        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }

        // Forcibly remove the Users module
        // So if they have added it, remove it here
        if (isset($wireless_module_registry['Users'])) {
            unset($wireless_module_registry['Users']);
        }

        $quickcreateList = array();

        foreach($wireless_module_registry as $module => $moduleData) {
            if (empty($moduleData['disable_create'])) {
                $quickcreateList[] = $module;
            }
        }
        return $quickcreateList;
    }

    /**
     * Gets the module list for the current user.
     * Returns the same module list as `getTabList`.
     *
     * In the future, there will be a UI to allow user to configure visible
     * modules in his `Profile` section.
     *
     * @return array The list of modules for mobile
     */
    public function getUserModuleList()
    {
        return $this->getTabList();
    }

    /**
     * The same as MetadataApi::loadMetadata except that the result is filtered to remove
     * unnecesary elements for nomad/mobile
     *
     * @return array|void
     */
    protected function loadMetadata($args = array()) {
        $data = parent::loadMetadata($args);

        if (!empty($data['modules'])) {
            foreach($data['modules'] as $module=> $mData) {
                //blacklist certain data types alltogether
                foreach($this->blackListModuleDataKeys as $key) {
                    unset($data['modules'][$module][$key]);
                }
                //views and layouts should be white-list filtered
                if (!empty($mData['views'])) {
                    foreach($mData['views'] as $key => $def) {
                        if (!in_array($key, $this->allowedModuleViews)) {
                            unset($data['modules'][$module]['views'][$key]);
                        }
                    }
                }
                if (!empty($mData['layouts'])) {
                    foreach($mData['layouts'] as $key => $def) {
                        if (!in_array($key, $this->allowedModuleLayouts)) {
                            unset($data['modules'][$module]['layouts'][$key]);
                        }
                    }
                }
            }
        }

        // Handle the new hash
        $data['_hash'] = $this->hashChunk($data);
        return $data;
    }

    /**
     * Retrieve white listed properties which shall be copied from server side
     * configurations to client side configurations.
     *
     * @return array Configuration properties.
     */
    protected function getConfigProperties()
    {
        $properties = parent::getConfigProperties();
        $properties['offlineEnabled'] = true;
        return $properties;
    }
}
