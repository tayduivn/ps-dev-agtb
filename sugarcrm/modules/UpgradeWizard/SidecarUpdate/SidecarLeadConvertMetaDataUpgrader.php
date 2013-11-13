<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarAbstractMetaDataUpgrader.php';
require_once 'modules/Leads/ConvertLayoutMetadataParser.php';

/**
 * Sidecar Lead Convert Metadata Upgrader
 * This class upgrades existing custom alterations
 * to the lead convert metadata into the new sidecar
 * lead convert metadata format.
 */
class SidecarLeadConvertMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    protected $newPath = 'custom/modules/Leads/clients/base/layouts/convert-main/convert-main.php';
    protected $metadataParser;

    /**
     * Check if we should continue with the upgrade
     *
     * @return bool
     */
    public function upgradeCheck()
    {
        //if the new file exists, we shouldn't convert
        if (file_exists($this->newPath)) {
            $this->logUpgradeStatus("Skipping upgrade, new file already exists: {$this->newPath}");
            return false;
        }

        return true;
    }

    /**
     * Pull the legacy viewdefs from the custom file
     */
    public function setLegacyViewdefs()
    {
        if (file_exists($this->fullpath)) {
            $this->logUpgradeStatus("legacy file being read: {$this->fullpath}");
            $viewdefs = null;
            include $this->fullpath;
            $this->logUpgradeStatus("legacy file read: {$this->fullpath}");
            if (empty($viewdefs)) {
                //if they don't have a convert viewdef we should log it and move on
                $this->logUpgradeStatus("No view_defs for '{$this->fullpath}'");
            }
            $this->legacyViewdefs = $viewdefs;
        }
    }

    /**
     * Convert old lead convert viewdefs over to the new format
     */
    public function convertLegacyViewDefsToSidecar()
    {
        if (empty($this->legacyViewdefs)) {
            return;
        }
        $this->logUpgradeStatus("Converting lead conversion view defs for '$this->fullpath'");

        $orderedModules = array('Contacts', 'Accounts', 'Opportunities');
        $moduleList = array();

        //pull out the ordered ones first...
        foreach ($orderedModules as $module) {
            if (isset($this->legacyViewdefs[$module])) {
                $moduleList[] = $this->convertSingleModuleDef($module, $this->legacyViewdefs[$module]);
                unset($this->legacyViewdefs[$module]);
            }
        }

        //...now iterate over the rest...
        foreach($this->legacyViewdefs as $key => $oldDef) {
            if ($this->getMetadataParser()->isModuleAllowedInConvert($key)) {
                $moduleList[] = $this->convertSingleModuleDef($key, $oldDef);
            }
        }

        //...and then merge them with default defs and apply cross module business logic
        $moduleList = $this->getMetadataParser()->mergeConvertDefs($moduleList);

        $this->sidecarViewdefs = array('modules' => $moduleList);

        $this->logUpgradeStatus("Converted lead conversion view defs for '$this->fullpath'");
    }

    /**
     * Convert old convert lead module def to new format (pull out just settings apply to 7.0)
     *
     * @param $module
     * @param $oldDef
     * @return array
     */
    protected function convertSingleModuleDef($module, $oldDef)
    {
        if (isset($oldDef['ConvertLead'])) {
            $oldDef = $oldDef['ConvertLead'];
        }

        $newDef = array(
            'module' => $module,
        );
        //move over specific settings from old def
        foreach(array('required', 'copyData') as $setting) {
            if (isset($oldDef[$setting])) {
                $newDef[$setting] = $oldDef[$setting];
            }
        }

        return $newDef;
    }

    protected function getMetadataParser()
    {
        if (empty($this->metadataParser)) {
            $this->metadataParser = new ConvertLayoutMetadataParser("Contacts");
        }
        return $this->metadataParser;
    }

    /**
     * Save the new format
     *
     * @return bool|int
     */
    public function handleSave()
    {
        return $this->handleSaveArray("viewdefs['Leads']['base']['layout']['convert-main']", $this->newPath);
    }
}

