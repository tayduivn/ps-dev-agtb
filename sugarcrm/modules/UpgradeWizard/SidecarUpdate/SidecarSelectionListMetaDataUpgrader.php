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
        $legacyListViewDefs = $this->upgrader->getUpgradeFileParams(
            "modules/{$this->module}/metadata/listviewdefs.php",
            $this->module,
            $this->client,
            $this->type
        );
        $listViewUpgrader = new SidecarListMetaDataUpgrader($this->upgrader, $legacyListViewDefs);
        // Load Sidecar ListView defs.
        $listViewUpgrader->setLegacyViewdefs();
        $listViewUpgrader->convertLegacyViewDefsToSidecar();
        $sidecarListViewDefs = $listViewUpgrader->getSidecarViewDefs();

        $this->logUpgradeStatus("Setting new {$this->client} selection-list internally for {$this->module}");
        $this->sidecarViewdefs[$this->module][$this->client]['view']['selection-list'] =
            $sidecarListViewDefs[$this->module][$this->client]['view']['list'];
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
