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
 * Applies default character set and collation upgrade in MySQL (only)
 * from: utf8:utf8_general_ci  to: utf8mb4:utf8mb4_general_ci
 */
class SugarUpgradeApplyDbCollation extends UpgradeScript
{
    public $order = 1010;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if (version_compare($this->from_version, '8.1.0', '>=')) {
            return;
        }

        // This upgrade applies to MySQL Only
        $db = DBManagerFactory::getInstance();
        if ($db->dbType !== 'mysql') {
            return;
        }

        // Upgrade is applied to the Database and all existing tables
        $collation = 'utf8mb4_general_ci';
        $db->setCollation($collation);
    }
}
