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

require_once("include/SugarSearchEngine/Interface.php");

class SugarSearchEngine implements SugarSearchEngineInterface{

     public static $_instance;
     private $_searchEngine;


     public function search($query, $offset = 0, $limit = 20){
         if($this->_hasSearchEngine)
         {
            return $this->_searchEngine->search($query, $offset, $limit);
         }
     }

     public function connect($config){
         if($this->_hasSearchEngine)
         {
            $this->_searchEngine->connect($config);
         }
     }

     public function flush(){
         if($this->_hasSearchEngine)
         {
            $this->_searchEngine->flush();
         }
     }

     public function indexBean($bean)
     {
         if($this->_hasSearchEngine)
         {
            $this->_searchEngine->indexBean($bean);
         }
     }

     public function delete($bean)
     {
          if($this->_hasSearchEngine)
          {
             $this->_searchEngine->indexBean($bean);
          }
     }

     /**
      * getInstance()
      *
      * Connect to the backend engine and store for later use
      * 
      * @static
      * @return void
      */
     public static function getInstance($name = '')
     {
        if (!isset(self::$_instance))
        {
            self::$_instance = new SugarSearchEngine();
            self::$_instance->setupEngine($name);
        } // if
        return self::$_instance;
     }

         /**
     * initializes the cache in question
     */
    public function setupEngine($name = '')
    {
        $this->_hasSearchEngine = false;
        if(empty($name))
        {
            //if the name is empty then let's try to see if we have one configured in the config
            if(!empty($GLOBALS['sugar_config']['full_text_engine']))
            {
                $keys = array_keys($GLOBALS['sugar_config']['full_text_engine']);
                $name = $keys[0];
            }
        }
        $locations = array('include/SugarSearchEngine/'.$name,'custom/include/SugarSearchEngine/'.$name);
 	    foreach ( $locations as $location )
         {
            if (sugar_is_dir($location) && $dir = opendir($location))
            {
                while (($file = readdir($dir)) !== false)
                {
                    if ($file == ".." || $file == "." || !is_file("$location/$file") )
                        continue;

                    require_once("$location/$file");

                    $engineClass = basename($file, ".php");

                    if ( class_exists($engineClass))
                    {
                        $GLOBALS['log']->debug("Found full text engine backend $engineClass");
                        $engineInstance = new $engineClass();
                        if (method_exists($engineInstance, "useEngine") && $engineInstance->useEngine())
                        {
                            $this->_searchEngine = $engineInstance;
                            $this->connect($GLOBALS['sugar_config']['full_text_engine'][$name]);
                            $this->_hasSearchEngine = true;
                        }
                    }
                }
            }
        }
    }

     /**
	 * Returns the array containing the $searchFields for a module.  This function
	 * first checks the default installation directories for the SearchFields.php file and then
	 * loads any custom definition (if found)
	 *
	 * @param  $moduleName String name of module to retrieve SearchFields entries for
	 * @return array of SearchFields
	 */
	public static function getSearchFields($moduleName)
	{
		$searchFields = array();

		if(file_exists("modules/{$moduleName}/metadata/SearchFields.php"))
		{
		    require("modules/{$moduleName}/metadata/SearchFields.php");
		}

		if(file_exists("custom/modules/{$moduleName}/metadata/SearchFields.php"))
		{
		    require("custom/modules/{$moduleName}/metadata/SearchFields.php");
		}

		return $searchFields;
	}
 }