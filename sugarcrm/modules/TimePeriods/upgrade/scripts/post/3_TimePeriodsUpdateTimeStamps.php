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

class SugarUpgradeTimePeriodsUpdateTimeStamps extends UpgradeScript
{
    public $order = 3030;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        $this->log('Updating TimePeriod TimeStamp fields');
        $sql = "select id, start_date, end_date from timeperiods";
        $results = $this->db->query($sql);

        $dt = TimeDate::getInstance();
        $dt->setAlwaysDb(true);

        $updateSql = "UPDATE timeperiods SET start_date_timestamp = '%d', end_date_timestamp = '%d' where id = '%s'";
        while ($row = $this->db->fetchRow($results)) {
            $this->db->query(
                sprintf(
                    $updateSql,
                    strtotime($row['start_date'] . ' 00:00:00'),
                    strtotime($row['end_date'] . ' 23:59:59'),
                    $row['id']
                )
            );
        }

        $dt->setAlwaysDb(false);

        $this->log('Done Updating TimePeriod TimeStamp fields');
    }
}
