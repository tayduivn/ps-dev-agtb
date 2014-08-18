<?php

class S_515_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    protected function init()
    {
        return $this->fail("files.md5 not found");
    }
}
