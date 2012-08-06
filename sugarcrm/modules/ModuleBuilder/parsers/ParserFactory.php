<?php
if (! defined ( 'sugarEntry' ) || ! sugarEntry)
    die ( 'Not A Valid Entry Point' ) ;

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once 'modules/ModuleBuilder/parsers/constants.php' ;

class ParserFactory
{
    // BEGIN SUGARCRM flav=ent ONLY
    /**
     * @param $view
     * @return mixed
     * hack for portal to use its own constants
     */
    protected static function _helperConvertToViewConstant($view)
    {
        $map = array(
            'edit' => MB_PORTALEDITVIEW,
            'detail' => MB_PORTALDETAILVIEW,
            'search' => MB_PORTALSEARCHVIEW,
            'list' => MB_PORTALLISTVIEW
        );

        // view variable sent to the factory has changed: remove 'view' suffix
        // in case of further change
        $view = strtolower($view);
        if (substr_compare($view,'view',-4) === 0) {
            $view = substr($view,0,-4);
        }
        return $map[$view];
    }
    //END SUGARCRM flav=ent ONLY

    /*
     * Create a new parser
     *
     * @param string $view          The view, for example EditView or ListView. For search views, use advanced_search or basic_search
     * @param string $moduleName    Module name
     * @param string $packageName   Package name. If present implies that we are being called from ModuleBuilder
     * @param string $client        The view client (e.g. portal, wireless, etc.)
     * @return AbstractMetaDataParser
     */

    public static function getParser ( $view , $moduleName , $packageName = null , $subpanelName = null, $client = '' )
    {
        $GLOBALS [ 'log' ]->info ( "ParserFactory->getParser($view,$moduleName,$packageName,$subpanelName,$client )" ) ;
		$sm = null;
        $lView = strtolower ( $view );
        if ( empty ( $packageName ) || ( $packageName == 'studio' ) )
        {
            $packageName = null ;
            //For studio modules, check for view parser overrides
            $parser = self::checkForStudioParserOverride($view, $moduleName, $packageName);
            if ($parser) return $parser;
            $sm = StudioModuleFactory::getStudioModule($moduleName);
            //If we didn't find a specofic parser, see if there is a view to type mapping
            foreach($sm->sources as $file => $def)
            {
                if (!empty($def['view']) && $def['view'] == $view && !empty($def['type']))
                {
                    $lView = strtolower($def['type']);
                    break;
                }
            }
        }

        switch ( $lView)
        {
            case MB_EDITVIEW :
            case MB_DETAILVIEW :
            case MB_QUICKCREATE :
                require_once 'modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php' ;
                return new GridLayoutMetaDataParser ( $view, $moduleName, $packageName ) ;
            //BEGIN SUGARCRM flav=pro || flav=sales ONLY
            case MB_WIRELESSEDITVIEW :
            case MB_WIRELESSDETAILVIEW :
            //BEGIN SUGARCRM flav=ent ONLY
            case MB_PORTALDETAILVIEW:
            case MB_PORTALEDITVIEW:
            //END SUGARCRM flav=ent ONLY
                if (empty($client))
                {
                    $client = MB_WIRELESS;
                    //BEGIN SUGARCRM flav=ent ONLY
                    if ($lView == MB_PORTALDETAILVIEW || $lView == MB_PORTALEDITVIEW)
                    {
                        $client = MB_PORTAL;
                    }
                    //END SUGARCRM flav=ent ONLY
                }
                require_once 'modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php' ;
                return new SidecarGridLayoutMetaDataParser ( $view, $moduleName, $packageName, $client ) ;
            case MB_WIRELESSLISTVIEW:
            //BEGIN SUGARCRM flav=ent ONLY
            case MB_PORTALLISTVIEW:
            //END SUGARCRM flav=ent ONLY
                // Handle client settings if we can
                if (empty($client))
                {
                    $client = MB_WIRELESS;
                    //BEGIN SUGARCRM flav=ent ONLY
                    if ($lView == MB_PORTALLISTVIEW)
                    {
                        $client = MB_PORTAL;
                    }
                    //END SUGARCRM flav=ent ONLY
                }
                require_once 'modules/ModuleBuilder/parsers/views/SidecarListLayoutMetaDataParser.php' ;
                return new SidecarListLayoutMetaDataParser($view, $moduleName, $packageName, $client);
            //END SUGARCRM flav=pro || flav=sales ONLY
            case MB_BASICSEARCH :
            case MB_ADVANCEDSEARCH :
            //BEGIN SUGARCRM flav=pro || flav=sales ONLY
            case MB_WIRELESSBASICSEARCH :
            case MB_WIRELESSADVANCEDSEARCH :
                // Make sure we have the right client
                if ($lView == MB_WIRELESSBASICSEARCH || $lView == MB_WIRELESSADVANCEDSEARCH)
                {
                    $client = MB_WIRELESS;
                }
            //END SUGARCRM flav=pro || flav=sales ONLY
                require_once 'modules/ModuleBuilder/parsers/views/SearchViewMetaDataParser.php' ;
                return new SearchViewMetaDataParser ( $view, $moduleName, $packageName, $client ) ;
            case MB_LISTVIEW :
                if ($subpanelName == null)
                {
                    require_once 'modules/ModuleBuilder/parsers/views/ListLayoutMetaDataParser.php' ;
                    return new ListLayoutMetaDataParser ( MB_LISTVIEW, $moduleName, $packageName ) ;
                } else
                {
                    require_once 'modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php' ;
                    return new SubpanelMetaDataParser ( $subpanelName, $moduleName, $packageName ) ;
                }
            case MB_DASHLET :
            case MB_DASHLETSEARCH :
                require_once 'modules/ModuleBuilder/parsers/views/DashletMetaDataParser.php' ;
                return new DashletMetaDataParser($view, $moduleName, $packageName  );
            case MB_POPUPLIST :
            case MB_POPUPSEARCH :
                require_once 'modules/ModuleBuilder/parsers/views/PopupMetaDataParser.php' ;
                return new PopupMetaDataParser($view, $moduleName, $packageName  );
            case MB_LABEL :
                require_once 'modules/ModuleBuilder/parsers/parser.label.php' ;
                return new ParserLabel ( $moduleName, $packageName ) ;
            case MB_VISIBILITY :
                require_once 'modules/ModuleBuilder/parsers/parser.visibility.php' ;
                return new ParserVisibility ( $moduleName, $packageName ) ;
            default :
                $parser = self::checkForParserClass($view, $moduleName, $packageName);
                if ($parser)
                    return $parser;

        }

        $GLOBALS [ 'log' ]->fatal ("ParserFactory: cannot create ModuleBuilder Parser $view" ) ;

    }

    protected static function checkForParserClass($view, $moduleName, $packageName, $nameOverride = false)
    {
        $prefix = '';
        if(!is_null ( $packageName )){
            $prefix = empty($packageName) ? 'build' :'modify';
        }
        $fileNames = array(
            "custom/modules/$moduleName/parsers/parser." . strtolower ( $prefix . $view ) . ".php",
            "modules/$moduleName/parsers/parser." . strtolower ( $prefix . $view ) . ".php",
            "custom/modules/ModuleBuilder/parsers/parser." . strtolower ( $prefix . $view ) . ".php",
            "modules/ModuleBuilder/parsers/parser." . strtolower ( $prefix . $view ) . ".php",
        );
        foreach($fileNames as $fileName)
        {
            if (file_exists ( $fileName ))
            {
                require_once $fileName ;
                $class = 'Parser' . $prefix . ucfirst ( $view ) ;
                if (class_exists ( $class ))
                {
                    $GLOBALS [ 'log' ]->debug ( 'Using ModuleBuilder Parser ' . $fileName ) ;
                    $parser = new $class ( ) ;
                    return $parser ;
                }
            }
        }
        return false;
    }

    protected static function checkForStudioParserOverride($view, $moduleName, $packageName)
    {
        require_once('modules/ModuleBuilder/Module/StudioModuleFactory.php');
        $sm = StudioModuleFactory::getStudioModule($moduleName);
        foreach($sm->sources as $file => $def)
        {
            if (!empty($def['view']) && $def['view'] == strtolower($view) && !empty($def['parser']))
            {
                $pName = $def['parser'];
                $path = "module/ModuleBuilder/parsers/views/{$pName}.php";
                if (file_exists("custom/$path"))
                    require_once("custom/$path");
                else if (file_exists($path))
                    require_once($path);
                if (class_exists ( $pName ))
                    return new $pName($view, $moduleName, $packageName);
                //If it wasn't defined directly, check for a generic parser name for the view
                $parser = self::checkForParserClass($view, $moduleName, $packageName);
                if ($parser)
                    return $parser;
            }
        }
        return false;
    }

}
?>