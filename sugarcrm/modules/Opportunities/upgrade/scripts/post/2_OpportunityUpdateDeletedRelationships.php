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

class SugarUpgradeOpportunityUpdateDeletedRelationships extends UpgradeScript
{
    public $order = 2195;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        $this->log('Updating accounts_opportunities Deleted Status on Deleted Opportunities');
        $sql = "UPDATE accounts_opportunities
                SET    deleted = 1
                WHERE
                       deleted = 0 AND
                       opportunity_id IN ( SELECT id FROM opportunities WHERE deleted = 1 );";
        $this->db->query($sql);

        $this->log('Done Updating accounts_opportunities Deleted Status on Deleted Opportunities');
    }
}
