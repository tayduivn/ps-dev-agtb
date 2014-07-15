<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
/**
 * Set up FTS when upgrading CE->PRO
 */
class SugarUpgradeFTS extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if ($this->from_flavor == 'ce' && $this->toFlavor('pro')
            && $this->db->supports('fulltext') && $this->db->full_text_indexing_installed()
        ) {
            $this->db->full_text_indexing_setup();
        }

        //Always perform a clean re-index for the FTS after every upgrade
        require_once 'include/SugarSearchEngine/SugarSearchEngineFullIndexer.php';
        $indexer = new SugarSearchEngineFullIndexer();
        $indexer->initiateFTSIndexer();
        $this->log("FTS Indexer initiated.");
    }
}
