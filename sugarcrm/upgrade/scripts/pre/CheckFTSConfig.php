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

use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;

/**
 * Check that the Sugar FTS Engine configuration is valid
 */
class SugarUpgradeCheckFTSConfig extends UpgradeScript
{
    public $order = 200;
    public $version = '7.1.5';

    public function run()
    {
        global $sugar_config;

        $ftsConfig = isset($sugar_config['full_text_engine']) ? $sugar_config['full_text_engine'] : null;
        // Check that Elastic info is set (only currently supported search engine)
        if (empty($ftsConfig) || empty($ftsConfig['Elastic']) ||
            empty($ftsConfig['Elastic']['host']) || empty($ftsConfig['Elastic']['port'])
        ) {
            // error implies fail
            $this->error('Elastic Full Text Search engine needs to be configured on this Sugar instance prior to upgrade.');
            $this->error('Access Full Text Search configuration under Administration > Search.');
        } else {
            // Test Elastic FTS connection
            $searchEngine = SearchEngine::newEngine('Elastic', $ftsConfig['Elastic']);
            $ftsStatus = $searchEngine->verifyConnectivity(false);

            if ($ftsStatus != Client::CONN_SUCCESS) {
                $this->error('Connection test for Elastic Full Text Search engine failed.  Check your FTS configuration.');
                $this->error('Access Full Text Search configuration under Administration > Search.');
            }
        }
    }

}
