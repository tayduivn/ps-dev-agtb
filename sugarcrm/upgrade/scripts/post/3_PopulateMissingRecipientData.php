<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
require_once('modules/Emails/Email.php');
/**
 * MAR-3031 was fixed in 7.6.1, and cleans up html syntax so emails can be displayed correctly in sugar 7.6.0+
 * This repair script should run when coming in from 7.6.0 to decode html characters in html field
 */
class SugarUpgradePopulateMissingRecipientData extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_DB;
    public $version = '7.7.0';

    public function run()
    {

        //check to see if data is missing, if not don't run
        $recordsToUpdate = $this->fetchBrokenRecords();
        if (empty($recordsToUpdate)) {
            return;
        } else {
            $this->populateRecipientData($recordsToUpdate);
        }
    }

    protected function fetchBrokenRecords()
    {
        // find troubled rows we check for '' (mysql/mssql/db2) as well as is null (oci)
        $query = "SELECT et.email_id, et.from_addr, et.to_addrs, ea.email_address, eear.address_type";
        $query .= " FROM emails_text et LEFT JOIN emails_email_addr_rel eear on eear.email_id = et.email_id";
        $query .= " LEFT JOIN email_addresses ea on ea.id = eear.email_address_id ";
        $query .= " WHERE et.from_addr is null or et.to_addrs is null or et.from_addr = '' or et.to_addrs = '' and et.deleted = 0 ";


        $result = $this->db->query($query);
        $updateInfo = array();

        while ($row = $this->db->fetchByAssoc($result, false)) {
            $row = array_change_key_case($row);

            if ($row['address_type'] == 'from' && $row['from_addr'] == '') {
                $updateInfo[$row['email_id']]['from_addr'] = addslashes($row['email_address']);
            }

            if ($row['address_type'] == 'to' && $row['to_addrs'] == '') {
                if (!empty($updateInfo[$row['email_id']]['to_addr'])) {
                    $updateInfo[$row['email_id']]['to_addrs'] .= ', ' . addslashes($row['email_address']);
                } else {
                    $updateInfo[$row['email_id']]['to_addrs'] = addslashes($row['email_address']);
                }
            }
        }

        return $updateInfo;
    }


    protected function populateRecipientData($updateInfo = array())
    {
        foreach ($updateInfo as $q_key => $q_val) {

            $set_query = '';
            if (!empty($q_val['from_addr'])) {
                $set_query = " from_addr = '{$q_val['from_addr']}' ";
            }
            if (!empty($q_val['to_addrs'])) {
                if (!empty($set_query)) {
                    $set_query .= ", to_addrs = '{$q_val['to_addrs']}'";
                } else {
                    $set_query = " to_addrs = '{$q_val['to_addrs']}'";
                }
            }

            if (!empty($set_query)) {
                $update_query = 'UPDATE emails_text set ' . $set_query . " WHERE email_id = '$q_key'";
                $this->db->query($update_query);
            }
        }
    }
}