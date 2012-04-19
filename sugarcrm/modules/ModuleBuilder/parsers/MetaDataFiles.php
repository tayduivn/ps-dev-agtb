<?php
require_once 'modules/ModuleBuilder/Module/StudioModuleFactory.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';
class MetaDataFiles
{
    /**
     * Constants for this class, used for pathing metadata files
     */
    const PATHBASE    = '';
    const PATHCUSTOM  = 'custom/';
    const PATHWORKING = 'custom/working/';
    const PATHHISTORY = 'custom/history/';

    /**
     * Path prefixes for metadata files
     *
     * @var array
     * @access public
     * @static
     */
    public static $paths = array(
        MB_BASEMETADATALOCATION    => self::PATHBASE,
        MB_CUSTOMMETADATALOCATION  => self::PATHCUSTOM,
        MB_WORKINGMETADATALOCATION => self::PATHWORKING,
        MB_HISTORYMETADATALOCATION => self::PATHHISTORY,
    );

    /**
     * The types of metadata files that could be loaded and their directory
     * locations inside of the metadata directory
     *
     * @var array
     * @access public
     * @static
     */
    public static $clients = array(
        'base'    => 'base',
        'portal'  => 'portal',
        'mobile'  => 'mobile',
    );

    /**
     * Names of the files themselves
     *
     * @var array
     * @access public
     * @static
     */
    public static $names = array(
        MB_DASHLETSEARCH          => 'dashletviewdefs',
        MB_DASHLET                => 'dashletviewdefs',
        MB_POPUPSEARCH            => 'popupdefs',
        MB_POPUPLIST              => 'popupdefs',
        MB_LISTVIEW               => 'listviewdefs' ,
        MB_BASICSEARCH            => 'searchdefs' ,
        MB_ADVANCEDSEARCH         => 'searchdefs' ,
        MB_EDITVIEW               => 'editviewdefs' ,
        MB_DETAILVIEW             => 'detailviewdefs' ,
        MB_QUICKCREATE            => 'quickcreatedefs',
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        MB_WIRELESSEDITVIEW       => 'edit' ,
        MB_WIRELESSDETAILVIEW     => 'detail' ,
        MB_WIRELESSLISTVIEW       => 'list' ,
        MB_WIRELESSBASICSEARCH    => 'searchView' ,
        MB_WIRELESSADVANCEDSEARCH => 'searchView' ,
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        MB_PORTALEDITVIEW         => 'edit',
        MB_PORTALDETAILVIEW       => 'detail',
        MB_PORTALLISTVIEW         => 'list',
        MB_PORTALSEARCHVIEW       => 'searchView',
        //END SUGARCRM flav=ent ONLY
    );

    /**
     * List of metadata def array vars
     *
     * @static
     * @access public
     * @var array
     */
    public static $viewDefVars = array(
        MB_EDITVIEW    => 'EditView' ,
    	MB_DETAILVIEW  => 'DetailView' ,
    	MB_QUICKCREATE => 'QuickCreate',

        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        MB_WIRELESSEDITVIEW => array('mobile','view','edit'),
        MB_WIRELESSDETAILVIEW => array('mobile','view','detail'),
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        MB_PORTALEDITVIEW => array('portal','view','edit'),
        MB_PORTALDETAILVIEW => array('portal','view','detail'),
        //END SUGARCRM flav=ent ONLY

    );

    /**
     * The path inside the $client directories to the views
     *
     * @var string
     * @access public
     * @static
     */
    public static $viewsPath = 'views/';

    /**
     * Gets the file base names array
     *
     * @static
     * @return array
     */
    public static function getNames() {
        return self::$names;
    }

    /**
     * Gets the file/variable name for a given view
     *
     * @param string $name The name of the view to get the variable/file name for
     * @return string The name of the file/variable
     */
    public static function getName($name) {
        return empty(self::$names[$name]) ? null : self::$names[$name];
    }

    /**
     * Gets the clients array
     *
     * @static
     * @return array
     */
    public static function getClients() {
        return self::$clients;
    }

    /**
     * Gets a particular client by name. $client should map to an index of the
     * clients array.
     *
     * @static
     * @param string $client The client to get
     * @return string
     */
    public static function getClient($client) {
        return empty(self::$clients[$client]) ? '' : self::$clients[$client];
    }

    /**
     * Gets the file paths array
     *
     * @static
     * @return array
     */
    public static function getPaths() {
        return self::$paths;
    }

    /**
     * Gets the view type of a client based on the requested view
     *
     * @static
     * @param string $view The requested view
     * @return string
     */
    public static function getViewClient($view) {
        if (!empty($view)) {
            if (stripos($view, 'portal') !== false) {
                return 'portal';
            }

            if (stripos($view, 'wireless') !== false || stripos($view, 'mobile') !== false) {
                return 'mobile';
            }

            return 'base';
        }

        return '';
    }


    /**
     * helper to give us a parameterized path to create viewdefs for saving to file
     * @param string | array $path (path of keys to use for array)
     * @param mixed $data the data to place at that path
     * @return array the data in the correct path
     */
    public static function mapPathToArray($path, $data)
    {
        if (!is_array($path)) {
            return array($path => $data);
        }

        $arr = $data;
        while($key = array_pop($path)) {
            $arr = array($key => $arr);
        }
        return $arr;
    }

    /**
     * helper to give us a parameterized path find our data from our viewdefs
     * @param string | array $path (path of keys to use for array)
     * @param mixed $arr the array to search for the path
     * @return array| null the data in the correct path or null if a key isn't found.
     */
    public static function mapArrayToPath($path, $arr)
    {
        if (!is_array($arr)) {
            return NULL;
        }

        if (!is_array($path)) {
            return (isset($arr[$path]) ? $arr[$path] : NULL);
        }

        // traverse the array for our path
        $out = &$arr;
        foreach ($path as $key) {
            if (!isset($out[$key])) {
                return NULL;
            }

            $out = $out[$key];
        }
        return $out;
    }



    /**
     * helper to give us a parameterized path to create viewdefs for saving to file
     * @param string | array $path (path of keys to use for array)
     * @param mixed $data the data to place at that path
     * @return array the data in the correct path
     */
    public static function mapPathToArray($path, $data)
    {
        if (!is_array($path)) {
            return array($path => $data);
        }

        $arr = $data;
        while($key = array_pop($path)) {
            $arr = array($key => $arr);
        }
        return $arr;
    }

    /**
     * helper to give us a parameterized path find our data from our viewdefs
     * @param string | array $path (path of keys to use for array)
     * @param mixed $arr the array to search for the path
     * @return array| null the data in the correct path or null if a key isn't found.
     */
    public static function mapArrayToPath($path, $arr)
    {
        if (!is_array($arr)) {
            return NULL;
        }

        if (!is_array($path)) {
            return (isset($arr[$path]) ? $arr[$path] : NULL);
        }

        // traverse the array for our path
        $out = &$arr;
        foreach ($path as $key) {
            if (!isset($out[$key])) {
                return NULL;
            }

            $out = $out[$key];
        }
        return $out;
    }



    /**
     * helper to give us a parameterized path to create viewdefs for saving to file
     * @param string | array $path (path of keys to use for array)
     * @param mixed $data the data to place at that path
     * @return array the data in the correct path
     */
    public static function mapPathToArray($path, $data)
    {
        if (!is_array($path)) {
            return array($path => $data);
        }

        $arr = $data;
        while($key = array_pop($path)) {
            $arr = array($key => $arr);
        }
        return $arr;
    }

    /**
     * helper to give us a parameterized path find our data from our viewdefs
     * @param string | array $path (path of keys to use for array)
     * @param mixed $arr the array to search for the path
     * @return array| null the data in the correct path or null if a key isn't found.
     */
    public static function mapArrayToPath($path, $arr)
    {
        if (!is_array($arr)) {
            return NULL;
        }

        if (!is_array($path)) {
            return (isset($arr[$path]) ? $arr[$path] : NULL);
        }

        // traverse the array for our path
        $out = &$arr;
        foreach ($path as $key) {
            if (!isset($out[$key])) {
                return NULL;
            }

            $out = $out[$key];
        }
        return $out;
    }



    /**
     * helper to give us a parameterized path to create viewdefs for saving to file
     * @param string | array $path (path of keys to use for array)
     * @param mixed $data the data to place at that path
     * @return array the data in the correct path
     */
    public static function mapPathToArray($path, $data)
    {
        if (!is_array($path)) {
            return array($path => $data);
        }

        $arr = $data;
        while($key = array_pop($path)) {
            $arr = array($key => $arr);
        }
        return $arr;
    }

    /**
     * helper to give us a parameterized path find our data from our viewdefs
     * @param string | array $path (path of keys to use for array)
     * @param mixed $arr the array to search for the path
     * @return array| null the data in the correct path or null if a key isn't found.
     */
    public static function mapArrayToPath($path, $arr)
    {
        if (!is_array($arr)) {
            return NULL;
        }

        if (!is_array($path)) {
            return (isset($arr[$path]) ? $arr[$path] : NULL);
        }

        // traverse the array for our path
        $out = &$arr;
        foreach ($path as $key) {
            if (!isset($out[$key])) {
                return NULL;
            }

            $out = $out[$key];
        }
        return $out;
    }



    /**
     * helper to give us a parameterized path to create viewdefs for saving to file
     * @param string | array $path (path of keys to use for array)
     * @param mixed $data the data to place at that path
     * @return array the data in the correct path
     */
    public static function mapPathToArray($path, $data)
    {
        if (!is_array($path)) {
            return array($path => $data);
        }

        $arr = $data;
        while($key = array_pop($path)) {
            $arr = array($key => $arr);
        }
        return $arr;
    }

    /**
     * helper to give us a parameterized path find our data from our viewdefs
     * @param string | array $path (path of keys to use for array)
     * @param mixed $arr the array to search for the path
     * @return array| null the data in the correct path or null if a key isn't found.
     */
    public static function mapArrayToPath($path, $arr)
    {
        if (!is_array($arr)) {
            return NULL;
        }

        if (!is_array($path)) {
            return (isset($arr[$path]) ? $arr[$path] : NULL);
        }

        // traverse the array for our path
        $out = &$arr;
        foreach ($path as $key) {
            if (!isset($out[$key])) {
                return NULL;
            }

            $out = $out[$key];
        }
        return $out;
    }



    /**
     * Gets the list of view def array variable names
     *
     * @static
     * @return array
     */
    public static function getViewDefVars() {
        return self::$viewDefVars;
    }

    /**
     * Gets a single view def variable name
     *
     * This checks the def vars array first then the file name arrays. This
     * fallback allows for the use of the more standard naming for sidecar stuff
     * without having to redefine a bunch of vars that are the exact same as their
     * filename counterparts
     *
     * @static
     * @param string $view The name of the view to get the def var for
     * @return string The def variable name
     */
    public static function getViewDefVar($view) {
        // Try the view def var array first
        if (isset(self::$viewDefVars[$view])) {
            return self::$viewDefVars[$view];
        }

        // try the file name array second
        return self::getName($view);
    }

    public static function setViewDefVar($view, $defVar) {
        
    }

    /**
     * Gets a deployed metadata filename. This is generally called from a
     * DeployedMetaDataImplementation instance.
     *
     * @static
     * @param string $view The requested view type
     * @param string $module The module for this metadata file
     * @param string $type The type of metadata file location (custom, working, etc)
     * @return string
     */
    public static function getDeployedFileName($view, $module, $type = MB_CUSTOMMETADATALOCATION) {
        $type = strtolower($type);
        $paths = self::getPaths();
        $names = self::getNames();

        //In a deployed module, we can check for a studio module with file name overrides.
        $sm = StudioModuleFactory::getStudioModule($module);
        foreach($sm->sources as $file => $def) {
            if (!empty($def['view'])) {
                $names[$def['view']] = substr($file, 0, strlen($file) - 4);
            }
        }

        // BEGIN ASSERTIONS
		if (!isset($paths[$type])) {
			sugar_die("MetaDataFiles::getDeployedFileName(): Type $type is not recognized");
		}

		if (!isset($names[$view])) {
            sugar_die("MetaDataFiles::getDeployedFileName(): View $view is not recognized");
        }
		// END ASSERTIONS

		// Construct filename
        if (($viewType = self::getViewClient($view)) != '' && $viewType != 'base') {
            $viewPath = $viewType . '/' . self::$viewsPath;
        } else {
            $viewPath = '';
        }
		return $paths[$type] . 'modules/' . $module . '/metadata/' . $viewPath . $names[$view] . '.php' ;
    }

    /**
     * Gets an undeployed metadata filename. This is generally called from an
     * UndeployedMetaDataImplementation instance.
     *
     * @static
     * @param string $view The requested view
     * @param string $module The module for this metadata file
     * @param string $packageName The package for this metadata file
     * @param string $type The type of metadata file to get (custom, working, etc)
     * @return string
     */
    public static function getUndeployedFileName($view, $module, $packageName, $type = MB_BASEMETADATALOCATION) {
        $type = strtolower($type);

        // BEGIN ASSERTIONS
        if ($type != MB_BASEMETADATALOCATION && $type != MB_HISTORYMETADATALOCATION) {
            // just warn rather than die
            $GLOBALS['log']->warning("UndeployedMetaDataImplementation->getFileName(): view type $type is not recognized");
        }
        // END ASSERTIONS

        $names = self::getNames();

        // Get final filename path part
        if (($viewType = self::getViewClient($view)) != '' && $viewType != 'base') {
            $viewPath = $viewType . '/' . self::$viewsPath;
        } else {
            $viewPath = '';
        }

        switch ($type) {
            case MB_HISTORYMETADATALOCATION:
                return self::$paths[MB_WORKINGMETADATALOCATION] . 'modulebuilder/packages/' . $packageName . '/modules/' . $module . '/metadata/' . $viewPath . $names[$view] . '.php';
            default:
                // get the module again, all so we can call this method statically without relying on the module stored in the class variables
                $mb = new ModuleBuilder();
                return $mb->getPackageModule($packageName, $module)->getModuleDir() . '/metadata/' . $viewPath . $names[$view] . '.php';
        }
    }

    public static function getModuleMetaDataDefsWithReplacements($module, $defs) {
        if (!$module instanceof SugarBean) {
            $module = BeanFactory::getBean($module);
        }
        $replacements = array(
			"<object_name>"  => $module->object_name,
			"<_object_name>" => strtolower($module->object_name),
			"<OBJECT_NAME>"  => strtoupper($module->object_name),
			"<module_name>"  => $module->module_dir,
			'<_module_name>' => strtolower($module->module_dir),
		);
		return self::recursiveVariableReplace($defs, $replacements);
    }

    public static function recursiveVariableReplace($source, $replacements) {
        $ret = array();
		foreach ($source as $key => $val) {
			if (is_array($val)) {
	            $newkey = $key;
                $val = self::recursiveVariableReplace($val, $replacements);
	            $newkey = str_replace(array_keys($replacements), $replacements, $newkey);
	            $ret[$newkey] = $val;
	        } else {
                $newkey = $key;
			    $newval = $val;
                if(is_string($val)) {
                    $newkey = str_replace(array_keys($replacements), $replacements, $newkey);
                    $newval = str_replace(array_keys($replacements), $replacements, $newval);
                }
                $ret[$newkey] = $newval;
			}
        }
		return $ret;
    }

    /**
     * @param $view
     * @return mixed
     * hack for portal to use its own constants
     */
    public static function getMBConstantForView($view, $client = "base")
    {
        // Sometimes client is set to a defined null
        if (empty($client)) {
            $client = 'base';
        }

        $map = array(
            //BEGIN SUGARCRM flav=ent ONLY
            "portal" => array(
                'edit' => MB_PORTALEDITVIEW,
                'detail' => MB_PORTALDETAILVIEW,
                'search' => MB_PORTALSEARCHVIEW,
                'list' => MB_PORTALLISTVIEW
            ),
            //END SUGARCRM flav=ent ONLY
            'mobile' => array(
                'edit' => MB_WIRELESSEDITVIEW,
                'detail' => MB_WIRELESSDETAILVIEW,
                'list' => MB_WIRELESSLISTVIEW
            ),
            "base" => array(
                'edit' => MB_EDITVIEW,
                'detail' => MB_DETAILVIEW,
                'advanced_search' => MB_ADVANCEDSEARCH,
                'basic_search' => MB_BASICSEARCH,
                'list' => MB_LISTVIEW,
            ),
        );

        // view variable sent to the factory has changed: remove 'view' suffix
        // in case of further change
        $view = strtolower($view);
        if (substr_compare($view,'view',-4) === 0) {
            $view = substr($view,0,-4);
        }

        return isset($map[$client][$view]) ? $map[$client][$view] : $view;
    }
}
