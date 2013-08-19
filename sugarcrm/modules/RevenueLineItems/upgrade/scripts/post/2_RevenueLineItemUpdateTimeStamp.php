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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

class SugarUpgradeRevenueLineItemUpdateTimeStamp extends UpgradeScript
{
    public $order = 2180;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        $this->log('Updating Revenue Line Items TimeStamp fields');
        $sql = "select id, date_closed from revenue_line_items where deleted = 0";
        $results = $this->db->query($sql);

        $updateSql = "UPDATE revenue_line_items SET date_closed_timestamp = '%d' where id = '%s'";
        while ($row = $this->db->fetchRow($results)) {
            $this->db->query(
                sprintf(
                    $updateSql,
                    strtotime($row['date_closed']),
                    $row['id']
                )
            );
        }

        $this->log('Done Updating Revenue Line Items TimeStamp fields');
    }
}
