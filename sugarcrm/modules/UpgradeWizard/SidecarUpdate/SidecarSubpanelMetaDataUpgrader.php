<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once 'include/MetaDataManager/MetaDataConverter.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarAbstractMetaDataUpgrader.php';

/**
 * Sidecar Subpanel ViewDef Upgrader
 * This class upgrades existing custom modules
 * and custom alterations to subpanel viewdefs
 * into the new sidecar subpanel viewdef format.
 */
class SidecarSubpanelMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{

    protected $isCustom = false;
    protected $moduleName;
    protected $client = 'base';
    protected $subpanelName = 'subpanel';
    protected $upgrader;
    protected $metaDataConverter;
    protected $newPath;
    public $newSubpanel = array();
    public $newLayoutDef = array();

    public function __construct($upgrader, $file)
    {
        $this->metaDataConverter = new MetaDataConverter();
        parent::__construct($upgrader, $file);
    }

    /**
     * Handles the actual upgrading for list metadata
     *
     * THIS WILL BE REDEFINED FOR SEARCH
     *
     * @return boolean
     */
    public function upgrade()
    {
        // Convert them
        $this->logUpgradeStatus(
            "converting {$this->client}[{$this->type}] legacy viewdefs for {$this->module}:{$this->viewtype} to Sugar 7 format"
        );
        if ($this->convertLegacyViewDefsToSidecar()) {
            // Save the new file and report it
            return $this->handleSave();
        }

        return;
    }

    /**
     * The actual legacy defs converter. For list it is simply taking the old
     * def array, looping over it, lowercasing the field names, adding that to
     * each iteration and saving that into a 'fields' array inside of the panels
     * array.
     *
     */
    public function convertLegacyViewDefsToSidecar()
    {
        @include $this->fullpath;

        if (empty($subpanel_layout)) {
            // if they don't have a subpanel we should log it and move on
            $this->logUpgradeStatus(sprintf("No view_defs for '%s'", $this->fullpath));
            return false;
        }
        $this->setUpgradeProperties();

        // it doesn't matter custom or not, if the new file exists we shouldn't convert
        if (file_exists($this->newPath)) {
            return false;
        }

        $newDirName = dirname($this->newPath);

        if (!is_dir($newDirName) && !sugar_mkdir($newDirName, null, true)) {
            $this->logUpgradeStatus(
                sprintf(
                    "Cannot create '%s'.",
                    $newDirName
                )
            );
        }


        $this->logUpgradeStatus(sprintf("Converting subpanel view defs for '%s'.", $this->fullpath));

        $this->newSubpanel = $this->metaDataConverter->fromLegacySubpanelsViewDefs($subpanel_layout);

        // Clean up client to mobile for wireless clients
        $this->logUpgradeStatus(
            sprintf(
                "Setting new '%s' subpanel view defs internally for '%s'",
                $this->client,
                $this->moduleName
            )
        );
        return true;
    }

    public function handleSave()
    {
        $this->save($this->newPath, $this->newSubpanel);
    }

    public function save($path, $content)
    {
        write_array_to_file(
            "viewdefs['{$this->moduleName}']['{$this->client}']['view']['{$this->subpanelName}']",
            $content,
            $path
        );
    }

    /**
     * This converts custom legacy subpanel layout defs to
     * the new style layoutdefs
     *
     * @param $module the module to convert all the subpanel layoutdefs for
     */
    public function convertLegacySubpanelLayoutDefsToSidecar($module)
    {
        static $conversionKeys = array(
            'override_subpanel_name' => 'override_subpanel_list_view',
            'get_subpanel_data' => 'link',
            'title_key' => 'label',
        );
        // we don't care about all the fields right now, we just care about
        // override_subpanel_name => override_subpanel_list_view
        // get_subpanel_data => link
        // title_key => label

        // get the modules current layoutdefs converted to sidecar, use file_exists to remove warnings from log
        if (file_exists("modules/{$module}/metadata/subpaneldefs.php")) {
            include "modules/{$module}/metadata/subpaneldefs.php";
        }
        if (file_exists("custom/modules/{$module}/metadata/subpaneldefs.php")) {
            include "custom/modules/{$module}/metadata/subpaneldefs.php";
        }
        if (file_exists("custom/modules/{$module}/Ext/Layoutdefs/layoutdefs.ext.php")) {
            include "custom/modules/{$module}/Ext/Layoutdefs/layoutdefs.ext.php";
        }

        // no layoutdefs, nothing to upgrade
        if (!isset($layout_defs[$module]['subpanel_setup'])) {
            return;
        }

        $subpaneldefs = $layout_defs[$module]['subpanel_setup'];
        unset($layout_defs);

        // loop file by file through the extensions dir and map the matched changes to convert each
        foreach (glob("custom/Extension/modules/{$module}/Ext/Layoutdefs/*.php") as $file) {
            include $file;
            if (empty($layout_defs[$module]['subpanel_setup'])) {
                unset($layout_defs);
                continue;
            }

            $extSubpanelDefs = $layout_defs[$module]['subpanel_setup'];
            foreach ($extSubpanelDefs as $name => $def) {
                $convertSubpanelDefs[$name] = array_intersect_key($def, $conversionKeys);
            }

            if (empty($convertSubpanelDefs)) {
                continue;
            }

            $newdefs = array();

            // find the subpaneldef that contains the $convertSubpanelDefs
            foreach ($subpaneldefs as $key => $def) {
                foreach ($def as $k => $v) {
                    if (!empty($convertSubpanelDefs[$key][$k]) && $convertSubpanelDefs[$key][$k] == $v) {
                        // convert this section to sidecar
                        $sidecarDef = $this->metaDataConverter->fromLegacySubpanelLayout($subpaneldefs[$key]);
                        // take out the key we are trying to create
                        if ($conversionKeys[$k] == 'link') {
                            $newdefs['context']['link'] = $sidecarDef['context']['link'];
                        } else {
                            $newdefs[$conversionKeys[$k]] = $sidecarDef[$conversionKeys[$k]];
                        }
                    }
                }
            }
            if (!empty($newdefs)) {
                $newdefs['layout'] = 'subpanel';
                $this->newLayoutDef = $newdefs;
                $path = "custom/Extension/modules/{$module}/Ext/clients/base/layouts/subpanels/" . basename($file);
                $this->writeSidecarSubpanelLayoutDefs($module, $path);
            }
        }
    }

    /**
     * Write out the new subpanel layout def
     * @param string $module - the module the layout is for
     * @param string $path - the path to save the new layoutdef
     */
    public function writeSidecarSubpanelLayoutDefs($module, $path)
    {
        if (!is_dir(dirname($path))) {
            sugar_mkdir(dirname($path), null, true);
        }
        write_array_to_file(
            "viewdefs['{$module}']['{$this->client}']['layout']['subpanels']['components'][]",
            $this->newLayoutDef,
            $path
        );
    }


    /**
     * Set the properties of the subpanel is being upgraded
     *
     * @param string $filename
     */
    protected function setUpgradeProperties()
    {
        $this->moduleName = $this->module;
        $this->newPath = $this->metaDataConverter->fromLegacySubpanelPath($this->fullpath, $this->client);
        $pathInfo = pathinfo($this->newPath);
        $this->subpanelName = $pathInfo['filename'];
    }

    public function setLegacyViewdefs()
    {
    }

}

