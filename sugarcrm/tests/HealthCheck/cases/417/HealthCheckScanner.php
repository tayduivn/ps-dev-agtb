<?php

class S_417_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    protected function getModuleList()
    {
        $result = parent::getModuleList();
        $this->beanList['Feeds'] = 'Feed';
        $this->beanFiles['Feed'] = 'modules/Feeds/Feed.php';
        return $result;
    }
}
