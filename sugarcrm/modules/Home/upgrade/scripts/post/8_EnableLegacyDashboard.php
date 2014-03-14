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

class SugarUpgradeEnableLegacyDashboard extends UpgradeScript
{
    public $order = 8999;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // If the from_version is less than 7, we need to enable the legacy dashboards
        if (version_compare($this->from_version, '7.0.0', '<')) {
            $config = new Configurator();
            $config->config['enable_legacy_dashboards'] = true;
            $config->config['lock_homepage'] = true;
            $config->handleOverride();
            $this->log('Legacy Dashboards Enabled!');
        }
    }
}
