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
            $countUniqueQuery = "SELECT count(*) FROM "
                . "(SELECT DISTINCT account_id, contact_id, primary_account, deleted "
                . "FROM accounts_contacts) a";

            //Create a temp_table to hold the non-dupe entries.
            $columns = $this->db->get_columns('accounts_contacts');
            $indices = $this->db->get_indices('accounts_contacts');
            $cols = implode(',', array_keys($columns));
            $createTempTableQuery = $this->db->createTableSQLParams('accounts_contacts_tmp', $columns, $indices);
            $copyDataQuery = "INSERT INTO accounts_contacts_tmp ({$cols}) (SELECT {$cols} FROM accounts_contacts)";
            $deDupeQuery = "select t1.id FROM accounts_contacts t1, accounts_contacts t2 "
                . "WHERE t1.id > t2.id AND t1.contact_id = t2.contact_id AND t1.account_id = t2.account_id "
                . "AND t1.deleted = t2.deleted AND t1.primary_account = t2.primary_account";
            $result = $this->db->query($createTempTableQuery);
            if ($result) {
                $result = $this->db->query($copyDataQuery);
            }
            if ($result) {
                $uniqueRows = $this->db->getOne($countUniqueQuery);

                //De-Dupe the existing table
                $res = $this->db->query($deDupeQuery);
                while ($row = $this->db->fetchRow($res)) {
                    $this->db->query("delete from accounts_contacts where id={$this->db->quoted($row['id'])}");
                }

                //Copy the data from the temp back into the original table.
                $finalRows = $this->db->getOne("SELECT count(*) FROM accounts_contacts");

                //Now remove the temp table, only if the clone back into accounts_contacts worked.
                if ($finalRows == $uniqueRows) {
                    $this->db->query($this->db->dropTableNameSQL("accounts_contacts_tmp"));
                } else {
                    $this->upgrader->log("Failed to De-dupe the accounts_contacts table, "
                                       . "Data is preserved in the accounts_contacts_tmp table.");
                }
            } else {
                $this->upgrader->log("Failed to create accounts_contacts temp table");
            }
        }
    }
}
