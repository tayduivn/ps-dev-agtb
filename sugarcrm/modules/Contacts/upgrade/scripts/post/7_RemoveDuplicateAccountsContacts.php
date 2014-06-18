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

            $countUniqueQuery = "SELECT count(*) FROM "
                . "(SELECT DISTINCT account_id, contact_id, primary_account, deleted "
                . "FROM accounts_contacts) AS a";

            //Create a temp_table to hold the non-dupe entries.
            if ($this->db->dbName == "IBM_DB2") {
                $createTempTableQuery = "CREATE TABLE accounts_contacts_tmp LIKE accounts_contacts";
                $copyDataQuery = "INSERT INTO accounts_contacts_tmp (SELECT * FROM accounts_contacts)";
                $deDupeQuery = "DELETE FROM ( "
                             . "SELECT ROWNUMBER() OVER (PARTITION BY account_id, contact_id, primary_account, deleted) "
                             . "AS rn FROM accounts_contacts) AS row WHERE rn > 1";
            } else {
                $createTempTableQuery = "CREATE table accounts_contacts_tmp AS SELECT * FROM accounts_contacts";
                $deDupeQuery = "DELETE t1 FROM accounts_contacts t1, accounts_contacts t2 "
                 . "WHERE t1.id > t2.id AND t1.contact_id = t2.contact_id AND t1.account_id = t2.account_id "
                 . "AND t1.deleted = t2.deleted AND t1.primary_account = t2.primary_account";
            }
            $result = $this->db->query($createTempTableQuery);
            if (!empty($copyDataQuery) && $result) {
                $result = $this->db->query($copyDataQuery);
            }
            if ($result) {
                $uniqueRows = $this->db->getOne($countUniqueQuery);

                //De-Dupe the existing table
                $this->db->query($deDupeQuery);

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
