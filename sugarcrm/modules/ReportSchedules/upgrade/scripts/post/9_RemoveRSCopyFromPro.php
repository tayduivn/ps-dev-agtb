<?php
// FILE SUGARCRM flav!=ent ONLY
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
 * Class to remove 'Copy' button from nav menu for Report Schedules module in Pro
 */
class SugarUpgradeRemoveRSCopyFromPro extends UpgradeScript
{
    public $order = 9000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        $customFile = 'custom/modules/ReportSchedules/clients/base/views/record/record.php';
        if (version_compare($this->from_version, '8.2', '<') && is_file($customFile)) {
            require $customFile;
            if (!empty($viewdefs['ReportSchedules']['base']['view']['record'])) {
                $customMeta = $viewdefs['ReportSchedules']['base']['view']['record'];
                if ($this->removeCopyButton($customMeta)) {
                    $this->upgrader->backupFile($customFile);
                    write_array_to_file(
                        "viewdefs['ReportSchedules']['base']['view']['record']",
                        $customMeta,
                        $customFile
                    );
                    $this->log("Removed 'Copy' button from custom file: $customFile");
                }
            }
        }
    }

    /**
     * Removes 'Copy' button from custom metadata
     * @param array $customMeta
     * @return boolean
     */
    protected function removeCopyButton(&$customMeta)
    {
        if (!empty($customMeta['buttons'])) {
            foreach ($customMeta['buttons'] as $key1 => $buttons) {
                if (!empty($buttons['type']) && $buttons['type'] === 'actiondropdown' && !empty($buttons['buttons'])) {
                    foreach ($buttons['buttons'] as $key2 => $button) {
                        if (!empty($button['name']) && $button['name'] === 'duplicate_button') {
                            unset($customMeta['buttons'][$key1]['buttons'][$key2]);
                            return true;
                        }
                    }
                }
            }
        } else {
            return false;
        }
    }
}
