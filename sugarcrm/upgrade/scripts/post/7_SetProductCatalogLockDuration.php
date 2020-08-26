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
 * Disallow users to change the service duration for products that were marked
 * as services before the lock_duration field was added.
 */
class SugarUpgradeSetProductCatalogLockDuration extends UpgradeScript
{
    public $order = 7500;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if ($this->fromFlavor('ent') &&
            version_compare($this->from_version, '10.2.0', '<')
        ) {
            $this->log('Setting lock_duration to true for all products where service is true.');
            $this->db->query('UPDATE product_templates SET lock_duration = 1 WHERE service = 1');
        }
    }
}
