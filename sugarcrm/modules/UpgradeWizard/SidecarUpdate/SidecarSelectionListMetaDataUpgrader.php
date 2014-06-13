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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarAbstractMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarListMetaDataUpgrader.php';

class SidecarSelectionListMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    public $deleteOld = false;

    public function convertLegacyViewDefsToSidecar()
    {
        $filedata = $this->upgrader->getUpgradeFileParams(
            "modules/{$this->module}/metadata/listviewdefs.php",
            $this->module,
            $this->client,
            $this->type,
            $this->package,
            $this->deployed
        );

        // If by some chance the getter returned a false, stop
        if (!$filedata) {
            $this->logUpgradeStatus("No upgrade file params found for {$this->module} selection-list");
            return;
        }

        $upgrader = new SidecarListMetaDataUpgrader($this->upgrader, $filedata);

        // "Upgrade" list view defs
        $upgrader->setLegacyViewdefs();
        $upgrader->convertLegacyViewDefsToSidecar();
        // Get the converted defs
        $sidecarViewDefs = $upgrader->getSidecarViewDefs();
        if ($sidecarViewDefs) {
            // Twitterizing the assignment of the converted list view defs
            $this->logUpgradeStatus("Setting new {$this->client} selection-list internally for {$this->module}");
            $converted = $sidecarViewDefs[$this->module][$this->client]['view']['list'];
            $newdefs[$this->getNormalizedModuleName()][$this->client]['view']['selection-list'] = $converted;
            $this->sidecarViewdefs = $newdefs;
        } else {
            $this->logUpgradeStatus("No selection-list metadata found for {$this->module}");
            return;
        }
    }

    /**
     * Check if we actually want to upgrade this file.
     *
     * @return boolean
     */
    public function upgradeCheck()
    {
        // Custom files are converted by the upgrade script "7_ConvertPopupListView.php".
        if ($this->client != 'base' || $this->type != 'base') {
            return false;
        }
        return true;
    }

    /**
     * Stub, sidecar ListView defs are used instead of legacy defs in converting.
     */
    public function setLegacyViewdefs()
    {

    }

}
