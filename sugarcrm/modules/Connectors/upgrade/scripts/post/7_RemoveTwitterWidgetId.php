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
 * Remove twitter widget id from custom config file when upgrading from 6.7.5+ to 7
 */
class SugarUpgradeRemoveTwitterWidgetId extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // must be upgrading from 6.7.5+
        if (!version_compare($this->from_version, '6.7.4', '>') || !version_compare($this->from_version, '7.0.0', '<')) {
            return;
        }

        // remove data_widget_id from custom config file
        $source = SourceFactory::getSource("ext_rest_twitter");
        if ($source && $source->getProperty('data_widget_id')) {
            $properties = $source->getProperties();
            unset($properties['data_widget_id']);
            $source->setProperties($properties);
            $source->saveConfig();
        }
    }
}
