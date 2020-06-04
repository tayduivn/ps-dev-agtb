<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Add editable 'sales stage' to layouts in rli mode.
 */
class SugarUpgradeOpportunityUpdateSalesStageMetadata extends UpgradeScript
{
    public $order = 7030;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if ($this->toFlavor('ent') &&
            version_compare($this->from_version, '10.1.0', '<') &&
            Opportunity::usingRevenueLineItems()) {
            SugarAutoLoader::load('modules/Opportunities/include/OpportunityViews.php');
            $view = new OpportunityViews();
            $fieldMap = [
                'sales_stage' => true,
            ];
            $view->processBaseRecordLayout($fieldMap);
            $view->processMobileRecordLayout($fieldMap);
            $view->processPreviewLayout($fieldMap);
            $view->processListViews($fieldMap);
        }
    }
}
