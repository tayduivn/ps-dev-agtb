<?php
require_once 'modules/ModuleBuilder/Module/StudioModuleFactory.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';
class MetaDataFiles
{
    /**
     * Path prefixes for metadata files
     *
     * @var array
     * @access public
     * @static
     */
    public static $paths = array(
        MB_BASEMETADATALOCATION => '' ,
        MB_CUSTOMMETADATALOCATION => 'custom/' ,
        MB_WORKINGMETADATALOCATION => 'custom/working/' ,
        MB_HISTORYMETADATALOCATION => 'custom/history/',
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
        MB_WIRELESSEDITVIEW       => 'editView' ,
        MB_WIRELESSDETAILVIEW     => 'detailView' ,
        MB_WIRELESSLISTVIEW       => 'list' ,
        MB_WIRELESSBASICSEARCH    => 'searchView' ,
        MB_WIRELESSADVANCEDSEARCH => 'searchView' ,
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        MB_PORTALEDITVIEW         => 'editView',
        MB_PORTALDETAILVIEW       => 'detailView',
        MB_PORTALLISTVIEW         => 'list',
        MB_PORTALSEARCHVIEW       => 'searchView',
        //END SUGARCRM flav=ent ONLY
    );

    public static $viewsPath = 'views/';

    public static function getNames() {
        return self::$names;
    }

    public static function getClients() {
        return self::$clients;
    }

    public static function getPaths() {
        return self::$paths;
    }

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
}
