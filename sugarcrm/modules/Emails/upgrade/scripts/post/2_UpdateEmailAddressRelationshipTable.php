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
 * Update Email Address Relationship Table
 */
class SugarUpgradeUpdateEmailAddressRelationshipTable extends UpgradeScript
{
    public $order = 2100;
    public $type = self::UPGRADE_DB;

    /**
     * {@inheritdoc}
     *
     * Updates all existing emails_email_addr_rel records such that the 'bean_id' column gets the existing value in the
     * email_address_id column and the 'bean_type' column gets the value: 'EmailAddresses'.
     *
     * This upgrade script only runs when upgrading from a version prior to 7.10
     */
    public function run()
    {
        if (!version_compare($this->from_version, '7.10', '<')) {
            return;
        }
        $this->log('Setting all existing email addresses in emails_email_addr_rel as bean type: EmailAddresses');
        $sql = "UPDATE emails_email_addr_rel SET bean_type='EmailAddresses', bean_id=email_address_id";
        $this->runUpdate($sql);
    }

    /**
     * Executes an update query and logs the number of affected rows or the error.
     *
     * @param string $sql The query to execute.
     */
    protected function runUpdate($sql)
    {
        try {
            $rows = DBManagerFactory::getConnection()->executeUpdate($sql);
            $this->log("Number of affected rows: {$rows}");
        } catch (DBALException $error) {
            $this->log("Error: {$error}");
        }
    }
}
