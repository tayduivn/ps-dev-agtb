<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Fix up primary flagged relationships if there are more than one "primary" record
 */
class SugarUpgradePrimaryRelationshipAdd extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if (version_compare($this->from_version, '7.1.5', '<')) {
            // Hardcoded for the accounts_contacts relationship for now
            // Everybody becomes the primary account
            $this->db->query("UPDATE accounts_contacts "
                    . "SET primary_account = 1 "
                    . "WHERE deleted = 0"
            );

            // Find relationships where there are more than one "primary" record
            while (true) {
                $ret = $this->db->limitQuery("SELECT COUNT(id) duplicates, contact_id child_id "
                        . "FROM accounts_contacts "
                        . "WHERE primary_account = 1 "
                        . "AND deleted = 0 "
                        . "GROUP BY contact_id "
                        . "HAVING COUNT(id) > 1", 0, 200
                );
                $fixupRecords = array();

                while ($row = $this->db->fetchByAssoc($ret)) {
                    $fixupRecords[] = $this->db->quote($row['child_id']);
                }
                if (empty($fixupRecords)) {
                    // We have fixed everything
                    break;
                }

                // Find the most recent record for child and we'll unset the rest of them as primary
                $ret = $this->db->query("SELECT id, contact_id child_id, date_modified "
                        . "FROM accounts_contacts WHERE "
                        . "contact_id IN ('" . implode("','", $fixupRecords) . "') "
                        . "AND deleted = 0 ORDER BY date_modified DESC"
                );

                $fixedRecords = array();
                while ($row = $this->db->fetchByAssoc($ret)) {
                    if (!isset($fixedRecords[$row['child_id']])) {
                        // First time we've found this child
                        $fixedRecords[$row['child_id']] = true;
                        $this->db->query("UPDATE accounts_contacts "
                                . "SET primary_account = 0 "
                                . "WHERE deleted = 0 "
                                . "AND id <> '" . $row['id'] . "' "
                                . "AND contact_id = '" . $row['child_id'] . "'"
                        );
                    }
                }
            }
        }
    }
}
