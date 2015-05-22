<?php
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

use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;

/**
 * Upgrade script to run a full FTS index.
 */
class SugarUpgradeRunFTSIndex extends UpgradeScript
{
    public $order = 9610;
    public $type = self::UPGRADE_CUSTOM;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (version_compare($this->from_version, '7.7', '<')) {
            $this->runFTSIndex();
        }
    }

    /**
     *
     * code base.
     */
    public function runFTSIndex()
    {
        try {
            SearchEngine::getInstance()->runFullReindex(true);
        } catch (Exception $e) {
            $this->log("SugarUpgradeRunFTSIndex: running full reindex got exceptions!");
        }

    }
}
