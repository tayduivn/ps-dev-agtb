<?php

require_once 'modules/HealthCheck/Scanner/ScannerMeta.php';

class HealthCheckScannerMetaCasesTestWrapper extends HealthCheckScannerMeta
{
    public static function getCodes()
    {
        $instance = new HealthCheckScannerMeta();
        return array_keys($instance->meta);
    }
}
