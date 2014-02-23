<?php
/**
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

require_once 'modules/ModuleBuilder/parsers/constants.php';
require_once 'modules/ModuleBuilder/Module/StudioBrowser.php';
require_once 'modules/ModuleBuilder/parsers/views/PopupMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/views/SidecarListLayoutMetaDataParser.php';

/**
 * Converts custom "popupdefs.php" files to sidecar "selection-list.php".
 */
class SugarUpgradeConvertPopupListView extends UpgradeScript
{
    public $order = 7100;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.2';

    /**
     * Converts only listViewDefs.
     * Old format contains only default fields, current - default and enabled.
     */
    public function run()
    {
        if (!version_compare($this->from_version, '7.2', '<') || !version_compare($this->to_version, '7.2', '>=')) {
            return;
        }

        $sb = new StudioBrowser();
        $sb->loadModules();

        foreach ($sb->modules as $module => $defs) {
            if (!file_exists("custom/modules/$module/metadata/popupdefs.php")) {
                continue;
            }
            require "custom/modules/$module/metadata/popupdefs.php";

            if (!isset($popupMeta['listviewdefs'])) {
                continue;
            }

            $popupDefaultFieldNames = array();
            foreach ($popupMeta['listviewdefs'] as $key => $popupFieldDefs) {
                $popupDefaultFieldNames[] = isset($popupFieldDefs['name']) ?
                    $popupFieldDefs['name'] :
                    strtolower($key);
            }

            $sidecarParser = new SidecarListLayoutMetaDataParser(MB_SIDECARPOPUPVIEW, $module, null, 'base');
            $panel = $sidecarParser->getOriginalPanelDefs();
            $allFields = array_merge($sidecarParser->getAvailableFields(), $sidecarParser->getAdditionalFields());

            // Sidecar originally enabled and default fields.
            $newPanelDef = $panel[0]['fields'];
            // Reset all defaul fields to save available.
            array_walk($newPanelDef, function (&$val) {
                $val['default'] = false;
            });

            foreach ($popupDefaultFieldNames as $defaultFieldName) {
                // Populate with new default set.
                foreach ($newPanelDef as &$panelDef) {
                    if ($panelDef['name'] == $defaultFieldName) {
                        $panelDef['default'] = true;
                        continue 2;
                    }
                }
                // The field is hidden, populate the result defs with it.
                if (isset($allFields[$defaultFieldName])) {
                    $newPanelDef[] = array_merge(
                        $allFields[$defaultFieldName],
                        // Some valid fields have no name.
                        array('default' => true, 'enabled' => true, 'name' => $defaultFieldName)
                    );
                }
            }

            $sidecarParser->setPanelFields($newPanelDef);
            $sidecarParser->handleSave(false);
        }
    }
}
