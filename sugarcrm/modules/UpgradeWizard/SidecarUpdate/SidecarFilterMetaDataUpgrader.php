<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
// This will need to be pathed properly when packaged
require_once 'SidecarAbstractMetaDataUpgrader.php';
require_once 'modules/ModuleBuilder/Module/StudioModuleFactory.php';
require_once 'modules/ModuleBuilder/parsers/views/DeployedSearchMetaDataImplementation.php';

class SidecarFilterMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    /**
     * Should we delete pre-upgrade files?
     * Not deleting searchviews since we may need them for popups in subpanels driven by BWC module.
     * See BR-1044
     * @var bool
     */
    public $deleteOld = false;

    /**
     * Check if we actually want to upgrade this file
     * @return boolean
     */
    public function upgradeCheck()
    {
        $target = $this->getNewFileName($this->viewtype);
        if(file_exists($target)) {
            // if we already have the target, skip the upgrade
            return false;
        }
        return true;
    }

    /**
     * Move the functionalities to DeployedSearchMetaDataImplementation::convertLegacyViewDefsToSidecar().
     * Use $this->handleSave() to convert and save the files.
     *
     * @override SidecarAbstractMetaDataUpgrader::convertLegacyViewDefsToSidecar()
     */
    public function convertLegacyViewDefsToSidecar()
    {
    }
    /**
     * Handling the file conversion.
     * @override SidecarAbstractMetaDataUpgrader::handleSave()
     */
    public function handleSave()
    {
        // Get what we need to make our new files
        $viewName = $this->views[$this->client . $this->viewtype];
        $module = $this->getNormalizedModuleName();
        //Translate the viewName, only handling the base filter case
        if ($viewName == MB_SEARCHVIEW) {
            $viewName = MB_BASICSEARCH;
        } else {
            return array();
        }
        $impl = new DeployedSearchMetaDataImplementation($viewName, $module);
        return $impl->createSidecarFilterDefsFromLegacy();
    }

    public function getNewFileName($viewname)
    {
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        // Cut off metadata/searchdefs.php
        $dirname = dirname(dirname($this->fullpath));
        return $dirname . "/clients/$client/filters/default/default.php";
    }

}