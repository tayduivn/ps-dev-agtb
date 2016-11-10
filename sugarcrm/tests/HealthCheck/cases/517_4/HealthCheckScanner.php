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

class S_517_4_PackageManager extends PackageManager
{
    public function getinstalledPackages($types = array('module', 'langpack'))
    {
        return array(
            array(
                'name' => 'SynoDashboardTemplates',
                'version' => '20160916',
            ),
        );
    }
}

class S_517_4_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public function getPackageManager()
    {
        return new S_517_4_PackageManager();
    }
}
