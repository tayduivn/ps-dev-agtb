<?php

class S_405_PackageManager extends PackageManager
{
    function getinstalledPackages($types = array('module', 'langpack'))
    {
        return array(
            array(
                'enabled' => 'ENABLED',
                'name' => 'Zendesk',
                'version' => '2.7',
            ),
        );
    }
}

class S_405_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public function getPackageManager()
    {
        return new S_405_PackageManager();
    }
}
