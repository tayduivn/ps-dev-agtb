<?php
/**
 * Created by JetBrains PhpStorm.
 * User: admin
 * Date: 11/2/11
 * Time: 8:59 AM
 * To change this template use File | Settings | File Templates.
 */
require_once("include/SugarSearchEngine/ISugarSearchEngine.php");
class SugarSearchEngine implements ISugarSearchEngine{
     public static $_instance;
     private $_searchEngine;


     public function search($query, $offset = 0, $limit = 20){
         if($this->_hasSearchEngine){
            return $this->_searchEngine->search($query, $offset, $limit);
         }
     }

     public function connect($config){
         if($this->_hasSearchEngine){
            $this->_searchEngine->connect($config);
         }
     }

     public function flush(){
         if($this->_hasSearchEngine){
            $this->_searchEngine->flush();
         }
     }

     public function indexBean($bean){
         if($this->_hasSearchEngine){
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
     public static function getInstance($name = ''){
        if (!isset(self::$_instance)) {
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
        if(empty($name)){
            //if the name is empty then let's try to see if we have one configured in the config
            if(!empty($GLOBALS['sugar_config']['full_text_engine'])){
                $keys = array_keys($GLOBALS['sugar_config']['full_text_engine']);
                $name = $keys[0];
            }
        }
        $locations = array('include/SugarSearchEngine/'.$name,'custom/include/SugarSearchEngine/'.$name);
 	    foreach ( $locations as $location ) {
            if (sugar_is_dir($location) && $dir = opendir($location)) {
                while (($file = readdir($dir)) !== false) {
                    if ($file == ".."
                            || $file == "."
                            || !is_file("$location/$file")
                            )
                        continue;
                    require_once("$location/$file");

                    $engineClass = basename($file, ".php");

                    if ( class_exists($engineClass)) {
                        $GLOBALS['log']->debug("Found full text engine backend $engineClass");
                        $engineInstance = new $engineClass();
                        if (method_exists($engineInstance, "useEngine") && $engineInstance->useEngine()) {
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