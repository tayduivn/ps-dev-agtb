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

class S_901_2_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public $not = true;

    public function getVersionAndFlavor()
    {
        return array('7.6.0.0RC4', 'ent');
    }

    public function getVersion()
    {
        $result = parent::getVersion();
        $result[0] = '7.6.0.0RC4';
        $result[1] = 'ent';
        return $result;
    }

    public function getPackageManifest()
    {
        $result = parent::getPackageManifest();
        $result['version'] = '7.6.0.0';
        $result['flavor'] = 'ent';
        return $result;
    }
}
