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
 * Update site_user_id in contacts table.
 */
class SugarUpgradeUpdateSiteUserIdForContact extends UpgradeDBScript
{
    public $order = 9999;

    /**
     * Execute upgrade tasks
     * This script adds site_user_id in contacts table.
     * @see UpgradeScript::run()
     */
    public function run()
    {
        // run on all ent instances and pro-to-ent updrades
        if ($this->toFlavor('ent')) {
            $this->log('Updating site_user_id in contacts table');
            $result = $this->db->query("SELECT id FROM contacts WHERE site_user_id IS NULL AND deleted = 0");

            while ($row = $this->db->fetchByAssoc($result, false)) {
                $site_user_id = getSiteHash($row['id']);
                $sql = "UPDATE contacts SET site_user_id = ? WHERE id = ?";
                $this->executeUpdate($sql, [$site_user_id, $row['id']]);
            }
        }
    }
}
