<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

 /**
  * The ViewdefManager is a service designed to read and write viewdef files.
  */
namespace Sugarcrm\Sugarcrm\MetaData;

use Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions;

class ViewdefManager
{
    /**
     * Entrypoint to find the proper viewdef for a module/platform/view and load it for manipulation
     *
     * @param string $platform
     * @param string $module
     * @param string $view
     * @param bool $loadBase flag to load the base config instead of the custom one.
     * @return array viewdefs
     */
    public function loadViewdef($platform, $module, $view, $loadBase = false)
    {
        $returnViewdef = array();
        $paths = $this->getClientFiles([$platform], 'view', $module);
        $path = $this->findModuleViewdef($paths, $module, $view, $loadBase);
        
        //return viewdefs loaded in loadDef
        $viewdef = $this->loadDef($path['path']);

        //make sure the path we want exists in the loaded file
        $pathToCheck = array($module, $platform, 'view', $view);
        if (ArrayFunctions::keyExistsInPath($pathToCheck, $viewdef)) {
            $returnViewdef = $viewdef[$module][$platform]['view'][$view];
        }

        return $returnViewdef;
    }

    /**
     * Saves the viewdef out to the appropriate location
     *
     * @param string $viewdef
     * @param string $module
     * @param string $platform
     * @param string $view
     */
    public function saveViewdef($viewdef, $module, $platform, $view)
    {
        $path = "custom/modules/{$module}/clients/{$platform}/views/{$view}/{$view}.php";
        $fileName = "viewdefs['{$module}']['{$platform}']['view']['{$view}']";

        if (!file_exists($path)) {
            sugar_touch($path);
        }

        write_array_to_file($fileName, $viewdef, $path);
    }

    /**
     * Abstracts loading the viewdef
     *
     * @param string $path
     * @return array viewdefs
     */
    protected function loadDef($path)
    {
        $viewdefs = array();
        if (!empty($path)) {
            require $path;
        }

        return $viewdefs;
    }

    /**
     * abstracts away the static call to MetaDataFiles::getClientFiles
     *
     * @param string $platform
     * @param string $type
     * @param string $module
     * @return array
     */
    protected function getClientFiles($platform, $type, $module)
    {
        return \MetaDataFiles::getClientFiles($platform, $type, $module);
    }

    /**
     * Sorts through the output of MetaDataFiles::getClientFiles to find the proper definition file for a viewdef,
     * starting with custom first and working down from there.
     *
     * @param string $paths output from calling MetaDataFiles::getClientFiles
     * @param string $module - Quotes, Opportunities, etc
     * @param string $view - record, edit, detail, etc
     * @param bool $loadBase - flag to load base value over custom
     * @return string
     */
    public function findModuleViewdef($paths, $module, $view, $loadBase = false)
    {
        $returnPaths = null;

        foreach ($paths as $path) {
            //look for the view first, and make sure we're in the right module (sometimes not)
            if ($path['file'] == $view . '.php' && strpos($path['path'], $module) !== false) {
                $returnPaths = $path;
                // look for the custom def first, then load the default if we can't find a custom
                // unless loadBase is specified
                if (strpos($path['path'], 'custom') !== false && !$loadBase) {
                    $returnPaths = $path;
                    break;
                }
            }
        }
        return $returnPaths;
    }
}
