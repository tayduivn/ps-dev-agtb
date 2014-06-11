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

/**
 * Purpose: When going from Pro -> Ent, to select all RLI records and move them over
 * to the forecast_worksheets table as Pro uses Opps for the worksheets table. This
 * causes the RLIs to show up on the table instead of the Opps (or nothing)
 *
 * Class SugarUpgradeRevenueLineItemCreateForecastWorksheetRecords
 */
class SugarUpgradeRevenueLineItemCreateForecastWorksheetRecords extends UpgradeScript
{
    public $order = 2120;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        $q = "SELECT '' as id,
                     rli.name,
                     rli.id as parent_id,
                     'RevenueLineItems' as parent_type,
                     1 as draft
                FROM revenue_line_items as rli
                LEFT JOIN forecast_worksheets fw
                ON rli.id = fw.parent_id AND fw.parent_type = 'RevenueLineItems'
                WHERE fw.id IS NULL";

        $this->log('Running Select SQL: ' . $q);
        $r = $this->db->query($q);

        $this->log('Found ' . $this->db->getRowCount($r) . ' RLIs to add to ForecastWorksheets');

        $this->insertRows($r);
    }
    /**
     * Process all the results and insert them back into the db
     *
     * @param resource $results
     */
    protected function insertRows($results)
    {
        $insertSQL = "INSERT INTO forecast_worksheets (
                        id,
                        name,
                        parent_id,
                        parent_type,
                        draft) values";

        /* @var $fw ForecastWorksheets */
        $fw = BeanFactory::getBean('ForecastWorksheets');

        while ($row = $this->db->fetchByAssoc($results)) {
            $row['id'] = create_guid();
            foreach ($row as $key => $value) {
                $row[$key] = $this->db->massageValue($value, $fw->getFieldDefinition($key));
            }

            $q = $insertSQL . ' (' . join(',', $row) . ');';

            $this->db->query($q);
        };
    }
}
