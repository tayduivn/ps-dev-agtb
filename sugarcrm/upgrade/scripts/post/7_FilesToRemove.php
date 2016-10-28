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
 * Class SugarUpgradeFilesToRemove
 *
 * Deletes the difference between the current files.md5 (upgrade to version) and the previous
 * one (upgrade from version).
 */
class SugarUpgradeFilesToRemove extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        $filesToRemove = json_decode(file_get_contents("{$this->context['extract_dir']}/filesToRemove.json"));

        $this->fileToDelete($filesToRemove);
    }
}
