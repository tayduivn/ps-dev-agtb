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

class SugarModule
{
    //BEGIN SUGARCRM flav=sugarmdle ONLY
    protected static $_moduleCache = array();
    //END SUGARCRM flav=sugarmdle ONLY
    protected static $_instances = array();

    protected $_moduleName;

    public static function get(
        $moduleName
        )
    {
        if ( !isset(self::$_instances[$moduleName]) )
            self::$_instances[$moduleName] = new SugarModule($moduleName);

        return self::$_instances[$moduleName];
    }

    public function __construct(
        $moduleName
        )
    {
        $this->_moduleName = $moduleName;
        //BEGIN SUGARCRM flav=sugarmdle ONLY
        if ( empty($this->_moduleCache) )
            $this->_buildCache();

        // set the default module properties
        if ( isset($this->_moduleCache[$moduleName]['beanName']) )
            $this->beanName = $this->_moduleCache[$moduleName]['beanName'];
        if ( isset($this->_moduleCache[$moduleName]['beanFile']) )
            $this->beanFile = $this->_moduleCache[$moduleName]['beanFile'];
        if ( isset($this->_moduleCache[$moduleName]['inModuleList']) )
            $this->inModuleList = $this->_moduleCache[$moduleName]['inModuleList'];
        if ( isset($this->_moduleCache[$moduleName]['adminOnly']) )
            $this->adminOnly = $this->_moduleCache[$moduleName]['adminOnly'];
        if ( isset($this->_moduleCache[$moduleName]['adminOnlyExceptionViews']) )
            $this->adminOnlyExceptionViews = $this->_moduleCache[$moduleName]['adminOnlyExceptionViews'];
        if ( isset($this->_moduleCache[$moduleName]['parentModule']) )
            $this->parentModule = $this->_moduleCache[$moduleName]['parentModule'];
        //END SUGARCRM flav=sugarmdle ONLY
    }

    //BEGIN SUGARCRM flav=sugarmdle ONLY
    public function __get(
        $name
        )
    {
        return $this->$name;
    }

    protected function _buildCache()
    {
        // before anything, see if this is in external cache before building it again
        if ( !inDeveloperMode() ) {
            $this->_moduleCache = sugar_cache_retrieve('sugar_module_cache');
            if ( !empty($this->_moduleCache) )
                return;
        }

        // first, look in the legacy locations
        $moduleList = array();
        $beanList = array();
        $beanFiles = array();
        $modInvisList = array();
        $adminOnlyList = array();
        $moduleTabMap = array();
        include('include/modules.php');
        if (file_exists('include/modules_override.php'))
            include('include/modules_override.php');
        if (file_exists('custom/application/Ext/Include/modules.ext.php'))
            include('custom/application/Ext/Include/modules.ext.php');

        // convert these arrays to the object values
		foreach ( $moduleList as $moduleName ) {
		    if ( isset($beanList[$moduleName]) )
		        $this->_moduleCache[$moduleName]['beanName'] = $beanList[$moduleName];
		    if ( isset($beanFiles[$beanList[$moduleName]]) )
		        $this->_moduleCache[$moduleName]['beanFile'] = $beanFiles[$beanList[$moduleName]];
		    $_moduleCache[$moduleName]['inModuleList'] = in_array($moduleName,$modInvisList);
		    if ( isset($adminOnlyList[$moduleName]) )
		        $this->_moduleCache[$moduleName]['adminOnly'] = !empty($adminOnlyList[$moduleName]['all']);
		        $this->_moduleCache[$moduleName]['adminExceptionViews'] = $adminOnlyList[$moduleName];
		        if ( isset($this->_moduleCache[$moduleName]['adminExceptionViews']['all']) )
		            unset($this->_moduleCache[$moduleName]['adminExceptionViews']['all']);
            if ( isset($moduleTabMap[$moduleName]) )
                $this->_moduleCache[$moduleName]['parentModule'] = $moduleTabMap[$moduleName];
		}

		// now start looking in the new locations
		$locations = array('modules','custom/modules');
		foreach ( $locations as $location ) {
            if (sugar_is_dir($location) && $dir = opendir($location)) {
                while (($moduleDir = readdir($dir)) !== false) {
                    if ($moduleDir == ".."
                            || $moduleDir == "."
                            || !is_dir($moduleDir)
                            || !is_file("{$location}/{$moduleDir}/moduledef.php")
                            )
                        continue;
                    $moduledef = array();
                    include("{$location}/{$moduleDir}/moduledef.php");
                    $this->_moduleCache[$moduleDir] = $moduledef;
                }
            }
        }

        // push into external cache
        if ( !inDeveloperMode() )
            sugar_cache_put('sugar_module_cache',$this->_moduleCache);
    }

    /**
     * Retrieves a module's language file and returns the array of strings included.
     *
     * @param  string $language optional, defaults to the current application language
     * @param  bool   $refresh  optional, true if we want to rebuild the source cached file
     * @return array
     */
    public function getLanguageStrings(
        $language = null,
        $refresh = false
        )
    {
        global $mod_strings;
        global $sugar_config;
        global $currentModule;

        // Store the current mod strings for later
        $temp_mod_strings = $mod_strings;
        $loaded_mod_strings = array();
        if(empty($language))
            $language = $GLOBALS['current_language'];
        $language_used = $language;

        // Bug 21559 - So we can get all the strings defined in the template, refresh
        // the vardefs file if the cached language file doesn't exist.
        if(!file_exists(sugar_cached('modules/'. $this->_moduleName . '/language/'.$language.'.lang.php'))
                && !empty($GLOBALS['beanList'][$this->_moduleName])){
            $object = BeanFactory::getObjectName($this->_moduleName);
            VardefManager::refreshVardefs($this->_moduleName,$object);
        }

        $loaded_mod_strings = LanguageManager::loadModuleLanguage($this->_moduleName, $language,$refresh);

        // cn: bug 6048 - merge en_us with requested language
        if($language != $sugar_config['default_language'])
            $loaded_mod_strings = sugarLangArrayMerge(
                LanguageManager::loadModuleLanguage($this->_moduleName, $sugar_config['default_language'],$refresh),
                    $loaded_mod_strings
                );

        // If we are in debug mode for translating, turn on the prefix now!
        if($sugar_config['translation_string_prefix']) {
            foreach($loaded_mod_strings as $entry_key=>$entry_value) {
                $loaded_mod_strings[$entry_key] = $language_used.' '.$entry_value;
            }
        }

        $return_value = $loaded_mod_strings;
        if(!isset($mod_strings)){
            $mod_strings = $return_value;
        }
        else
            $mod_strings = $temp_mod_strings;

        return $return_value;
    }

    /**
     * Retrieves a module's language file and returns the array of list strings included.
     *
     * @param  string $language optional, defaults to the current application language
     * @return array
     */
    public function getLanguageListStrings(
        $language = null
        )
    {
        global $mod_list_strings;
        global $sugar_config;
        global $currentModule;

        $cache_key = "mod_list_str_lang.".$language.$this->_moduleName;

        // Check for cached value
        $cache_entry = sugar_cache_retrieve($cache_key);
        if(!empty($cache_entry))
        {
            return $cache_entry;
        }

        if(empty($language))
            $language = $GLOBALS['current_language'];
        $language_used = $language;
        $temp_mod_list_strings = $mod_list_strings;
        $default_language = $sugar_config['default_language'];

        if($currentModule == $this->_moduleName && isset($mod_list_strings) && $mod_list_strings != null) {
            return $mod_list_strings;
        }

        // cn: bug 6351 - include en_us if file langpack not available
        // cn: bug 6048 - merge en_us with requested language
        include("modules/$this->_moduleName/language/en_us.lang.php");
        $en_mod_list_strings = array();
        if($language_used != $default_language)
        $en_mod_list_strings = $mod_list_strings;

        if(file_exists("modules/$this->_moduleName/language/$language.lang.php")) {
            include("modules/$this->_moduleName/language/$language.lang.php");
        }

        if(file_exists("modules/$this->_moduleName/language/$language.lang.override.php")){
            include("modules/$this->_moduleName/language/$language.lang.override.php");
        }

        if(file_exists("modules/$this->_moduleName/language/$language.lang.php.override")){
            echo 'Please Change:<br>' . "modules/$this->_moduleName/language/$language.lang.php.override" . '<br>to<br>' . 'Please Change:<br>' . "modules/$module/language/$language.lang.override.php";
            include("modules/$this->_moduleName/language/$language.lang.php.override");
        }

        // cn: bug 6048 - merge en_us with requested language
        $mod_list_strings = sugarLangArrayMerge($en_mod_list_strings, $mod_list_strings);

        // if we still don't have a language pack, then log an error
        if(!isset($mod_list_strings)) {
            $GLOBALS['log']->fatal("Unable to load the application list language file for the selected language($language) or the default language($default_language) for module({$module})");
            return null;
        }

        $return_value = $mod_list_strings;
        $mod_list_strings = $temp_mod_list_strings;

        sugar_cache_put($cache_key, $return_value);
        return $return_value;
	}
    //END SUGARCRM flav=sugarmdle ONLY
    /**
     * Returns true if the given module implements the indicated template
     *
     * @param  string $template
     * @return bool
     */
    public function moduleImplements(
        $template
        )
    {
        $focus = self::loadBean();

        if ( !$focus )
            return false;

        return is_a($focus,$template);
    }

    /**
     * Returns the bean object of the given module
     *
     * @return object
     */
    public function loadBean($beanList = null, $beanFiles = null, $returnObject = true)
    {
        // Populate these reference arrays
        if ( empty($beanList) ) {
            global $beanList;
        }
        if ( empty($beanFiles) ) {
            global $beanFiles;
        }
        if ( !isset($beanList) || !isset($beanFiles) ) {
            require('include/modules.php');
        }

        if ( isset($beanList[$this->_moduleName]) ) {
            $bean = $beanList[$this->_moduleName];
            if (isset($beanFiles[$bean])) {
                if ( !$returnObject ) {
                    return true;
                }
                if ( !sugar_is_file($beanFiles[$bean]) ) {
                    return false;
                }
                require_once($beanFiles[$bean]);
                $focus = new $bean;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }

        return $focus;
    }
}
