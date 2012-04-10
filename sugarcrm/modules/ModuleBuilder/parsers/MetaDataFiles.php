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
        MB_WIRELESSDETAILVIEW     => 'detailView' ,
        MB_WIRELESSLISTVIEW       => 'list' ,
        MB_WIRELESSBASICSEARCH    => 'searchView' ,
        MB_WIRELESSADVANCEDSEARCH => 'searchView' ,
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        MB_PORTALEDITVIEW         => 'edit',
        MB_PORTALDETAILVIEW       => 'detailView',
        MB_PORTALLISTVIEW         => 'list',
        MB_PORTALSEARCHVIEW       => 'searchView',
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
            if (strpos($view, 'portal') !== false) {
                return 'portal';
            }

            if (strpos($view, 'wireless') !== false || strpos($view, 'mobile') !== false) {
                return 'mobile';
            }

            return 'base';
        }

        return '';
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

        switch ($type) {
            case MB_HISTORYMETADATALOCATION:
                return self::$paths[MB_WORKINGMETADATALOCATION] . 'modulebuilder/packages/' . $packageName . '/modules/' . $module . '/metadata/' . $names[$view] . '.php';
            default:
                // get the module again, all so we can call this method statically without relying on the module stored in the class variables
                $mb = new ModuleBuilder();
                return $mb->getPackageModule($packageName, $module)->getModuleDir() . '/metadata/' . $names[$view] . '.php';
        }
    }
}
