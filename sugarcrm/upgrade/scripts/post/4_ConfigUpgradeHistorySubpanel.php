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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
/**
 * Update config entries for History Supanel admin tool
 */
class SugarUpgradeConfigUpgradeHistorySubpanel extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // only do it when going to 7.2+
        if (!version_compare($this->from_version, '7.2', '<')) return;

        $defaults = array(
            'hide_history_contacts_emails' => array (
                'Cases' => false,
                'Accounts' => false,
                'Opportunities' => true
            ),
        );

        foreach ($defaults as $key => $values) {
            if (!isset($this->upgrader->config[$key])) {
                $this->upgrader->config[$key] = $values;
            }
        }
    }
}
