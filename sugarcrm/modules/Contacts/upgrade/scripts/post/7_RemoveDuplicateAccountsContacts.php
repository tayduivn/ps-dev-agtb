<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Remove duplicate relationship rows created by SP-1043 (Fixed in BR-1564)
 */
class SugarUpgradeRemoveDuplicateAccountsContacts extends UpgradeScript
{
    public $order = 7050;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        //Only applies to instances coming from 7.x < 7.2.1
        if (version_compare($this->from_version, '7.2.1', '<') && version_compare($this->from_version, '7.0.0', '>=')) {
            // Hardcoded for the accounts_contacts relationship for now

            //Create a temp_table to hold the non-dupe entries.
            $result = $this->db->query(
                "CREATE table accounts_contacts_tmp AS " .
                "SELECT * FROM accounts_contacts GROUP BY account_id, contact_id, deleted, primary_account " .
                "ORDER BY date_modified desc"
            );
            if ($result) {
                $uniqueRows = $this->db->getAffectedRowCount($result);
                //Nuke the existing relationship table...
                $this->db->query($this->db->truncateTableSQL("accounts_contacts"));

                //Copy the data from the temp back into the original table.
                $result = $this->db->query("INSERT INTO accounts_contacts SELECT * FROM accounts_contacts_tmp");

                //Now remove the temp table, only if the clone back into accounts_contacts worked.
                if ($result && $this->db->getAffectedRowCount($result) == $uniqueRows) {
                    $this->db->query($this->db->dropTableNameSQL("accounts_contacts_tmp"));
                } else {
                    $this->upgrader->log("Failed to copy from temp table back to accounts_contacts");
                }
            } else {
                $this->upgrader->log("Failed to create accounts_contacts temp table");
            }
        }
    }
}
