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
 * Class SugarUpgradeFixPrivateTeams
 */
class SugarUpgradeFixPrivateTeams extends UpgradeScript
{
    public $order = 7800;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if (version_compare($this->from_version, '7.8.0', '>=')) {
            // only need to run this upgrading from pre 7.8.0 versions
            return;
        }
        // Delete Orphan Private Teams
        $query = 'UPDATE teams SET deleted = 1 WHERE associated_user_id in (SELECT id FROM users WHERE deleted = 1)';
        $db = DBManagerFactory::getInstance();
        $db->query($query);
    }
}
