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
 * Set the account_id using the parent purchase for PLIs that were created
 * before that field was added
 */
class SugarUpgradeSetAccountForExistingPLIs extends UpgradeScript
{
    public $order = 7550;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if ($this->fromFlavor('ent') &&
            version_compare($this->from_version, '10.2.0', '<')
        ) {
            $this->log('Setting account_id for existing PLIs');

            $updateSql = 'UPDATE purchased_line_items pli ';
            $updateSql .= 'INNER JOIN purchases p ON pli.purchase_id = p.id ';
            $updateSql .= 'SET pli.account_id = p.account_id';
            $this->db->query($updateSql);
        }
    }
}
