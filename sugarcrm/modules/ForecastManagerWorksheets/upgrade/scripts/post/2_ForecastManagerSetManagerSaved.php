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
 * Copyright 2004-2013 SugarCRM Inc. All rights reserved.
 */

class SugarUpgradeForecastManagerSetManagerSaved extends UpgradeScript
{
    public $order = 2195;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if (!version_compare($this->from_version, '7.1.5', '<')) {
            return;
        }

        // simple update
        $sql = "UPDATE forecast_manager_worksheets SET manager_saved = 1
                    WHERE id IN (
                      SELECT * FROM (
                        SELECT id FROM forecast_manager_worksheets
                        WHERE manager_saved = 0
                        AND draft = 1
                        AND deleted = 0
                        AND (((likely_case != likely_case_adjusted
                                AND likely_case != 0.000000 )
                              OR (best_case != best_case_adjusted
                                  AND best_case != 0.000000 )
                              OR (worst_case != worst_case_adjusted
                                  AND worst_case != 0.000000 )
                            )
                            OR assigned_user_id = modified_user_id
                        )
                      ) records);
                ";
        $this->db->query($sql);
    }
}
