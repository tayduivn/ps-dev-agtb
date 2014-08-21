<?php

class S_503_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public function init()
    {
        if (parent::init()) {
            $this->beanList['503Module'] = '503Module';
            $this->newModules['503Module'] = '503Module';
            return true;
        }
        return false;
    }

    protected function getModuleList()
    {
        return array('503Module');
    }

    public function tearDown()
    {
        unset($this->beanList['503Module'], $this->newModules['503Module']);
    }
}
