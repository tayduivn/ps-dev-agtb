<?php

class S_999_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public function init()
    {
        $this->updateStatus('Something_went_wrong_during_check_oops');
    }
}
