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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

/**
 * Search Engines drivers factory class
 * @api
 */
class SugarSearchEngineFactory
{
    /**
     * @var SugarSearchEngineInterface
     */
    public static $_instance;

    /**
     * Singleton pattern
     */
    private function __construct(){}

    /**
     * getInstance()
     *
     * Connect to the backend engine and store for later use
     *
     * @static
     * @return SugarSearchEngineInterface
     */
    public static function getInstance($name = '', $config = array(), $useDefaultWhenFTSDown = false)
    {
        if ($useDefaultWhenFTSDown && isSearchEngineDown())
        {
            $name = 'SugarSearchEngine';
        }

       if (!isset(self::$_instance[$name]))
       {
           self::$_instance[$name] = self::setupEngine($name, $config);
       }

       return self::$_instance[$name];
    }

    public static function getFTSEngineNameFromConfig()
    {
        $name = "";
        if(!empty($GLOBALS['sugar_config']['full_text_engine']))
        {
            $keys = array_keys($GLOBALS['sugar_config']['full_text_engine']);
            $name = array_pop($keys);
        }
        return $name;
    }
    /**
     * @static
     * @param string $name
     * @param array $config
     * @return bool
     */
    protected static function setupEngine($name = '', $config = array())
    {
        if( empty($name) )
        {
            //if the name is empty then let's try to see if we have one configured in the config
            if(!empty($GLOBALS['sugar_config']['full_text_engine']))
            {
                $name = self::getFTSEngineNameFromConfig();
                $config = !empty($config) ? $config : $GLOBALS['sugar_config']['full_text_engine'][$name];
            }
        }

        $defaultTemplateLocation = "include/SugarSearchEngine/%s/SugarSearchEngine%s.php";

        $searchEngineLocations = array(
            "custom" . DIRECTORY_SEPARATOR . sprintf($defaultTemplateLocation, $name,$name),
            sprintf($defaultTemplateLocation, $name,$name),
            "custom" . DIRECTORY_SEPARATOR . sprintf($defaultTemplateLocation, '',''),
            sprintf($defaultTemplateLocation, '','')
        );

        foreach($searchEngineLocations as $engineLocation)
        {
            $engineInstance = self::loadSearchEngineFromLocation($engineLocation, $config);
            if($engineInstance !== FALSE)
            {
                $GLOBALS['log']->debug("Found Sugar Search Engine: " . get_class($engineInstance));
                return $engineInstance;
            }
        }
    }

    /**
     * @static
     * @param $filePath
     * @return bool
     */
    protected static function loadSearchEngineFromLocation($filePath, $config)
    {
        $filePath = realpath($filePath);
        if( is_file($filePath) )
        {
            require_once($filePath);
            $engineClass = basename($filePath, ".php");
            $engineInstance = new $engineClass($config);

            if ($engineInstance instanceof SugarSearchEngineInterface )
            {
                return $engineInstance;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }
}

