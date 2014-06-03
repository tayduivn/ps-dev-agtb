<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * Removes files for fields
 */
class SugarUpgradeRemoveOldFieldFiles extends UpgradeScript
{
    public $order = 4101;
    public $type = self::UPGRADE_CORE;
    public $version = '7.2.1';

    public function run()
    {
        $this->log('Removing old field files');
        // we only need to remove these files if
        // the from_version is less than 7.2.1 but greater or equal to 6.7.0
        if (version_compare($this->from_version, '7.2.1', '<')
            && version_compare($this->from_version, '6.7.0', '>=')
        ) {
            $this->log('Removing files for 6.7.0 -> 7.2.1');
            // files to delete
            $files = array(
                'clients/base/fields/date/default.hbs',
                'clients/base/fields/date/detail.hbs',
                'clients/base/fields/date/list.hbs',
                'clients/base/fields/datetimecombo/default.hbs',
                'clients/base/fields/datetimecombo/detail.hbs',
                'clients/base/fields/datetimecombo/list.hbs',
                'modules/Notifications/clients/base/fields/datetimecombo/datetimecombo.js',
                'modules/Notifications/clients/base/fields/datetimecombo/detail.hbs',
            );

            $this->fileToDelete($files);
        }

        $this->log('Done removing old field files');
    }
}
