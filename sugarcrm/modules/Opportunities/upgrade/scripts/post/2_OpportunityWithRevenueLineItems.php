<?php

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

class SugarUpgradeOpportunityWithRevenueLineItems extends UpgradeScript
{
    public $order = 2050;
    public $version = "7.6.0.0";
    public $type = self::UPGRADE_CORE;

    protected $validFlavors = array('ent', 'ult');

    public function run()
    {
        // if we are not going to ent or ult, we need to kick out
        if (!in_array(strtolower($this->to_flavor), $this->validFlavors)) {
            return;
        }
        // to run this we must be coming any version of 7 before 7.6
        if (version_compare($this->from_version, '7.0', '>=') && version_compare($this->from_version, '7.6', '<')) {
            SugarAutoLoader::load('modules/Opportunities/include/OpportunityWithRevenueLineItems.php');

            // in the upgrade, we only want to do the metadata conversion
            $converter = new OpportunityWithRevenueLineItem();
            $converter->doMetadataConvert();

            $admin = BeanFactory::getBean('Administration');
            $admin->saveSetting('Opportunities', 'opps_view_by', 'RevenueLineItems', 'base');
            Opportunity::getSettings(true);
        }
    }
}
